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
                    $email = $role === User::EMPLOYEE
                        ? $this->employeeEmailFor($tenant)
                        : $this->emailFor($tenant, $emailPrefix);
                    $user = User::query()->firstOrNew(['email' => $email]);

                    $user->fill([
                        'name' => $user->name ?: $this->defaultName($tenant, $role),
                        'email' => $email,
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

    private function emailFor(Tenant $tenant, string $prefix): string
    {
        return sprintf('%s+%s@example.com', $prefix, $tenant->id);
    }

    private function employeeEmailFor(Tenant $tenant): string
    {
        return sprintf('employee%s@gmail.com', $tenant->id);
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
