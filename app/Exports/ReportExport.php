<?php

namespace App\Exports;

use App\Reports\ReportDefinition;
use App\Repositories\Interface\ReportRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * One generic Excel export for every report. It pulls the headings and each
 * row from the report definition's column resolvers and reuses the repository's
 * filtered query, so the .xlsx is identical to the on-screen, filtered table.
 */
class ReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        private readonly ReportDefinition $report,
        private readonly array $filters,
        private readonly ReportRepositoryInterface $repository,
    ) {}

    public function query(): Builder
    {
        $query = $this->repository->query($this->report, $this->filters);
        $this->report->applyOrdering($query, $this->filters);

        return $query;
    }

    /** @return list<string> */
    public function headings(): array
    {
        return $this->report->headings();
    }

    /** @return list<mixed> */
    public function map($row): array
    {
        /** @var Model $row */
        return array_values($this->report->mapRow($row));
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
