<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Member;
use App\Services\RegisterAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    // Pantalla para anotar visitas: esperados de hoy y ya anotados
    public function index(Request $request, RegisterAttendance $service): View
    {
        $user = $request->user();

        $attendances = Attendance::with('member')
            ->where('gym_id', $user->gym_id)
            ->whereDate('checked_in_at', today())
            ->latest('checked_in_at')
            ->get();

        return view('attendances.index', [
            'attendances' => $attendances,
            'expected' => $service->expectedToday($user),
            'result' => session('attendance_result'),
            'matches' => session('attendance_matches'),
            'notice' => session('attendance_notice'),
        ]);
    }

    // Sugerencias mientras se escribe (JSON)
    public function search(Request $request, RegisterAttendance $service): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $user = $request->user();
        $arrived = $service->checkedInToday($user);

        $data = $service->search($term, $user)->map(fn (Member $member) => [
            'id' => $member->id,
            'name' => $member->first_name.' '.$member->last_name,
            'document' => $member->document_number,
            'checked_in' => $arrived->contains($member->id),
        ])->values();

        return response()->json(['data' => $data]);
    }

    // Anota la visita: por socio elegido de la lista o por búsqueda
    public function store(StoreAttendanceRequest $request, RegisterAttendance $service): RedirectResponse
    {
        $user = $request->user();

        if ($request->filled('member_id')) {
            $member = Member::with('gym')
                ->where('gym_id', $user->gym_id)
                ->find($request->integer('member_id'));

            return $this->back($member ? $service->register($member, $user) : $service->notFound());
        }

        $members = $service->search($request->validated('code'), $user);

        if ($members->isEmpty()) {
            return $this->back($service->notFound());
        }

        if ($members->count() === 1) {
            return $this->back($service->register($members->first(), $user));
        }

        // Varias coincidencias: recepción elige a quién anotar
        return redirect()
            ->route('attendances.index')
            ->withInput()
            ->with('attendance_matches', $members->map(fn (Member $m) => [
                'id' => $m->id,
                'name' => $m->first_name.' '.$m->last_name,
                'document' => $m->document_number,
            ])->all());
    }

    // Quita una visita anotada por error (solo las de hoy)
    public function destroy(Request $request, Attendance $attendance): RedirectResponse
    {
        abort_unless($attendance->gym_id === $request->user()->gym_id, 403);

        if (! $attendance->checked_in_at->isToday()) {
            return $this->notice('Solo se pueden quitar visitas de hoy.');
        }

        $attendance->loadMissing('member');
        $name = $attendance->member->first_name.' '.$attendance->member->last_name;

        $attendance->delete();

        return $this->notice('Se quitó la visita de '.$name.'.');
    }

    private function back(array $result): RedirectResponse
    {
        return redirect()->route('attendances.index')->with('attendance_result', $result);
    }

    private function notice(string $message): RedirectResponse
    {
        return redirect()->route('attendances.index')->with('attendance_notice', $message);
    }
}