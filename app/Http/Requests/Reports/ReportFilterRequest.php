<?php

namespace App\Http\Requests\Reports;

use App\Support\DashboardDateRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route-level `permission:reports.view` middleware already gates access.
        return true;
    }

    /**
     * Only the shared date/range params are validated here; per-report filter
     * keys are whitelisted and applied by each report definition, and the
     * DataTables params (draw/start/length/order/columns/search) pass through.
     */
    public function rules(): array
    {
        return [
            'period' => ['nullable', Rule::in(DashboardDateRange::PERIODS)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'date_column' => ['nullable', 'string', 'max:64'],
            'draw' => ['nullable', 'integer'],
            'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer'],
        ];
    }
}
