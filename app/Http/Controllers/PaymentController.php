<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Member;
use App\Models\Plan;
use App\Services\CollectPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    // Formulario de cobro con el socio ya elegido
    public function create(Request $request, Member $member): View
    {
        abort_unless($request->user()->gym_id === $member->gym_id, 403);

        $plans = Plan::where('gym_id', $member->gym_id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Ocupación de los turnos de los planes con cupo
        $occupancy = [];
        foreach ($plans->whereNotNull('capacity_per_slot') as $plan) {
            $occupancy[$plan->id] = [
                'tarde' => $plan->slotOccupancy('tarde'),
                'noche' => $plan->slotOccupancy('noche'),
            ];
        }

        return view('payments.create', [
            'member' => $member,
            'plans' => $plans,
            'current' => $member->currentMembership,
            'state' => $member->state(),
            'occupancy' => $occupancy,
        ]);
    }

    // Guarda membresía + pago + movimiento de caja (todo en una transacción).
    // La validación y el permiso están en StorePaymentRequest.
    public function store(StorePaymentRequest $request, Member $member, CollectPayment $collect): RedirectResponse
    {
        $data = $request->validated();

        $plan = Plan::where('gym_id', $member->gym_id)->findOrFail($data['plan_id']);

        // Planes con cupo (spinning): el turno es obligatorio y no puede estar lleno
        if ($plan->capacity_per_slot) {
            if (empty($data['time_slot'])) {
                return back()->withInput()->withErrors(['time_slot' => 'Elegí el turno (tarde o noche).']);
            }

            if ($plan->slotOccupancy($data['time_slot']) >= $plan->capacity_per_slot) {
                return back()->withInput()->withErrors([
                    'time_slot' => "El turno {$data['time_slot']} está lleno ({$plan->capacity_per_slot} lugares).",
                ]);
            }
        } else {
            $data['time_slot'] = null;
        }

        // validated() devuelve strings: Carbon necesita un int
        $data['months'] = (int) ($data['months'] ?? 1);
        $data['amount'] = (int) $data['amount'];

        $collect->handle($member, $plan, $data, $request->user());

        return redirect()
            ->route('members.show', $member)
            ->with('status', 'Cobro registrado: $'.number_format($data['amount'], 0, ',', '.'));
    }
}
