<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberRequest;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $gymId = $request->user()->gym_id;
        $search = trim((string) $request->query('q', ''));

        // Sin búsqueda se muestran los activos; buscando, se mira en todos
        $estado = $request->query('estado', $search !== '' ? 'all' : 'active');
        if (! in_array($estado, ['active', 'inactive', 'suspended', 'all'], true)) {
            $estado = 'active';
        }

        $members = Member::query()
            ->where('gym_id', $gymId)
            ->when($estado !== 'all', fn ($q) => $q->where('status', $estado))
            ->search($search)
            ->with(['gym', 'currentMembership.plan'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        $counts = Member::where('gym_id', $gymId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('members.index', [
            'members' => $members,
            'search' => $search,
            'estado' => $estado,
            'counts' => $counts,
        ]);
    }

    public function create(): View
    {
        return view('members.create');
    }

    public function store(MemberRequest $request): RedirectResponse
    {
        $gymId = $request->user()->gym_id;
        $data = $request->validated();
        unset($data['confirm_duplicate']);

        // Dos personas pueden llamarse igual, así que no se bloquea: solo se avisa
        if (! $request->boolean('confirm_duplicate')) {
            $duplicate = Member::where('gym_id', $gymId)
                ->whereRaw('LOWER(first_name) = ?', [mb_strtolower($data['first_name'])])
                ->whereRaw('LOWER(last_name) = ?', [mb_strtolower($data['last_name'])])
                ->first();

            if ($duplicate) {
                return back()->withInput()->with('duplicate', [
                    'id' => $duplicate->id,
                    'name' => $duplicate->full_name,
                ]);
            }
        }

        $member = Member::create($data + [
            'gym_id' => $gymId,
            'status' => 'active',
            'joined_at' => today(),
        ]);

        return redirect()
            ->route('members.show', $member)
            ->with('status', "{$member->full_name} quedó registrado como socio.");
    }

    public function show(Member $member): View
    {
        $this->ensureOwnGym($member);

        $member->load(['gym', 'currentMembership.plan']);

        $memberships = $member->memberships()
            ->with('plan')
            ->latest('starts_on')
            ->limit(6)
            ->get();

        $payments = $member->payments()
            ->whereNull('voided_at')
            ->latest('paid_at')
            ->limit(8)
            ->get();

        return view('members.show', [
            'member' => $member,
            'memberships' => $memberships,
            'payments' => $payments,
            'visitsThisWeek' => $member->visitsThisWeek(),
        ]);
    }

    public function edit(Member $member): View
    {
        $this->ensureOwnGym($member);

        return view('members.edit', ['member' => $member]);
    }

    public function update(MemberRequest $request, Member $member): RedirectResponse
    {
        $this->ensureOwnGym($member);

        $data = $request->validated();
        unset($data['confirm_duplicate']);

        $member->update($data);

        return redirect()
            ->route('members.show', $member)
            ->with('status', 'Los datos de '.$member->full_name.' se guardaron.');
    }

    // Un usuario solo puede ver socios de su propio gimnasio
    private function ensureOwnGym(Member $member): void
    {
        abort_unless($member->gym_id === request()->user()->gym_id, 404);
    }
}
