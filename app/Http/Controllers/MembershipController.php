<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExtendMembershipRequest;
use App\Models\Membership;
use App\Services\ExtendGrace;
use Illuminate\Http\RedirectResponse;

class MembershipController extends Controller
{
    public function extend(ExtendMembershipRequest $request, Membership $membership, ExtendGrace $extendGrace): RedirectResponse
    {
        if ($membership->status === 'cancelled') {
            return back()->withErrors(['grace_until' => 'No se puede prorrogar una membresía cancelada.']);
        }

        $extendGrace->handle(
            $membership,
            $request->validated('grace_until'),
            $request->validated('notes'),
            $request->user()->name
        );

        return back()->with(
            'status',
            'Prórroga guardada hasta el '.$membership->grace_until->format('d/m/Y').'.'
        );
    }
}