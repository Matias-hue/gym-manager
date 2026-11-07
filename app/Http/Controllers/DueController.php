<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MessageLog;
use App\Services\DueList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DueController extends Controller
{
    // Lista de quién vence y quién debe
    public function index(Request $request, DueList $dues): View
    {
        $filter = $request->query('estado');
        $filter = in_array($filter, DueList::FILTERS, true) ? $filter : 'pendientes';
        $search = trim((string) $request->query('q', ''));

        $rows = $dues->rows($request->user(), $search);

        return view('dues.index', [
            'rows' => $dues->filter($rows, $filter),
            'counts' => $dues->counts($rows),
            'filter' => $filter,
            'search' => $search,
            'expiringDays' => DueList::EXPIRING_DAYS,
        ]);
    }

    // Registra el aviso y abre WhatsApp con el mensaje ya escrito
    public function notify(Request $request, Member $member, DueList $dues): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->gym_id === $member->gym_id, 403);

        $membership = $member->currentMembership;

        if (! $membership) {
            return back()->with('error', 'Este socio todavía no tiene pagos registrados.');
        }

        $type = $dues->messageType($membership->state($user->gym->grace_days));
        $message = $dues->message($member, $membership, $type, $user->gym->name);
        $url = $member->whatsappUrl($message);

        if (! $url) {
            return back()->with('error', "{$member->first_name} no tiene celular cargado.");
        }

        MessageLog::create([
            'gym_id' => $member->gym_id,
            'member_id' => $member->id,
            'type' => $type,
            'channel' => 'whatsapp',
            'sent_by' => $user->id,
            'sent_at' => now(),
            'status' => 'opened',
        ]);

        return redirect()->away($url);
    }
}
