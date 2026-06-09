<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Resolves a dashboard filter selection (today / yesterday / week / month /
 * year / custom) into a concrete [start, end] window plus display metadata and
 * the trend-chart granularity (daily for short spans, monthly for long ones).
 */
class DashboardDateRange
{
    public const PERIODS = ['today', 'yesterday', 'week', 'month', 'year', 'custom'];

    public function __construct(
        public readonly string $period,
        public readonly Carbon $start,
        public readonly Carbon $end,
        public readonly string $label,
    ) {}

    public static function fromRequest(?string $period, ?string $start = null, ?string $end = null): self
    {
        $period = in_array($period, self::PERIODS, true) ? $period : 'month';
        $now = Carbon::now();

        return match ($period) {
            'today' => new self('today', $now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today'),
            'yesterday' => new self('yesterday', $now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay(), 'Yesterday'),
            'week' => new self('week', $now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(), 'Last 7 Days'),
            'year' => new self('year', $now->copy()->startOfYear(), $now->copy()->endOfYear(), 'This Year'),
            'custom' => self::custom($start, $end),
            default => new self('month', $now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month'),
        };
    }

    private static function custom(?string $start, ?string $end): self
    {
        try {
            $from = $start ? Carbon::parse($start)->startOfDay() : Carbon::now()->startOfMonth();
            $to = $end ? Carbon::parse($end)->endOfDay() : Carbon::now()->endOfDay();
        } catch (\Throwable) {
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now()->endOfDay();
        }

        if ($to->lessThan($from)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return new self('custom', $from, $to, $from->format('d M Y').' – '.$to->format('d M Y'));
    }

    /** Number of whole days the window spans (inclusive). */
    public function days(): int
    {
        return (int) floor($this->start->diffInDays($this->end)) + 1;
    }

    /** The equal-length window immediately preceding this one (for % change). */
    public function previous(): self
    {
        $length = (int) round($this->start->diffInSeconds($this->end));
        $prevEnd = $this->start->copy()->subSecond();
        $prevStart = $prevEnd->copy()->subSeconds($length);

        return new self('previous', $prevStart, $prevEnd, 'Previous period');
    }

    /** Bucket the trend chart by day for short spans, otherwise by month. */
    public function isDailyTrend(): bool
    {
        return $this->days() <= 62;
    }
}
