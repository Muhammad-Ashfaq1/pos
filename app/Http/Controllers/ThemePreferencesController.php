<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AppTheme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ThemePreferencesController extends Controller
{
    /**
     * Save the authenticated user's personal theme choice.
     *
     * Mode is always stored. Variant is stored only when named (or when dark
     * mode forces a dark variant); otherwise the row keeps inheriting.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $data = $request->validate([
            'theme_mode' => ['required', Rule::in(AppTheme::MODES)],
            'theme_variant' => ['nullable', Rule::in(AppTheme::VARIANTS)],
        ]);

        $mode = $data['theme_mode'];
        $variant = AppTheme::personalVariantToStore(
            $data['theme_variant'] ?? null,
            $user->theme_variant,
            $mode,
        );

        $user->forceFill([
            'theme_mode' => $mode,
            'theme_variant' => $variant,
        ])->save();

        $resolved = AppTheme::forUser($user->fresh());

        return response()->json([
            'ok' => true,
            'theme' => [
                'variant' => $resolved['variant'],
                'mode' => $resolved['mode'],
                'bs_theme' => $resolved['bs_theme'],
                'source' => $resolved['source'],
                'stored_variant' => $variant,
            ],
        ]);
    }

    /**
     * Save the shop's default theme (tenant admins / managers with access).
     */
    public function updateTenant(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->tenant_id) {
            abort(403);
        }

        if (! ($user->isTenantAdmin() || $user->isManager() || $user->isSuperAdmin())) {
            abort(403);
        }

        $data = $request->validate([
            'theme_mode' => ['nullable', Rule::in(AppTheme::MODES)],
            'theme_variant' => ['nullable', Rule::in(AppTheme::VARIANTS)],
        ]);

        $tenant = $user->tenant;
        abort_unless($tenant, 404);

        $variant = $data['theme_variant'] ?? null;
        $mode = $data['theme_mode'] ?? null;

        if ($variant !== null && $mode !== null) {
            $variant = AppTheme::pairWithMode($variant, $mode);
        } elseif ($variant !== null && $tenant->theme_mode) {
            $variant = AppTheme::pairWithMode($variant, $tenant->theme_mode);
        }

        $tenant->forceFill([
            'theme_variant' => $variant ?? $tenant->theme_variant,
            'theme_mode' => $mode ?? $tenant->theme_mode,
        ])->save();

        AppTheme::forgetTenant((int) $tenant->id);

        return response()->json([
            'ok' => true,
            'theme' => AppTheme::forTenant((int) $tenant->id),
        ]);
    }
}
