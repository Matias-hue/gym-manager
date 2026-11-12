<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Models\Plan;
use App\Services\Reports\AttendanceReport;
use App\Services\Reports\CashReport;
use App\Services\Reports\IncomeReport;
use App\Services\Reports\MembersReport;
use App\Services\Reports\SummaryReport;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly SummaryReport $summary,
        private readonly IncomeReport $income,
        private readonly MembersReport $members,
        private readonly AttendanceReport $attendance,
        private readonly CashReport $cash,
    ) {}

    public function index(ReportFilterRequest $request): View
    {
        $filters = $request->filters();
        $tab = $request->activeTab();

        $data = match ($tab) {
            'income'     => $this->income->build($filters),
            'members'    => $this->members->build($filters),
            'attendance' => $this->attendance->build($filters),
            'cash'       => $this->cash->build($filters),
            default      => $this->summary->build($filters),
        };

        return view('reports.index', [
            'tab'     => $tab,
            'tabs'    => config('reports.tabs'),
            'ranges'  => config('reports.ranges'),
            'methods' => config('reports.methods'),
            'filters' => $filters,
            'data'    => $data,
            // Incluye planes dados de baja: pueden tener cobros históricos.
            'plans'   => Plan::withTrashed()
                ->where('gym_id', $filters->gymId())
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
