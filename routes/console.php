<?php

use App\Support\Permissions\PermissionSyncService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('permissions:sync {--skip-tenant-admins : Seed permissions only without re-syncing tenant admin roles}', function (PermissionSyncService $syncService) {
    $this->info('Syncing roles and permissions...');

    $result = $syncService->sync(
        syncTenantAdmins: ! $this->option('skip-tenant-admins')
    );

    $this->info('Roles and permissions synced successfully.');
    $this->line('Tenant admin users synced: '.$result['tenant_admins_synced']);
})->purpose('Sync roles, permissions, and tenant admin role assignments');
