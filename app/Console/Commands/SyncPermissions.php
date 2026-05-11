<?php

namespace App\Console\Commands;

use App\Support\Permissions\PermissionSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-permissions {--no-tenant-admins : Skip syncing existing tenant admin role assignments}')]
#[Description('Synchronize roles, permissions, and default role assignments')]
class SyncPermissions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(PermissionSyncService $permissionSyncService): int
    {
        $this->components->info('Syncing roles and permissions...');

        $result = $permissionSyncService->sync(
            syncTenantAdmins: ! $this->option('no-tenant-admins')
        );

        $this->info('Roles and permissions synced successfully.');
        $this->line('Super admin users synced: '.$result['super_admins_synced']);
        $this->line('Tenant admin users synced: '.$result['tenant_admins_synced']);
        $this->line('Tenant role users synced: '.$result['tenant_role_users_synced']);
        $this->components->info('Roles and permissions synced successfully.');

        if (! $this->option('no-tenant-admins')) {
            $this->line("Tenant admins synced: {$result['tenant_admins_synced']}");
        }

        return self::SUCCESS;
    }
}
