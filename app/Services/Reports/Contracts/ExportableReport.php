<?php

namespace App\Services\Reports\Contracts;

use App\Services\Reports\ReportFilters;

/**
 * Un reporte que se puede bajar como CSV.
 */
interface ExportableReport
{
    /** Prefijo del nombre de archivo, sin extensión. */
    public function exportName(): string;

    /** @return list<string> */
    public function exportHeaders(): array;

    /** @return iterable<list<string|int|float|null>> */
    public function exportRows(ReportFilters $filters): iterable;
}
