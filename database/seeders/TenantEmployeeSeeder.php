<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions\PermissionSyncService;
use Illuminate\Database\Seeder;

class TenantEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production') && ! app()->runningUnitTests()) {
            $this->command?->warn('Skipping demo employee users in production.');

            return;
        }

        app(PermissionSyncService::class)->sync(syncTenantAdmins: false);

        $password = env('TENANT_DEMO_PASSWORD', 'password');

        Tenant::query()
            ->orderBy('id')
            ->get()
            ->each(function (Tenant $tenant) use ($password): void {
                $email = sprintf('employee%s@gmail.com', $tenant->id);

                $user = User::query()->firstOrNew(['email' => $email]);

                $user->fill([
                    'name' => $user->name ?: sprintf('%s Employee', $tenant->display_name),
                    'email' => $email,
                    'password' => $password,
                    'tenant_id' => $tenant->id,
                    'role' => User::EMPLOYEE,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                $user->save();
                $user->assignPrimaryRole(User::EMPLOYEE, $tenant->id);
            });
    }
}
