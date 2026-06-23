<?php

namespace App\Reports;

use App\Support\DashboardDateRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * A single dynamic report dataset. One subclass per report (sales, payments,
 * products, customers). The subclass declares its model, columns, date columns,
 * filters, sortable map and summary aggregates — and this base class turns that
 * declaration into a filtered/sorted Eloquent query plus the row/heading maps
 * shared by both the on-screen DataTable and the Excel export.
 *
 * Adding a new report = add one subclass and register it in ReportRegistry.
 * Nothing in the controller, repository, exporter or JS needs to change.
 */
abstract class ReportDefinition
{
    /** Machine key used in routes/URLs, e.g. "sales". */
    abstract public function key(): string;

    /** Human label shown in the report picker / export title. */
    abstract public function label(): string;

    /** Eloquent model class backing this report. */
    abstract protected function model(): string;

    /**
     * Column definitions. Each is:
     *   ['key' => string, 'label' => string, 'value' => fn(Model): scalar,
     *    'align' => 'start'|'center'|'end' (optional), 'type' => string (optional)]
     *
     * The `value` closure produces the display string used identically by the
     * table and the export, guaranteeing parity.
     *
     * @return list<array<string, mixed>>
     */
    abstract public function columns(): array;

    /** Permission required to view/export this report. */
    public function permission(): string
    {
        return 'reports.view';
    }

    /** Default date column the range filter applies to. */
    public function dateColumn(): string
    {
        return 'created_at';
    }

    /**
     * Selectable date columns (key => label) shown in the "Filter date by"
     * dropdown. Override to expose more than the default created_at.
     *
     * @return array<string, string>
     */
    public function dateColumnOptions(): array
    {
        return ['created_at' => 'Created Date'];
    }

    /**
     * Declarative per-report filters. Each descriptor:
     *   ['key' => string, 'label' => string, 'type' => 'select'|'number'|'boolean',
     *    'options' => array|callable (for select),
     *    'apply' => fn(Builder, mixed): void]
     *
     * @return list<array<string, mixed>>
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * Orderable columns: column key => fully-qualified DB column.
     *
     * @return array<string, string>
     */
    public function sortable(): array
    {
        return [];
    }

    /** Aggregate cards for the filtered set. @return list<array{label:string,value:string}> */
    public function summary(Builder $query): array
    {
        return [];
    }

    /** The base query (eager loads, joins, hard constraints). */
    protected function baseQuery(): Builder
    {
        return ($this->model())::query();
    }

    /** Free-text search across the dataset. No-op unless overridden. */
    protected function applySearch(Builder $query, string $term): void {}

    /** Underlying table name, used for unambiguous ordering. */
    protected function table(): string
    {
        return (new ($this->model()))->getTable();
    }

    /**
     * Build the fully filtered query: date range + search + declared filters.
     * Shared by the listing and the export so both see identical rows.
     *
     * @param  array<string, mixed>  $filters
     */
    public function query(array $filters): Builder
    {
        $query = $this->baseQuery();

        // Note: the date-range bounds are deliberately named `date_from`/`date_to`
        // (not `start`/`end`) so they never collide with the DataTables paging
        // params `start`/`length` carried in the same request.
        $range = DashboardDateRange::fromRequest(
            $filters['period'] ?? null,
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null,
        );

        $query->withinRange($range, $this->resolveDateColumn($filters));

        $term = trim((string) (data_get($filters, 'search.value') ?? ($filters['search_value'] ?? '')));
        if ($term !== '') {
            $this->applySearch($query, $term);
        }

        foreach ($this->filters() as $filter) {
            $value = $filters[$filter['key']] ?? null;

            if ($value === null || $value === '' || (is_array($value) && $value === [])) {
                continue;
            }

            ($filter['apply'])($query, $value);
        }

        return $query;
    }

    /**
     * Apply DataTables ordering, falling back to newest-first on the active
     * date column.
     *
     * @param  array<string, mixed>  $filters
     */
    public function applyOrdering(Builder $query, array $filters): void
    {
        $orderColumnIndex = data_get($filters, 'order.0.column');
        $direction = data_get($filters, 'order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $columns = $filters['columns'] ?? [];
        $key = is_numeric($orderColumnIndex)
            ? data_get($columns, (int) $orderColumnIndex.'.data')
            : null;

        $sortable = $this->sortable();

        if (is_string($key) && array_key_exists($key, $sortable)) {
            $query->orderBy($sortable[$key], $direction)->orderBy($this->table().'.id', $direction);

            return;
        }

        $query
            ->orderByDesc($this->table().'.'.$this->resolveDateColumn($filters))
            ->orderByDesc($this->table().'.id');
    }

    /**
     * Display row keyed by column key — used by both the table and the export.
     *
     * @return array<string, mixed>
     */
    public function mapRow(Model $row): array
    {
        $out = [];

        foreach ($this->columns() as $column) {
            $out[$column['key']] = ($column['value'])($row);
        }

        return $out;
    }

    /** Column metadata for the front-end (no closures). @return list<array<string,mixed>> */
    public function columnsMeta(): array
    {
        $sortable = $this->sortable();

        return array_map(fn (array $column): array => [
            'key' => $column['key'],
            'label' => $column['label'],
            'align' => $column['align'] ?? 'start',
            'orderable' => array_key_exists($column['key'], $sortable),
        ], $this->columns());
    }

    /** Filter metadata for the front-end (resolves option callables, drops closures). */
    public function filtersMeta(): array
    {
        return array_map(function (array $filter): array {
            $meta = Arr::except($filter, ['apply']);

            if (isset($meta['options']) && is_callable($meta['options'])) {
                $meta['options'] = ($meta['options'])();
            }

            return $meta;
        }, $this->filters());
    }

    /** Ordered list of heading labels for the export. @return list<string> */
    public function headings(): array
    {
        return array_map(fn (array $column): string => $column['label'], $this->columns());
    }

    /**
     * The date column the range applies to — the request choice if it is one of
     * the allowed options, otherwise the default.
     *
     * @param  array<string, mixed>  $filters
     */
    protected function resolveDateColumn(array $filters): string
    {
        $requested = $filters['date_column'] ?? null;

        return is_string($requested) && array_key_exists($requested, $this->dateColumnOptions())
            ? $requested
            : $this->dateColumn();
    }
}
