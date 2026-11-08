<?php

namespace App\Http\Controllers;

use App\Http\Requests\CloseCashSessionRequest;
use App\Http\Requests\OpenCashSessionRequest;
use App\Http\Requests\StoreCashMovementRequest;
use App\Models\CashMovement;
use App\Models\CashSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CashController extends Controller
{
    // Caja del día: la caja abierta (o el formulario para abrirla) y el historial
    public function index(): View
    {
        $gymId = auth()->user()->gym_id;

        $current = CashSession::where('gym_id', $gymId)
            ->whereNull('closed_at')
            ->with('openedBy')
            ->latest('opened_at')
            ->first();

        $summary = $current?->summary();

        $movements = $current
            ? $current->movements()->with('payment')->latest()->get()
            : collect();

        $history = CashSession::where('gym_id', $gymId)
            ->whereNotNull('closed_at')
            ->with('openedBy')
            ->latest('closed_at')
            ->paginate(10);

        return view('cash.index', compact('current', 'summary', 'movements', 'history'));
    }

    // Detalle de una caja, abierta o cerrada
    public function show(CashSession $cashSession): View
    {
        $this->ensureSameGym($cashSession);

        $cashSession->load('openedBy');

        $summary = $cashSession->summary();
        $movements = $cashSession->movements()->with('payment')->latest()->get();

        return view('cash.show', [
            'session' => $cashSession,
            'summary' => $summary,
            'movements' => $movements,
        ]);
    }

    public function open(OpenCashSessionRequest $request): RedirectResponse
    {
        $user = $request->user();

        $alreadyOpen = CashSession::where('gym_id', $user->gym_id)
            ->whereNull('closed_at')
            ->exists();

        if ($alreadyOpen) {
            return redirect()->route('cash.index')
                ->withErrors(['opening_amount' => 'Ya hay una caja abierta. Cerrala antes de abrir otra.']);
        }

        CashSession::create([
            'gym_id' => $user->gym_id,
            'opened_by' => $user->id,
            'opened_at' => now(),
            'opening_amount' => $request->integer('opening_amount'),
        ]);

        return redirect()->route('cash.index')->with('status', 'Caja abierta.');
    }

    public function storeMovement(StoreCashMovementRequest $request, CashSession $cashSession): RedirectResponse
    {
        $this->ensureSameGym($cashSession);

        if (! $cashSession->isOpen()) {
            return redirect()->route('cash.show', $cashSession)
                ->withErrors(['amount' => 'La caja ya está cerrada, no se pueden agregar movimientos.']);
        }

        CashMovement::create([
            'cash_session_id' => $cashSession->id,
            'type' => $request->input('type'),
            'concept' => $request->input('concept'),
            'amount' => $request->integer('amount'),
        ]);

        return redirect()->route('cash.index')->with('status', 'Movimiento registrado.');
    }

    // Cierre: guarda el efectivo contado y el esperado, así queda fija la diferencia
    public function close(CloseCashSessionRequest $request, CashSession $cashSession): RedirectResponse
    {
        $this->ensureSameGym($cashSession);

        $result = DB::transaction(function () use ($request, $cashSession) {
            $session = CashSession::whereKey($cashSession->id)->lockForUpdate()->first();

            if (! $session->isOpen()) {
                return false;
            }

            $session->update([
                'closed_at' => now(),
                'counted_amount' => $request->integer('counted_amount'),
                'expected_amount' => $session->summary()['expected'],
                'notes' => $request->input('notes'),
            ]);

            return true;
        });

        if (! $result) {
            return redirect()->route('cash.show', $cashSession)
                ->withErrors(['counted_amount' => 'Esta caja ya estaba cerrada.']);
        }

        return redirect()->route('cash.show', $cashSession)->with('status', 'Caja cerrada.');
    }

    // Una caja de otro gimnasio responde como si no existiera
    private function ensureSameGym(CashSession $cashSession): void
    {
        abort_unless($cashSession->gym_id === auth()->user()->gym_id, 404);
    }
}
