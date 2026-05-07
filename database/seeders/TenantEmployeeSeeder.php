<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions\PermissionSyncService;
use Illuminate\Database\Seeder;

class TenantEmployeeSeeder extends Seeder
{
    private const EMPLOYEES_PER_SHOP = 9;

    public function run(): void
    {
        if (app()->environment('production') && ! app()->runningUnitTests()) {
            $this->command?->warn('Skipping demo employee users in production.');

            return;
        }

        app(PermissionSyncService::class)->sync(syncTenantAdmins: false);

        Tenant::query()
            ->orderBy('id')
            ->get()
            ->each(function (Tenant $tenant): void {
                for ($employeeNumber = 1; $employeeNumber <= self::EMPLOYEES_PER_SHOP; $employeeNumber++) {
                    $email = sprintf('employee%d%d@pos.com', $tenant->id, $employeeNumber);

                    $user = User::query()->firstOrNew(['email' => $email]);

                    $user->fill([
                        'name' => $user->name ?: sprintf('Shop %d Employee %d', $tenant->id, $employeeNumber),
                        'email' => $email,
                        'password' => 'password',
                        'tenant_id' => $tenant->id,
                        'role' => User::EMPLOYEE,
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]);

                    $user->save();
                    $user->assignPrimaryRole(User::EMPLOYEE, $tenant->id);
                }
            });
    }
}
