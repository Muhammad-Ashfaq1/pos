<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;

/**
 * Resolves the current user's theme preference into the
 * `data-bs-theme` and `data-pos-theme` attributes the layouts use.
 *
 * Theme keys:
 *   sky / lake / eggplant — light variants (data-bs-theme=light)
 *   dark                  — dark variant   (data-bs-theme=dark)
 *   high-contrast         — dark PLUS an overlay: the element wears
 *                           `pos-theme-dark pos-theme-high-contrast` together,
 *                           so every dark rule applies and the overlay only has
 *                           to raise contrast. See classesFor().
 *
 * Theme mode (from the dropdown, separate axis):
 *   light  — force light variant
 *   dark   — force dark variant
 *   system — follow `prefers-color-scheme`; resolved client-side, server falls back to the saved variant
 */
class AppTheme
{
    /** Variants the user can actually pick / save. */
    public const VARIANTS = ['sky', 'lake', 'eggplant', 'dark', 'high-contrast'];

    /** Variants that render on a dark surface. */
    public const DARK_VARIANTS = ['dark', 'high-contrast'];

    public const MODES = ['light', 'dark', 'system'];

    public const DEFAULT_VARIANT = 'lake';

    public const DEFAULT_MODE = 'light';

    /**
     * Resolve theme for whatever is signed in (staff User or portal Customer).
     *
     * @return array{variant: string, mode: string, bs_theme: string, classes: string, source: string}
     */
    public static function resolve(?object $actor = null): array
    {
        if ($actor instanceof User) {
            return self::forUser($actor);
        }

        if ($actor instanceof Customer && $actor->tenant_id) {
            $tenant = self::forTenant((int) $actor->tenant_id);
            $variant = $tenant['variant'] ?? self::DEFAULT_VARIANT;
            $mode = $tenant['mode'] ?? self::DEFAULT_MODE;

            return [
                'variant' => $variant,
                'mode' => $mode,
                'bs_theme' => self::resolveBsTheme($variant, $mode),
                'classes' => self::classesFor($variant),
                'source' => $tenant['variant'] !== null ? 'tenant' : 'default',
            ];
        }

        return [
            'variant' => self::DEFAULT_VARIANT,
            'mode' => self::DEFAULT_MODE,
            'bs_theme' => self::resolveBsTheme(self::DEFAULT_VARIANT, self::DEFAULT_MODE),
            'classes' => self::classesFor(self::DEFAULT_VARIANT),
            'source' => 'default',
        ];
    }

    /**
     * The theme this user sees, in three steps:
     *
     *   1. their OWN choice, if they have made one;
     *   2. otherwise their organization's theme, if it has one;
     *   3. otherwise the platform default.
     *
     * @return array{variant: string, mode: string, bs_theme: string, classes: string, source: string}
     */
    public static function forUser(?User $user): array
    {
        $variant = null;
        $mode = null;
        $source = 'default';

        if ($user) {
            if (in_array($user->theme_variant, self::VARIANTS, true)) {
                $variant = $user->theme_variant;
                $source = 'user';
            }
            if (in_array($user->theme_mode, self::MODES, true)) {
                $mode = $user->theme_mode;
            }
        }

        if ($variant === null && $user?->tenant_id) {
            $tenant = self::forTenant((int) $user->tenant_id);
            if ($tenant['variant'] !== null) {
                $variant = $tenant['variant'];
                $mode ??= $tenant['mode'];
                $source = 'tenant';
            }
        }

        $variant ??= self::DEFAULT_VARIANT;
        $mode ??= self::DEFAULT_MODE;

        return [
            'variant' => $variant,
            'mode' => $mode,
            'bs_theme' => self::resolveBsTheme($variant, $mode),
            'classes' => self::classesFor($variant),
            'source' => $source,
        ];
    }

    /**
     * An organization's own theme, or nulls when it has never chosen one.
     *
     * @return array{variant: ?string, mode: ?string}
     */
    public static function forTenant(int $tenantId): array
    {
        if (array_key_exists($tenantId, self::$tenantMemo)) {
            return self::$tenantMemo[$tenantId];
        }

        $resolved = ['variant' => null, 'mode' => null];

        try {
            $row = Tenant::query()
                ->whereKey($tenantId)
                ->first(['theme_variant', 'theme_mode']);

            if ($row !== null) {
                if (in_array($row->theme_variant, self::VARIANTS, true)) {
                    $resolved['variant'] = $row->theme_variant;
                }
                if (in_array($row->theme_mode, self::MODES, true)) {
                    $resolved['mode'] = $row->theme_mode;
                }
            }
        } catch (\Throwable) {
            // Column not migrated yet, or a storage blip: no organization theme.
        }

        return self::$tenantMemo[$tenantId] = $resolved;
    }

    /** Drop the per-request tenant cache — used after saving an organization theme. */
    public static function forgetTenant(?int $tenantId = null): void
    {
        if ($tenantId === null) {
            self::$tenantMemo = [];

            return;
        }

        unset(self::$tenantMemo[$tenantId]);
    }

    /** @var array<int, array{variant: ?string, mode: ?string}> */
    private static array $tenantMemo = [];

    /**
     * The `pos-theme-*` classes the <html> element wears.
     *
     * High contrast is dark plus an overlay, not a separate theme.
     */
    public static function classesFor(string $variant): string
    {
        return $variant === 'high-contrast'
            ? 'pos-theme-dark pos-theme-high-contrast'
            : 'pos-theme-'.$variant;
    }

    /**
     * Keep a variant and a mode consistent with one another.
     *
     * **`system` is deliberately left alone.** It is not a synonym for light.
     */
    public static function pairWithMode(string $variant, string $mode): string
    {
        if ($mode === 'dark' && ! in_array($variant, self::DARK_VARIANTS, true)) {
            return 'dark';
        }

        if ($mode === 'light' && in_array($variant, self::DARK_VARIANTS, true)) {
            return self::DEFAULT_VARIANT;
        }

        return $variant;
    }

    /**
     * What a user's PERSONAL row should store, given what they just asked for.
     *
     * Returning null means "keep inheriting".
     *
     * @param  ?string  $requested  variant named in the request, or null
     * @param  ?string  $stored     variant already on the personal row, or null
     * @return ?string  variant to store; null keeps the row inheriting
     */
    public static function personalVariantToStore(?string $requested, ?string $stored, string $mode): ?string
    {
        if ($requested !== null && in_array($requested, self::VARIANTS, true)) {
            return self::pairWithMode($requested, $mode);
        }

        if ($stored !== null && in_array($stored, self::VARIANTS, true)) {
            return self::pairWithMode($stored, $mode);
        }

        return $mode === 'dark' ? 'dark' : null;
    }

    public static function resolveBsTheme(string $variant, string $mode): string
    {
        if ($mode === 'dark') {
            return 'dark';
        }

        return in_array($variant, self::DARK_VARIANTS, true) ? 'dark' : 'light';
    }
}
