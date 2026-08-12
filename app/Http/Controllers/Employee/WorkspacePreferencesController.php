<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Support\EmployeeNavigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkspacePreferencesController extends Controller
{
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
}
