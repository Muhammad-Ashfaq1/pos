<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Support\EmployeeNavigation;
use App\Support\ProductMixCards;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkspacePreferencesController extends Controller
{
    public function settingsProductMix(): View
    {
        $user = request()->user();
        abort_unless($user?->isEmployee(), 403);

        $selected = ProductMixCards::selectedFor($user);

        return view('employee.settings.product-mix', [
            'groupedCards' => ProductMixCards::groupedCatalog(),
            'selectedKeys' => $selected,
            'maxSelected' => ProductMixCards::MAX_SELECTED,
            'selectedCount' => count($selected),
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
            'cards' => ['nullable', 'array', 'max:'.ProductMixCards::MAX_SELECTED],
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
