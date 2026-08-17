<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Support\DashboardDateRange;
use App\Support\EmployeeNavigation;
use App\Support\ProductMixCards;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkspacePreferencesController extends Controller
{
    public function settingsProductMix(PanelController $panel): View
    {
        $user = request()->user();
        abort_unless($user?->isEmployee(), 403);

        $selected = ProductMixCards::selectedFor($user);
        $stats = $panel->productMixStats(DashboardDateRange::fromRequest('today'), $user);
        $previewByKey = collect(ProductMixCards::summaryCards(ProductMixCards::keys(), $stats))
            ->keyBy('key')
            ->map(fn (array $card): array => [
                'value' => (string) ($card['value'] ?? '0'),
                'meta' => (string) ($card['meta'] ?? ''),
            ])
            ->all();

        return view('employee.settings.product-mix', [
            'groupedCards' => ProductMixCards::groupedCatalog(),
            'selectedKeys' => $selected,
            'selectedCount' => count($selected),
            'previewByKey' => $previewByKey,
        ]);
    }

    public function updateNavigation(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->isEmployee(), 403);

        $validated = $request->validate([
            'employee_nav_mode' => ['required', Rule::in([
                EmployeeNavigation::MODE_BOTTOM,
                EmployeeNavigation::MODE_SIDEBAR,
            ])],
        ]);

        $user->forceFill([
            'employee_nav_mode' => $validated['employee_nav_mode'],
        ])->save();

        return redirect()
            ->route('account.profile')
            ->with('success', 'Workspace navigation updated.');
    }

    public function updateProductMixCards(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->isEmployee(), 403);

        $validated = $request->validate([
            'cards' => ['nullable', 'array'],
            'cards.*' => ['string', Rule::in(ProductMixCards::keys())],
        ]);

        $selected = ProductMixCards::sanitize($validated['cards'] ?? []);

        if ($selected === []) {
            return back()
                ->withInput()
                ->with('error', 'Select at least one product mix card.');
        }

        $user->forceFill([
            'employee_product_mix_cards' => $selected,
        ])->save();

        return redirect()
            ->route('employee.settings.product-mix')
            ->with('success', 'Product mix cards updated.');
    }
}
