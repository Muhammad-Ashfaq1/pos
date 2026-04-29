<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions\PermissionSyncService;
use Illuminate\Database\Seeder;

class TenantEmployeeSeeder extends Seeder
{
    private const DEFAULT_EMPLOYEES_PER_SHOP = 3;

    public function run(): void
    {
        if (app()->environment('production') && ! app()->runningUnitTests()) {
            $this->command?->warn('Skipping demo employee users in production.');

            return;
        }

        app(PermissionSyncService::class)->sync(syncTenantAdmins: false);

        $password = env('TENANT_DEMO_PASSWORD', 'password');
        $employeesPerShop = max((int) env('DEMO_EMPLOYEES_PER_SHOP', self::DEFAULT_EMPLOYEES_PER_SHOP), 1);

        Tenant::query()
            ->orderBy('id')
            ->get()
            ->each(function (Tenant $tenant) use ($password, $employeesPerShop): void {
                for ($employeeNumber = 1; $employeeNumber <= $employeesPerShop; $employeeNumber++) {
                    $email = $this->employeeEmailFor($tenant->id, $employeeNumber);

                    $user = User::query()->firstOrNew(['email' => $email]);

                    $user->fill([
                        'name' => $user->name ?: sprintf('%s Employee %d', $tenant->display_name, $employeeNumber),
                        'email' => $email,
                        'password' => $password,
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

    private function employeeEmailFor(int $tenantId, int $employeeNumber): string
    {
        return sprintf('employee%d+shop%d@example.com', $employeeNumber, $tenantId);
    }
}
