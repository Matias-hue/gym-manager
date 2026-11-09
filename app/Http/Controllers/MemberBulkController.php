<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBulkMembersRequest;
use App\Models\Plan;
use App\Services\ImportMembers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberBulkController extends Controller
{
    // Tabla de filas para cargar varios socios de una vez
    public function create(Request $request): View
    {
        $plans = Plan::where('gym_id', $request->user()->gym_id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Si volvemos de un guardado con filas pendientes, se muestran solo esas
        $oldRows = $request->old('rows');

        return view('members.bulk', [
            'plans' => $plans,
            'rowIndexes' => $oldRows ? array_keys($oldRows) : range(0, 9),
            'rowErrors' => session('row_errors', []),
            'duplicates' => session('duplicates', []),
        ]);
    }

    public function store(StoreBulkMembersRequest $request, ImportMembers $import): RedirectResponse
    {
        $data = $request->validated();

        $planId = filled($data['plan_id'] ?? null) ? (int) $data['plan_id'] : null;
        $endsOn = $data['default_ends_on'] ?? null;

        $result = $import->handle(
            $request->user(),
            $data['rows'],
            $planId,
            $endsOn,
            $request->boolean('confirm_duplicates'),
        );

        // Hay nombres repetidos: no se guardó nada, se pregunta primero
        if ($result['needs_confirmation']) {
            return back()
                ->withInput($request->except('confirm_duplicates'))
                ->with('duplicates', $result['duplicates'])
                ->with('row_errors', $result['errors']);
        }

        $summary = $this->summary($result);

        // Se guardaron las filas correctas; vuelven solo las que tienen algún error
        if ($result['errors']) {
            return back()
                ->withInput([
                    'plan_id' => $planId,
                    'default_ends_on' => $endsOn,
                    'rows' => array_intersect_key($data['rows'], $result['errors']),
                ])
                ->with('row_errors', $result['errors'])
                ->with('status', $summary.' Corregí las filas marcadas y guardalas de nuevo.');
        }

        return redirect()->route('members.index')->with('status', $summary);
    }

    private function summary(array $result): string
    {
        $n = $result['created'];

        if ($n === 0) {
            return 'No se guardó ningún socio.';
        }

        $text = $n === 1 ? 'Se cargó 1 socio' : "Se cargaron {$n} socios";

        if ($result['with_membership'] > 0) {
            $text .= " ({$result['with_membership']} con su vencimiento)";
        }

        return $text.'.';
    }
}
