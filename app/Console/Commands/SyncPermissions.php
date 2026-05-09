<?php

namespace App\Console\Commands;

use App\Support\Permissions\PermissionSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-permissions {--skip-tenant-admins : Seed permissions only without re-syncing tenant admin roles}')]
#[Description('Sync roles, permissions, and tenant admin role assignments')]
class SyncPermissions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(PermissionSyncService $syncService): int
    {
        $this->info('Syncing roles and permissions...');

        $result = $syncService->sync(
            syncTenantAdmins: ! $this->option('skip-tenant-admins')
        );

        $this->info('Roles and permissions synced successfully.');
        $this->line('Super admin users synced: '.$result['super_admins_synced']);
        $this->line('Tenant admin users synced: '.$result['tenant_admins_synced']);
        $this->line('Tenant role users synced: '.$result['tenant_role_users_synced']);

        return self::SUCCESS;
    }
}
