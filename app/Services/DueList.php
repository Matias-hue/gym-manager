<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MessageLog;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Arma la lista de vencimientos: el estado de cada socio activo, calculado
 * con la fecha de hoy, y el mensaje de WhatsApp para avisarle.
 */
class DueList
{
    // Un socio al día "está por vencer" cuando le faltan estos días o menos
    public const EXPIRING_DAYS = 7;

    public const FILTERS = ['pendientes', 'por_vencer', 'gracia', 'vencidos', 'sin_pagos', 'todos'];

    /**
     * Una fila por socio activo, ordenada por vencimiento (los más atrasados primero).
     * Cada fila: member, membership, state, bucket, days, ends_on, last_sent.
     */
    public function rows(User $user, ?string $search = null): Collection
    {
        $graceDays = $user->gym->grace_days;

        $members = Member::where('gym_id', $user->gym_id)
            ->where('status', 'active')
            ->when($search, fn ($query) => $this->applySearch($query, $search))
            ->with('currentMembership.plan')
            ->get();

        $lastSent = MessageLog::where('gym_id', $user->gym_id)
            ->whereIn('member_id', $members->pluck('id'))
            ->whereIn('type', ['expiring', 'overdue'])
            ->selectRaw('member_id, max(sent_at) as last_sent')
            ->groupBy('member_id')
            ->pluck('last_sent', 'member_id');

        return $members
            ->map(fn (Member $member) => $this->row($member, $graceDays, $lastSent[$member->id] ?? null))
            ->sortBy(fn (array $row) => $row['ends_on']?->timestamp ?? PHP_INT_MAX)
            ->values();
    }

    // Cantidad de socios por pestaña. "pendientes" = por vencer + gracia + vencidos
    public function counts(Collection $rows): array
    {
        $by = $rows->countBy('bucket');

        $porVencer = $by['por_vencer'] ?? 0;
        $gracia = $by['gracia'] ?? 0;
        $vencidos = $by['vencidos'] ?? 0;

        return [
            'pendientes' => $porVencer + $gracia + $vencidos,
            'por_vencer' => $porVencer,
            'gracia' => $gracia,
            'vencidos' => $vencidos,
            'sin_pagos' => $by['sin_pagos'] ?? 0,
            'todos' => $rows->count(),
        ];
    }

    public function filter(Collection $rows, string $filter): Collection
    {
        return match ($filter) {
            'pendientes' => $rows->whereIn('bucket', ['por_vencer', 'gracia', 'vencidos'])->values(),
            'por_vencer', 'gracia', 'vencidos', 'sin_pagos' => $rows->where('bucket', $filter)->values(),
            default => $rows,
        };
    }

    // expiring (todavía al día) | overdue (ya venció)
    public function messageType(string $state): string
    {
        return $state === 'current' ? 'expiring' : 'overdue';
    }

    public function message(Member $member, Membership $membership, string $type, string $gymName): string
    {
        $date = $membership->ends_on->format('d/m/Y');

        return $type === 'expiring'
            ? "Hola {$member->first_name}, te escribimos de {$gymName}. Tu cuota vence el {$date}. ¡Te esperamos!"
            : "Hola {$member->first_name}, te escribimos de {$gymName}. Tu cuota venció el {$date}. Cualquier duda, avisanos.";
    }

    private function row(Member $member, int $graceDays, ?string $lastSent): array
    {
        $membership = $member->currentMembership;

        if (! $membership) {
            return [
                'member' => $member,
                'membership' => null,
                'state' => 'none',
                'bucket' => 'sin_pagos',
                'days' => null,
                'ends_on' => null,
                'last_sent' => null,
            ];
        }

        $state = $membership->state($graceDays);
        $days = (int) today()->diffInDays($membership->ends_on, false); // negativo = ya venció

        $bucket = match ($state) {
            'current' => $days <= self::EXPIRING_DAYS ? 'por_vencer' : 'al_dia',
            'grace' => 'gracia',
            default => 'vencidos',
        };

        return [
            'member' => $member,
            'membership' => $membership,
            'state' => $state,
            'bucket' => $bucket,
            'days' => $days,
            'ends_on' => $membership->ends_on,
            'last_sent' => $lastSent ? Carbon::parse($lastSent) : null,
        ];
    }

    // Cada palabra tiene que aparecer en nombre, apellido, DNI o celular
    private function applySearch($query, string $search): void
    {
        foreach (preg_split('/\s+/', trim($search)) as $term) {
            $like = '%'.$term.'%';

            $query->where(function ($q) use ($like) {
                $q->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('document_number', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }
    }
}
