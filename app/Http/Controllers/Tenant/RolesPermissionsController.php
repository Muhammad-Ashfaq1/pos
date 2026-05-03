<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Interface\ShopSettingsRepositoryInterface;
use App\Support\Permissions\PermissionTeamScope;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsController extends Controller
{
    private const PROTECTED_ROLES = [
        User::SUPER_ADMIN,
        User::TENANT_ADMIN,
    ];

    private const PERMISSION_GROUPS = [
        'Dashboard' => ['dashboard.view'],
        'Categories' => ['category.view', 'category.create', 'category.update', 'category.delete'],
        'Sub Categories' => ['subcategory.view', 'subcategory.create', 'subcategory.update', 'subcategory.delete'],
        'Products' => ['product.view', 'product.create', 'product.update', 'product.delete', 'product.adjust_stock', 'products.view', 'products.manage'],
        'Services' => ['service.view', 'service.create', 'service.update', 'service.delete', 'services.view', 'services.manage'],
        'Inventory' => ['inventory.view', 'inventory.manage'],
        'POS' => ['pos.bill'],
        'Discounts' => ['discount.manage', 'discount.apply_bill', 'discount.apply_item', 'discounts.manage'],
        'Refunds' => ['refunds.manage'],
        'Customers' => ['customer.view', 'customer.create', 'customer.update', 'customer.delete', 'customers.view', 'customers.manage'],
        'Vehicles' => ['vehicle.view', 'vehicle.create', 'vehicle.update', 'vehicle.delete', 'vehicles.view', 'vehicles.manage'],
        'Reminders' => ['reminders.manage'],
        'Reports & Audit' => ['reports.view', 'audit-logs.view'],
        'Users' => ['users.view', 'users.create', 'users.update', 'users.delete'],
        'Roles & Staff' => ['roles.view', 'roles.manage'],
        'Settings' => ['settings.manage'],
    ];

    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly ShopSettingsRepositoryInterface $settingsRepo,
    ) {}

    public function index(): View
    {
        $tenant = $this->currentTenant();
        $tenantId = $tenant->getKey();

        $roles = PermissionTeamScope::for($tenantId, function () {
            return Role::query()
                ->whereNotIn('name', self::PROTECTED_ROLES)
                ->where('guard_name', 'web')
                ->get();
        });

        $permissionGroups = $this->buildPermissionGroups();

        $staff = User::query()
            ->where('tenant_id', $tenantId)
            ->where('role', '!=', User::TENANT_ADMIN)
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get();

        $settingsSections = $this->settingsSections();

        return view('tenant.settings.roles-permissions.index', compact(
            'roles',
            'permissionGroups',
            'staff',
            'tenant',
            'settingsSections',
        ));
    }

    public function rolePermissions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', 'integer'],
        ]);

        $tenantId = $this->currentTenant()->getKey();

        $rolePermissions = PermissionTeamScope::for($tenantId, function () use ($validated) {
            $role = Role::query()->findOrFail($validated['role_id']);

            return $role->permissions->pluck('name')->toArray();
        });

        return response()->json(['permissions' => $rolePermissions]);
    }

    public function saveRole(Request $request): JsonResponse
    {
        $tenantId = $this->currentTenant()->getKey();

        $validator = Validator::make($request->all(), [
            'id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z_]+$/'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $validated = $validator->validated();

        if (in_array($validated['name'], self::PROTECTED_ROLES)) {
            return response()->json(['message' => 'Cannot create or modify protected roles.'], 403);
        }

        $role = PermissionTeamScope::for($tenantId, function () use ($validated) {
            if (! empty($validated['id'])) {
                $role = Role::query()->findOrFail($validated['id']);

                if (in_array($role->name, self::PROTECTED_ROLES)) {
                    abort(403, 'Cannot modify protected roles.');
                }

                $role->update(['name' => $validated['name']]);

                return $role;
            }

            return Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);
        });

        return response()->json([
            'message' => isset($validated['id']) ? 'Role updated.' : 'Role created.',
            'role' => ['id' => $role->id, 'name' => $role->name],
        ]);
    }

    public function deleteRole(Role $role): JsonResponse
    {
        if (in_array($role->name, self::PROTECTED_ROLES)) {
            return response()->json(['message' => 'Cannot delete protected roles.'], 403);
        }

        $tenantId = $this->currentTenant()->getKey();

        PermissionTeamScope::for($tenantId, function () use ($role) {
            $usersWithRole = User::query()
                ->where('tenant_id', $this->currentTenant()->getKey())
                ->where('role', $role->name)
                ->count();

            if ($usersWithRole > 0) {
                abort(422, "Cannot delete role: {$usersWithRole} user(s) are assigned to it.");
            }

            $role->delete();
        });

        return response()->json(['message' => 'Role deleted.']);
    }

    public function syncPermissions(Request $request): JsonResponse
    {
        $tenantId = $this->currentTenant()->getKey();

        $validated = $request->validate([
            'role_id' => ['required', 'integer'],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', 'max:100'],
        ]);

        $allPermissions = collect(self::PERMISSION_GROUPS)->flatten()->toArray();
        $filtered = array_intersect($validated['permissions'], $allPermissions);

        PermissionTeamScope::for($tenantId, function () use ($validated, $filtered) {
            $role = Role::query()->findOrFail($validated['role_id']);

            if (in_array($role->name, self::PROTECTED_ROLES)) {
                abort(403, 'Cannot modify permissions of protected roles.');
            }

            foreach ($filtered as $permName) {
                Permission::firstOrCreate(
                    ['name' => $permName, 'guard_name' => 'web'],
                );
            }

            $role->syncPermissions($filtered);
        });

        return response()->json(['message' => 'Permissions synced successfully.']);
    }

    public function staffListing(Request $request): JsonResponse
    {
        $tenantId = $this->currentTenant()->getKey();

        $staff = User::query()
            ->where('tenant_id', $tenantId)
            ->where('role', '!=', User::TENANT_ADMIN)
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'last_login_at' => $user->last_login_at?->diffForHumans(),
            ]);

        return response()->json(['data' => $staff]);
    }

    public function impersonateStaff(User $user): RedirectResponse
    {
        $tenant = $this->currentTenant();
        $currentUser = auth()->user();

        if ($user->tenant_id !== $tenant->getKey()) {
            return back()->with('error', 'User does not belong to this tenant.');
        }

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot impersonate yourself.');
        }

        if ($user->isTenantAdmin() || $user->isSuperAdmin()) {
            return back()->with('error', 'Cannot impersonate admin users.');
        }

        session(['impersonator_id' => $currentUser->id]);
        auth()->login($user);

        return redirect()->route('employee.dashboard')
            ->with('info', "You are now impersonating {$user->name}.");
    }

    private function currentTenant(): Tenant
    {
        return $this->tenantContext->current()
            ?? abort(404, 'Tenant context could not be resolved.');
    }

    private function buildPermissionGroups(): array
    {
        $groups = [];

        foreach (self::PERMISSION_GROUPS as $group => $permissions) {
            $groups[$group] = collect($permissions)->map(fn (string $p) => [
                'name' => $p,
                'label' => str($p)->replace('.', ' ')->replace('_', ' ')->title()->toString(),
            ])->toArray();
        }

        return $groups;
    }

    private function settingsSections(): array
    {
        return $this->settingsRepo->getSettingsSections();
    }
}
