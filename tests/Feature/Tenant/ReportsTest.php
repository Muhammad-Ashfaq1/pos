<?php

namespace Tests\Feature\Tenant;

use App\Enums\TenantStatus;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions\PermissionTeamScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_view_the_sales_report_page(): void
    {
        [, $user] = $this->createTenantUserWithReports();

        $response = $this->actingAs($user)->get(route('tenant.reports.index', 'sales'));

        $response->assertOk();
        $response->assertSee('Sales Report');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        [$tenant] = $this->createTenantUserWithReports();

        $stranger = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::CASHIER,
            'is_active' => true,
        ]);

        $this->actingAs($stranger)
            ->get(route('tenant.reports.index', 'sales'))
            ->assertForbidden();
    }

    public function test_sales_data_endpoint_respects_the_date_range(): void
    {
        [$tenant, $user] = $this->createTenantUserWithReports();

        $this->makeOrder($tenant, 'ORD-IN', 100, Carbon::parse('2026-01-15'));
        $this->makeOrder($tenant, 'ORD-OUT', 250, Carbon::parse('2026-03-15'));

        $response = $this->actingAs($user)->getJson(route('tenant.reports.data', 'sales').'?'.http_build_query([
            'period' => 'custom',
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
            'draw' => 1,
        ]));

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 1);
        $response->assertJsonPath('data.0.order_number', 'ORD-IN');
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data', 'summary']);
    }

    public function test_sales_data_endpoint_applies_a_status_filter(): void
    {
        [$tenant, $user] = $this->createTenantUserWithReports();

        $this->makeOrder($tenant, 'ORD-PAID', 100, Carbon::now(), Order::STATUS_PAID);
        $this->makeOrder($tenant, 'ORD-PENDING', 100, Carbon::now(), Order::STATUS_PENDING);

        $response = $this->actingAs($user)->getJson(route('tenant.reports.data', 'sales').'?'.http_build_query([
            'period' => 'year',
            'status' => Order::STATUS_PAID,
        ]));

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 1);
        $response->assertJsonPath('data.0.order_number', 'ORD-PAID');
    }

    public function test_estimates_are_excluded_from_the_sales_report(): void
    {
        [$tenant, $user] = $this->createTenantUserWithReports();

        $this->makeOrder($tenant, 'EST-1', 100, Carbon::now(), Order::STATUS_ESTIMATE);

        $response = $this->actingAs($user)->getJson(route('tenant.reports.data', 'sales').'?'.http_build_query([
            'period' => 'year',
        ]));

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 0);
    }

    public function test_export_returns_an_xlsx_download(): void
    {
        [$tenant, $user] = $this->createTenantUserWithReports();
        $this->makeOrder($tenant, 'ORD-1', 100, Carbon::now(), Order::STATUS_PAID);

        $response = $this->actingAs($user)->get(route('tenant.reports.export', 'sales').'?period=year');

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml',
            (string) $response->headers->get('content-type'),
        );
    }

    public function test_unknown_report_key_returns_not_found(): void
    {
        [, $user] = $this->createTenantUserWithReports();

        $this->actingAs($user)
            ->get(route('tenant.reports.index', 'does-not-exist'))
            ->assertNotFound();
    }

    private function makeOrder(Tenant $tenant, string $number, float $total, Carbon $createdAt, string $status = Order::STATUS_PAID): Order
    {
        $order = new Order([
            'order_number' => $number,
            'status' => $status,
            'total_amount' => $total,
            'payment_amount' => $total,
        ]);

        $order->forceFill([
            'tenant_id' => $tenant->id,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'paid_at' => $status === Order::STATUS_PAID ? $createdAt : null,
        ])->saveQuietly();

        return $order;
    }

    private function createTenantUserWithReports(): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate('reports.view', 'web');

        $tenant = Tenant::create([
            'name' => 'Rapid Lube Central',
            'slug' => 'rapid-lube-central',
            'owner_name' => 'Shop Owner',
            'owner_email' => 'owner@rapidlube.test',
            'owner_phone' => '+1 555 100 1000',
            'business_name' => 'Rapid Lube Central',
            'business_email' => 'owner@rapidlube.test',
            'business_phone' => '+1 555 100 1000',
            'shop_name' => 'Rapid Lube Central',
            'email' => 'owner@rapidlube.test',
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
            $user->givePermissionTo('reports.view');
        });

        return [$tenant, $user];
    }
}
