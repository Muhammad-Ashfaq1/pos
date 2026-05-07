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
        User::MANAGER => 'manager',
        User::CASHIER => 'cashier',
        User::TECHNICIAN => 'technician',
        User::INVENTORY_CLERK => 'inventory',
    ];

    public function run(): void
    {
        if (app()->environment('production') && ! app()->runningUnitTests()) {
            $this->command?->warn('Skipping demo tenant role users in production.');

            return;
        }

        app(PermissionSyncService::class)->sync(syncTenantAdmins: false);

        Tenant::query()
            ->orderBy('id')
            ->get()
            ->each(function (Tenant $tenant): void {
                foreach (self::ROLE_EMAIL_PREFIXES as $role => $emailPrefix) {
                    $email = sprintf('%s%d@pos.com', $emailPrefix, $tenant->id);
                    $user = User::query()->firstOrNew(['email' => $email]);

                    $user->fill([
                        'name' => $user->name ?: $this->defaultName($tenant, $role),
                        'email' => $email,
                        'password' => 'password',
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

    private function defaultName(Tenant $tenant, string $role): string
    {
        return sprintf(
            'Shop %d %s',
            $tenant->id,
            Str::of($role)->replace('_', ' ')->title()
        );
    }
}
