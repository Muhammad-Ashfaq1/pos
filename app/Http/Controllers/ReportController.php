<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Http\Requests\Reports\ReportFilterRequest;
use App\Reports\ReportRegistry;
use App\Repositories\Interface\ReportRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * One surface-agnostic controller serving the dynamic reports to BOTH the
 * tenant admin portal (`tenant.reports.*`) and the employee panel
 * (`employee.reports.*`). The surface only changes the Blade layout and the
 * route names used for the listing/export URLs; the data path is identical.
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly ReportRegistry $registry,
        private readonly ReportRepositoryInterface $repository,
    ) {}

    /** The report screen (DataTable shell + dynamic filter bar). */
    public function index(Request $request, string $report): View
    {
        $definition = $this->registry->resolve($report);
        $surface = $this->surface($request);

        return view('reports.index', [
            'layout' => $surface['layout'],
            'dashboardRoute' => $surface['dashboardRoute'],
            'report' => $definition->key(),
            'reportLabel' => $definition->label(),
            'tabs' => array_map(fn (array $tab): array => [
                'key' => $tab['key'],
                'label' => $tab['label'],
                'url' => route($surface['prefix'].'index', $tab['key']),
                'active' => $tab['key'] === $definition->key(),
            ], $this->registry->tabs()),
            'columns' => $definition->columnsMeta(),
            'filters' => $definition->filtersMeta(),
            'dateColumns' => $definition->dateColumnOptions(),
            'dataUrl' => route($surface['prefix'].'data', $definition->key()),
            'exportUrl' => route($surface['prefix'].'export', $definition->key()),
        ]);
    }

    /** Server-side DataTables JSON payload + summary cards. */
    public function data(ReportFilterRequest $request, string $report): JsonResponse
    {
        $definition = $this->registry->resolve($report);

        return response()->json(
            $this->repository->data($definition, $request->all())
        );
    }

    /** Stream the current (filtered) report as a .xlsx download. */
    public function export(ReportFilterRequest $request, string $report): BinaryFileResponse
    {
        $definition = $this->registry->resolve($report);
        $filename = sprintf('%s-report-%s.xlsx', $definition->key(), Carbon::now()->format('Y-m-d_His'));

        return Excel::download(
            new ReportExport($definition, $request->all(), $this->repository),
            $filename,
        );
    }

    /**
     * Resolve presentation context from the matched route name prefix.
     *
     * @return array{prefix:string, layout:string, dashboardRoute:string}
     */
    private function surface(Request $request): array
    {
        $name = (string) $request->route()?->getName();
        $isEmployee = str_starts_with($name, 'employee.');

        return [
            'prefix' => $isEmployee ? 'employee.reports.' : 'tenant.reports.',
            'layout' => $isEmployee ? 'layouts.employee-portal' : 'layouts.app',
            'dashboardRoute' => $isEmployee ? 'employee.dashboard' : 'tenant.dashboard',
        ];
    }
}
