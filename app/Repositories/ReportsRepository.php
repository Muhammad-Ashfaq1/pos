<?php

namespace App\Repositories;

use App\Reports\ReportDefinition;
use App\Repositories\Interface\ReportRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic, dynamic report repository. A single implementation serves every
 * report definition for both the tenant portal and the employee panel: it
 * builds the filtered query, applies DataTables pagination/ordering, and maps
 * rows through the definition's column resolvers. The same `query()` feeds the
 * Excel export so table and file are always identical.
 */
class ReportsRepository implements ReportRepositoryInterface
{
    public function data(ReportDefinition $report, array $filters): array
    {
        $start = max((int) ($filters['start'] ?? 0), 0);
        $length = (int) ($filters['length'] ?? 25);
        $length = $length > 0 ? min($length, 500) : 25;

        $filtered = $this->query($report, $filters);

        $recordsTotal = (clone $filtered)->count();

        $report->applyOrdering($filtered, $filters);

        $rows = $filtered
            ->skip($start)
            ->take($length)
            ->get()
            ->map(fn (Model $row) => $report->mapRow($row))
            ->all();

        return [
            'draw' => (int) ($filters['draw'] ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $rows,
            'summary' => $report->summary($this->query($report, $filters)),
        ];
    }

    public function query(ReportDefinition $report, array $filters): Builder
    {
        return $report->query($filters);
    }
}
