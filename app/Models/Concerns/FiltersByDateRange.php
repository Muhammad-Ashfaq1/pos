<?php

namespace App\Models\Concerns;

use App\Support\DashboardDateRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Reusable date-range filtering for any Eloquent model.
 *
 * Add `use FiltersByDateRange;` to a model and you get two local scopes that
 * constrain a query to a [start, end] window on a configurable date column
 * (defaults to `created_at`). Used across the reporting layer but intentionally
 * generic so any future feature can scope listings/exports by date.
 *
 *   Order::dateRange('2026-01-01', '2026-01-31')->get();
 *   Order::dateRange($from, $to, 'paid_at')->get();
 *   Order::withinRange($range)->get(); // $range = DashboardDateRange
 */
trait FiltersByDateRange
{
    /**
     * Constrain the query to rows whose $column falls within [$start, $end].
     * A null bound leaves that side open. Strings are parsed and snapped to the
     * start/end of their day so a plain date like "2026-01-31" covers the whole day.
     */
    public function scopeDateRange(
        Builder $query,
        Carbon|string|null $start,
        Carbon|string|null $end,
        string $column = 'created_at'
    ): Builder {
        $column = $this->qualifyColumn($column);

        if ($start !== null) {
            $from = $start instanceof Carbon ? $start->copy() : Carbon::parse($start)->startOfDay();
            $query->where($column, '>=', $from);
        }

        if ($end !== null) {
            $to = $end instanceof Carbon ? $end->copy() : Carbon::parse($end)->endOfDay();
            $query->where($column, '<=', $to);
        }

        return $query;
    }

    /**
     * Constrain the query to a resolved DashboardDateRange window. The same
     * value object that drives the dashboard date filter powers reports here.
     */
    public function scopeWithinRange(
        Builder $query,
        DashboardDateRange $range,
        string $column = 'created_at'
    ): Builder {
        return $this->scopeDateRange($query, $range->start, $range->end, $column);
    }
}
