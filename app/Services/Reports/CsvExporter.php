<?php

namespace App\Services\Reports;

use App\Services\Reports\Contracts\ExportableReport;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Descarga en CSV pensado para Excel en español: separador ";" y BOM UTF-8.
 */
final class CsvExporter
{
    public function download(ExportableReport $report, ReportFilters $filters): StreamedResponse
    {
        $filename = sprintf(
            '%s_%s_%s.csv',
            $report->exportName(),
            $filters->period->from->format('Ymd'),
            $filters->period->to->format('Ymd'),
        );

        return response()->streamDownload(function () use ($report, $filters) {
            $out = fopen('php://output', 'w');

            fwrite($out, "\xEF\xBB\xBF");
            $this->write($out, $report->exportHeaders());

            foreach ($report->exportRows($filters) as $row) {
                $this->write($out, $row);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @param resource $out */
    private function write($out, array $row): void
    {
        fputcsv($out, array_map($this->safe(...), $row), ';', '"', '\\');
    }

    /**
     * Evita que Excel interprete un texto como fórmula (nombres, conceptos,
     * notas escritas por usuarios).
     */
    private function safe(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
