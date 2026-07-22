<?php

namespace Tests\Feature\Tenant;

use App\Enums\TenantStatus;
use App\Models\Card;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions\PermissionTeamScope;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CardSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_admin_can_create_gift_card(): void
    {
        [, $user] = $this->makeTenantAdminWithCardPermissions(['cards.create', 'cards.manage']);

        $response = $this->actingAs($user)->postJson(
            route('tenant.ecommerce.cards.save', ['type' => Card::TYPE_GIFT]),
            [
                'name' => 'Welcome Gift',
                'value' => 40,
                'minimum_spend' => 10,
                'valid_until' => now()->addMonth()->toDateString(),
                'is_active' => 1,
                'product_ids' => [],
            ]
        );

        $response->assertOk()
            ->assertJsonPath('message', 'Gift Card created successfully.');

        $this->assertDatabaseHas('cards', [
            'tenant_id' => $user->tenant_id,
            'name' => 'Welcome Gift',
            'card_type' => Card::TYPE_GIFT,
        ]);
    }

    public function test_tenant_create_rejects_past_valid_until(): void
    {
        [, $user] = $this->makeTenantAdminWithCardPermissions(['cards.create', 'cards.manage']);

        $response = $this->actingAs($user)->postJson(
            route('tenant.ecommerce.cards.save', ['type' => Card::TYPE_GIFT]),
            [
                'name' => 'Past Gift',
                'value' => 20,
                'minimum_spend' => 0,
                'valid_until' => now()->subDays(3)->toDateString(),
                'is_active' => 1,
                'product_ids' => [],
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['valid_until']);
    }

    public function test_tenant_edit_allows_keeping_existing_past_valid_until(): void
    {
        [$tenant, $user] = $this->makeTenantAdminWithCardPermissions([
            'cards.create',
            'cards.update',
            'cards.manage',
        ]);

        $pastDate = now()->subDays(5)->toDateString();

        app(TenantContext::class)->initialize($tenant);
        $card = Card::query()->create([
            'card_type' => Card::TYPE_GIFT,
            'name' => 'Legacy Gift',
            'value' => 30,
            'minimum_spend' => 0,
            'valid_until' => $pastDate,
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        app(TenantContext::class)->end();

        $response = $this->actingAs($user)->postJson(
            route('tenant.ecommerce.cards.save', ['type' => Card::TYPE_GIFT]),
            [
                'id' => $card->id,
                'name' => 'Legacy Gift Updated',
                'value' => 35,
                'minimum_spend' => 5,
                'valid_until' => $pastDate,
                'is_active' => 1,
                'product_ids' => [],
            ]
        );

        $response->assertOk()
            ->assertJsonPath('message', 'Gift Card updated successfully.');

        $card->refresh();

        $this->assertSame('Legacy Gift Updated', $card->name);
        $this->assertSame($pastDate, $card->valid_until?->format('Y-m-d'));
    }

    public function test_tenant_edit_rejects_new_past_valid_until_different_from_existing(): void
    {
        [$tenant, $user] = $this->makeTenantAdminWithCardPermissions([
            'cards.update',
            'cards.manage',
        ]);

        $existingPast = now()->subDays(5)->toDateString();
        $otherPast = now()->subDays(10)->toDateString();

        app(TenantContext::class)->initialize($tenant);
        $card = Card::query()->create([
            'card_type' => Card::TYPE_GIFT,
            'name' => 'Legacy Gift',
            'value' => 30,
            'minimum_spend' => 0,
            'valid_until' => $existingPast,
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        app(TenantContext::class)->end();

        $response = $this->actingAs($user)->postJson(
            route('tenant.ecommerce.cards.save', ['type' => Card::TYPE_GIFT]),
            [
                'id' => $card->id,
                'name' => 'Legacy Gift',
                'value' => 30,
                'minimum_spend' => 0,
                'valid_until' => $otherPast,
                'is_active' => 1,
                'product_ids' => [],
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['valid_until']);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Tenant, 1: User}
     */
    private function makeTenantAdminWithCardPermissions(array $permissions): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['cards.view', 'cards.create', 'cards.update', 'cards.delete', 'cards.manage'] as $name) {
            Permission::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $tenant = Tenant::create([
            'name' => 'Admin Card Shop',
            'slug' => 'card-shop-admin',
            'owner_name' => 'Owner',
            'owner_email' => 'owner@card-shop-admin.test',
            'owner_phone' => '+1 555 000 0000',
            'business_name' => 'Admin Card Shop',
            'business_email' => 'biz@card-shop-admin.test',
            'business_phone' => '+1 555 000 0000',
            'shop_name' => 'Admin Card Shop',
            'email' => 'shop@card-shop-admin.test',
            'phone' => '+1 555 000 0000',
            'status' => TenantStatus::Approved->value,
            'approved_at' => now(),
            'onboarding_status' => 'completed',
        ]);

        $user = User::create([
            'name' => 'Tenant Admin',
            'email' => 'admin-cards@example.com',
            'password' => 'secret',
            'tenant_id' => $tenant->id,
            'role' => User::TENANT_ADMIN,
            'is_active' => true,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        PermissionTeamScope::for($tenant->id, function () use ($user, $permissions): void {
            $user->givePermissionTo($permissions);
        });

        return [$tenant, $user];
    }
}
