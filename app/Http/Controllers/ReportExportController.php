<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Services\Reports\Contracts\ExportableReport;
use App\Services\Reports\CsvExporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __invoke(ReportFilterRequest $request, CsvExporter $exporter, string $report): StreamedResponse
    {
        $class = config("reports.exports.{$report}");

        abort_unless($class, 404);

        $service = app($class);

        abort_unless($service instanceof ExportableReport, 404);

        return $exporter->download($service, $request->filters());
    }
}
