<?php

namespace App\Repositories\Interface;

use App\Reports\ReportDefinition;
use Illuminate\Database\Eloquent\Builder;

interface ReportRepositoryInterface
{
    /**
     * DataTables-style server-side payload for a report.
     *
     * @param  array<string, mixed>  $filters
     * @return array{draw:int, recordsTotal:int, recordsFiltered:int, data:array, summary:array}
     */
    public function data(ReportDefinition $report, array $filters): array;

    /**
     * The shared, fully-filtered query — reused by the export so the file
     * matches the on-screen table exactly.
     *
     * @param  array<string, mixed>  $filters
     */
    public function query(ReportDefinition $report, array $filters): Builder;
}
