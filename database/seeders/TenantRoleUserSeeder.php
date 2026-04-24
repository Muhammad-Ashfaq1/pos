<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions\PermissionSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantRoleUserSeeder extends Seeder
{
    private const ROLE_EMAIL_PREFIXES = [
        User::TENANT_ADMIN => 'tenant-admin',
        User::MANAGER => 'manager',
        User::CASHIER => 'cashier',
        User::TECHNICIAN => 'technician',
        User::INVENTORY_CLERK => 'inventory',
        User::EMPLOYEE => 'employee',
    ];

    public function run(): void
    {
        if (app()->environment('production') && ! app()->runningUnitTests()) {
            $this->command?->warn('Skipping demo tenant role users in production.');

            return;
        }

        app(PermissionSyncService::class)->sync(syncTenantAdmins: false);

        $password = env('TENANT_DEMO_PASSWORD', 'password');

        Tenant::query()
            ->orderBy('id')
            ->get()
            ->each(function (Tenant $tenant) use ($password): void {
                foreach (self::ROLE_EMAIL_PREFIXES as $role => $emailPrefix) {
                    $user = $this->resolveUser($tenant, $role, $emailPrefix);

                    $user->fill([
                        'name' => $user->exists ? $user->name : $this->defaultName($tenant, $role),
                        'email' => $user->email ?: $this->emailFor($tenant, $emailPrefix),
                        'password' => $password,
                        'tenant_id' => $tenant->id,
                        'role' => $role,
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]);

                    $user->save();
                    $user->assignPrimaryRole($role, $tenant->id);
                }
            });
    }

    private function resolveUser(Tenant $tenant, string $role, string $emailPrefix): User
    {
        if ($role === User::TENANT_ADMIN) {
            $existingTenantAdmin = User::query()
                ->where('tenant_id', $tenant->id)
                ->where('role', $role)
                ->orderBy('id')
                ->first();

            if ($existingTenantAdmin) {
                return $existingTenantAdmin;
            }
        }

        return User::query()->firstOrNew([
            'email' => $this->emailFor($tenant, $emailPrefix),
        ]);
    }

    private function emailFor(Tenant $tenant, string $prefix): string
    {
        return sprintf('%s+%s@example.com', $prefix, $tenant->id);
    }

    private function defaultName(Tenant $tenant, string $role): string
    {
        return sprintf(
            '%s %s',
            $tenant->display_name,
            Str::of($role)->replace('_', ' ')->title()
        );
    }
}
