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

class ShopSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_tenant_user_can_view_shop_settings_page(): void
    {
        [$tenant, $user] = $this->createTenantUserWithSettingsPermission();

        $response = $this->actingAs($user)->get(route('tenant.settings.shop-profile.edit'));

        $response->assertOk();
        $response->assertSee('Shop Settings');
        $response->assertSee($tenant->display_name);
    }

    public function test_shop_settings_update_persists_tenant_profile_and_settings_payload(): void
    {
        [$tenant, $user] = $this->createTenantUserWithSettingsPermission();

        $response = $this->actingAs($user)->put(route('tenant.settings.shop-profile.update'), [
            'shop_name' => 'Prime Lube Express',
            'business_name' => 'Prime Lube Holdings',
            'owner_name' => 'Areeba Khan',
            'business_email' => 'hello@primelube.test',
            'business_phone' => '+1 555 200 1111',
            'website_url' => 'https://primelube.test',
            'address' => '245 Service Lane',
            'city' => 'Dallas',
            'state' => 'Texas',
            'country' => 'USA',
            'currency' => 'USD',
            'timezone' => 'America/Chicago',
            'locale' => 'en_US',
            'tax_name' => 'Sales Tax',
            'tax_percentage' => '8.25',
            'invoice_prefix' => 'PLX',
            'invoice_next_number' => 145,
            'low_stock_threshold' => 7,
            'reminder_email_enabled' => '1',
            'receipt_email_enabled' => '1',
            'loyalty_enabled' => '1',
            'loyalty_points_per_currency' => '1.50',
            'active_tab' => 'notifications',
            'business_hours' => [
                'monday' => ['is_closed' => '0', 'open' => '08:00', 'close' => '18:00'],
                'tuesday' => ['is_closed' => '0', 'open' => '08:00', 'close' => '18:00'],
                'wednesday' => ['is_closed' => '0', 'open' => '08:00', 'close' => '18:00'],
                'thursday' => ['is_closed' => '0', 'open' => '08:00', 'close' => '18:00'],
                'friday' => ['is_closed' => '0', 'open' => '08:00', 'close' => '18:00'],
                'saturday' => ['is_closed' => '0', 'open' => '09:00', 'close' => '14:00'],
                'sunday' => ['is_closed' => '1', 'open' => null, 'close' => null],
            ],
        ]);

        $response->assertRedirect(route('tenant.settings.shop-profile.edit'));
        $response->assertSessionHas('success', 'Shop settings updated successfully.');

        $tenant->refresh();

        $this->assertSame('Prime Lube Express', $tenant->shop_name);
        $this->assertSame('Prime Lube Holdings', $tenant->business_name);
        $this->assertSame('Areeba Khan', $tenant->owner_name);
        $this->assertSame('hello@primelube.test', $tenant->business_email);
        $this->assertSame('USD', data_get($tenant->settings, 'regional.currency'));
        $this->assertSame('America/Chicago', data_get($tenant->settings, 'regional.timezone'));
        $this->assertSame('8.25', data_get($tenant->settings, 'tax.percentage'));
        $this->assertSame('PLX', data_get($tenant->settings, 'invoice.prefix'));
        $this->assertSame(145, data_get($tenant->settings, 'invoice.next_number'));
        $this->assertSame(7, data_get($tenant->settings, 'inventory.low_stock_threshold'));
        $this->assertTrue((bool) data_get($tenant->settings, 'notifications.reminder_email_enabled'));
        $this->assertTrue((bool) data_get($tenant->settings, 'loyalty.enabled'));
        $this->assertSame('1.50', data_get($tenant->settings, 'loyalty.points_per_currency'));
        $this->assertTrue((bool) data_get($tenant->settings, 'business_hours.sunday.is_closed'));
        $this->assertNotNull($tenant->onboarding_completed_at);
        $this->assertSame('completed', $tenant->onboarding_status);
    }

    private function createTenantUserWithSettingsPermission(): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate('settings.manage', 'web');

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
            $user->givePermissionTo('settings.manage');
        });

        return [$tenant, $user];
    }
}
