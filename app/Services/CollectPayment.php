<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CollectPayment
{
    /**
     * $data: amount, method, months (opcional, 1 por defecto),
     * starts_on y ends_on (opcionales), time_slot y notes (opcionales).
     */
    public function handle(Member $member, Plan $plan, array $data, User $user): Payment
    {
        return DB::transaction(function () use ($member, $plan, $data, $user) {
            $starts = isset($data['starts_on']) ? Carbon::parse($data['starts_on']) : today();
            $months = $data['months'] ?? 1;

            // 31 de octubre + 1 mes = 30 de noviembre; 31 de enero + 1 mes = 28 de febrero
            $ends = isset($data['ends_on'])
                ? Carbon::parse($data['ends_on'])
                : $starts->copy()->addMonthsNoOverflow($months);

            $membership = Membership::create([
                'gym_id' => $member->gym_id,
                'member_id' => $member->id,
                'plan_id' => $plan->id,
                'time_slot' => $data['time_slot'] ?? null,
                'starts_on' => $starts,
                'ends_on' => $ends,
                'price_charged' => $data['amount'],
                'notes' => $data['notes'] ?? null,
            ]);

            $payment = Payment::create([
                'gym_id' => $member->gym_id,
                'member_id' => $member->id,
                'membership_id' => $membership->id,
                'amount' => $data['amount'],
                'method' => $data['method'],
                'paid_at' => now(),
                'received_by' => $user->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $session = CashSession::where('gym_id', $member->gym_id)
                ->whereNull('closed_at')
                ->latest('opened_at')
                ->first();

            if ($session) {
                CashMovement::create([
                    'gym_id' => $member->gym_id,
                    'cash_session_id' => $session->id,
                    'type' => 'income',
                    'concept' => "Cuota {$plan->name} - {$member->first_name} {$member->last_name}",
                    'amount' => $data['amount'],
                    'payment_id' => $payment->id,
                ]);
            }

            return $payment;
        });
    }
}