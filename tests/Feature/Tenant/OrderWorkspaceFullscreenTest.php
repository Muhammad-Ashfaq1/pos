<?php

namespace Tests\Feature\Tenant;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions\PermissionTeamScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OrderWorkspaceFullscreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_order_pages_include_the_fullscreen_toggle(): void
    {
        [, $user] = $this->createTenantAdminWithOrderAccess();

        $this->actingAs($user)
            ->get(route('tenant.order.index'))
            ->assertOk()
            ->assertSee('data-workspace-fullscreen', false)
            ->assertSee('pos-workspace-fullscreen.js', false);

        $this->actingAs($user)
            ->get(route('tenant.order.new-order'))
            ->assertOk()
            ->assertSee('data-workspace-fullscreen', false);

        $this->actingAs($user)
            ->get(route('tenant.invoices.index'))
            ->assertOk()
            ->assertSee('data-workspace-fullscreen', false);
    }

    public function test_employee_order_pages_do_not_include_the_admin_fullscreen_toggle(): void
    {
        [$tenant] = $this->createTenantAdminWithOrderAccess();

        $employee = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::EMPLOYEE,
            'is_active' => true,
        ]);

        PermissionTeamScope::for($tenant->id, function () use ($employee): void {
            $employee->givePermissionTo('orders.view');
        });

        $this->actingAs($employee)
            ->get(route('employee.order.index'))
            ->assertOk()
            ->assertDontSee('data-workspace-fullscreen', false);
    }

    /**
     * @return array{0: Tenant, 1: User}
     */
    private function createTenantAdminWithOrderAccess(): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate('orders.view', 'web');
        Permission::findOrCreate('orders.create', 'web');

        $tenant = Tenant::create([
            'name' => 'Fullscreen Lube',
            'slug' => 'fullscreen-lube',
            'owner_name' => 'Shop Owner',
            'owner_email' => 'owner@fullscreen.test',
            'owner_phone' => '+1 555 100 1000',
            'business_name' => 'Fullscreen Lube',
            'business_email' => 'owner@fullscreen.test',
            'business_phone' => '+1 555 100 1000',
            'shop_name' => 'Fullscreen Lube',
            'email' => 'owner@fullscreen.test',
            'phone' => '+1 555 100 1000',
            'status' => TenantStatus::Approved->value,
            'approved_at' => now(),
            'onboarding_status' => 'in_progress',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::TENANT_ADMIN,
            'is_active' => true,
        ]);

        PermissionTeamScope::for($tenant->id, function () use ($user): void {
            $user->givePermissionTo(['orders.view', 'orders.create']);
        });

        return [$tenant, $user];
    }
}
