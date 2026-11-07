<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Collection;

class RegisterAttendance
{
    /**
     * Busca socios del gimnasio por documento o por nombre y apellido.
     * Si hay un documento exacto, devuelve solo ese socio.
     */
    public function search(string $term, User $user): Collection
    {
        $term = trim($term);

        if ($term === '') {
            return collect();
        }

        $base = fn () => Member::with('gym')->where('gym_id', $user->gym_id);

        $digits = preg_replace('/[.\s]/', '', $term);

        if (ctype_digit($digits)) {
            $exact = $base()->where('document_number', $digits)->get();

            if ($exact->isNotEmpty()) {
                return $exact;
            }

            return $base()
                ->where('document_number', 'like', $this->escapeLike($digits).'%')
                ->orderBy('last_name')
                ->limit(8)
                ->get();
        }

        $query = $base();

        // Cada palabra tiene que aparecer en el nombre o en el apellido
        foreach (preg_split('/\s+/', $term) as $word) {
            $like = '%'.$this->escapeLike($word).'%';
            $query->where(function ($q) use ($like) {
                $q->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like);
            });
        }

        return $query->orderBy('last_name')->orderBy('first_name')->limit(8)->get();
    }

    /**
     * IDs de los socios que ya tienen su ingreso anotado hoy.
     */
    public function checkedInToday(User $user): Collection
    {
        return Attendance::where('gym_id', $user->gym_id)
            ->whereDate('checked_in_at', today())
            ->pluck('member_id');
    }

    /**
     * Socios que se espera que vengan hoy y todavía no fueron anotados:
     * planes con visitas limitadas por semana o con turno (spinning),
     * filtrados por los días permitidos del plan.
     *
     * Devuelve [ 'Spinning · tarde' => [ ['member_id' => 1, 'name' => '...'], ... ], ... ]
     */
    public function expectedToday(User $user): Collection
    {
        $arrived = $this->checkedInToday($user);
        $graceDays = $user->gym?->grace_days ?? 14;

        $memberships = Membership::with(['member', 'plan'])
            ->where('status', 'active')
            ->whereDate('ends_on', '>=', today()->subDays($graceDays))
            ->whereHas('member', function ($query) use ($user) {
                $query->where('gym_id', $user->gym_id)->where('status', 'active');
            })
            ->whereHas('plan', function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('visits_per_week')->orWhereNotNull('capacity_per_slot');
                });
            })
            ->get();

        return $memberships
            ->sortByDesc('ends_on')
            ->unique('member_id') // si tiene dos membresías superpuestas, cuenta la más nueva
            ->reject(fn (Membership $m) => $arrived->contains($m->member_id))
            ->filter(function (Membership $m) {
                $days = array_map('intval', $m->plan->allowed_weekdays ?? []);

                return $days === [] || in_array(today()->dayOfWeekIso, $days, true);
            })
            ->groupBy(fn (Membership $m) => $m->plan->name.($m->time_slot ? ' · '.$m->time_slot : ''))
            ->map(fn (Collection $group) => $group
                ->sortBy(fn (Membership $m) => $m->member->last_name.' '.$m->member->first_name)
                ->map(fn (Membership $m) => [
                    'member_id' => $m->member_id,
                    'name' => $m->member->first_name.' '.$m->member->last_name,
                ])
                ->values())
            ->sortKeys();
    }

    /**
     * Anota la visita de un socio. Siempre se registra (la gente entra
     * libremente); si algo no está en regla, se devuelve como aviso.
     *
     * Estados: ok | warning | overdue | not_found
     */
    public function register(Member $member, User $user): array
    {
        $member->loadMissing('gym');

        // Evita anotar dos veces el mismo día
        $already = Attendance::where('member_id', $member->id)
            ->whereDate('checked_in_at', today())
            ->oldest('checked_in_at')
            ->first();

        if ($already) {
            return $this->result('warning', false, 'Ya tenía su ingreso anotado hoy', $member, [
                'Primer ingreso de hoy: '.$already->checked_in_at->format('H:i').' hs.',
            ]);
        }

        Attendance::create([
            'gym_id' => $member->gym_id,
            'member_id' => $member->id,
            'checked_in_at' => now(),
            'method' => 'manual',
            'registered_by' => $user->id,
        ]);

        $membership = $member->currentMembership?->loadMissing('plan');

        if (! $membership) {
            return $this->result('overdue', true, 'Ingreso anotado: sin cuota paga', $member, [
                'Este socio todavía no pagó ninguna cuota.',
            ]);
        }

        $state = $membership->state($member->gym->grace_days);

        if ($state === 'expired') {
            return $this->result('overdue', true, 'Ingreso anotado: cuota vencida', $member, [
                'Venció el '.$membership->ends_on->format('d/m/Y').' y ya terminó la tolerancia.',
            ]);
        }

        $messages = [];

        if ($state === 'grace') {
            $messages[] = 'Cuota vencida el '.$membership->ends_on->format('d/m/Y').' (en período de gracia).';
        }

        if ($member->status !== 'active') {
            $messages[] = 'El socio figura como "'.$member->status.'".';
        }

        $plan = $membership->plan;

        // Visitas por semana (musculación 3 días)
        if ($plan->visits_per_week !== null) {
            $total = $member->visitsThisWeek();
            if ($total > $plan->visits_per_week) {
                $messages[] = 'Visitas superadas: su plan permite '.$plan->visits_per_week
                    .' por semana y lleva '.$total.'.';
            }
        }

        // Días permitidos del plan
        $allowed = array_map('intval', $plan->allowed_weekdays ?? []);
        if ($allowed !== [] && ! in_array(today()->dayOfWeekIso, $allowed, true)) {
            $messages[] = 'Hoy no es uno de los días de su plan.';
        }

        // Cupo del turno (spinning)
        if ($plan->capacity_per_slot && $membership->time_slot) {
            $inSlotToday = Attendance::where('gym_id', $member->gym_id)
                ->whereDate('checked_in_at', today())
                ->whereHas('member.memberships', function ($query) use ($plan, $membership) {
                    $query->where('plan_id', $plan->id)
                        ->where('status', 'active')
                        ->whereDate('ends_on', '>=', today())
                        ->where('time_slot', $membership->time_slot);
                })
                ->count();

            if ($inSlotToday > $plan->capacity_per_slot) {
                $messages[] = 'Turno '.$membership->time_slot.' pasado de cupo: '
                    .$inSlotToday.' de '.$plan->capacity_per_slot.'.';
            }
        }

        return $messages === []
            ? $this->result('ok', true, 'Ingreso anotado', $member, [])
            : $this->result('warning', true, 'Ingreso anotado con avisos', $member, $messages);
    }

    public function notFound(): array
    {
        return $this->result('not_found', false, 'No se encontró ningún socio', null, [
            'Revisá el documento o probá con el nombre y apellido.',
        ]);
    }

    private function result(string $status, bool $registered, string $title, ?Member $member, array $messages): array
    {
        return [
            'status' => $status,
            'registered' => $registered,
            'title' => $title,
            'member' => $member ? $member->first_name.' '.$member->last_name : null,
            'member_id' => $member?->id,
            'messages' => $messages,
        ];
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }
}