<?php

namespace Tests\Feature\Employee;

use App\Enums\TenantStatus;
use App\Models\Card;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions\PermissionTeamScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CardStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_create_discount_card(): void
    {
        [, $employee] = $this->makeEmployeeWithCardPermission('cards.create');

        $response = $this->actingAs($employee)->postJson(route('employee.cards.store'), [
            'card_type' => Card::TYPE_DISCOUNT,
            'name' => 'Summer Sale',
            'discount_type' => 'percentage',
            'value' => 15,
            'minimum_spend' => 50,
            'valid_until' => now()->addDays(10)->toDateString(),
            'product_ids' => [],
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Discount Card created successfully.')
            ->assertJsonPath('card_type', Card::TYPE_DISCOUNT)
            ->assertJsonStructure(['html', 'picker_html']);

        $this->assertDatabaseHas('cards', [
            'tenant_id' => $employee->tenant_id,
            'name' => 'Summer Sale',
            'card_type' => Card::TYPE_DISCOUNT,
            'discount_type' => 'percentage',
            'is_active' => 1,
        ]);
    }

    public function test_employee_cannot_create_card_with_past_valid_until(): void
    {
        [, $employee] = $this->makeEmployeeWithCardPermission('cards.create');

        $response = $this->actingAs($employee)->postJson(route('employee.cards.store'), [
            'card_type' => Card::TYPE_GIFT,
            'name' => 'Expired Gift',
            'value' => 25,
            'minimum_spend' => 0,
            'valid_until' => now()->subDay()->toDateString(),
            'product_ids' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['valid_until']);

        $this->assertDatabaseMissing('cards', [
            'name' => 'Expired Gift',
        ]);
    }

    public function test_employee_without_create_permission_cannot_store_card(): void
    {
        [, $employee] = $this->makeEmployeeWithCardPermission('cards.view');

        $response = $this->actingAs($employee)->postJson(route('employee.cards.store'), [
            'card_type' => Card::TYPE_REWARD,
            'name' => 'Blocked Reward',
            'value' => 100,
            'minimum_spend' => 0,
            'valid_until' => now()->addWeek()->toDateString(),
            'product_ids' => [],
        ]);

        $response->assertForbidden();
    }

    /**
     * @return array{0: Tenant, 1: User}
     */
    private function makeEmployeeWithCardPermission(string $permission): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate($permission, 'web');
        Permission::findOrCreate('cards.create', 'web');
        Permission::findOrCreate('cards.view', 'web');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $tenant = Tenant::create([
            'name' => 'Card Shop',
            'slug' => 'card-shop-employee',
            'owner_name' => 'Owner',
            'owner_email' => 'owner@card-shop-employee.test',
            'owner_phone' => '+1 555 000 0000',
            'business_name' => 'Card Shop',
            'business_email' => 'biz@card-shop-employee.test',
            'business_phone' => '+1 555 000 0000',
            'shop_name' => 'Card Shop',
            'email' => 'shop@card-shop-employee.test',
            'phone' => '+1 555 000 0000',
            'status' => TenantStatus::Approved->value,
            'approved_at' => now(),
            'onboarding_status' => 'completed',
        ]);

        $employee = User::create([
            'name' => 'Cashier',
            'email' => 'cashier-cards@example.com',
            'password' => 'secret',
            'tenant_id' => $tenant->id,
            'role' => User::EMPLOYEE,
            'is_active' => true,
        ]);
        $employee->forceFill(['email_verified_at' => now()])->save();

        PermissionTeamScope::for($tenant->id, function () use ($employee, $permission): void {
            $employee->givePermissionTo($permission);
        });

        return [$tenant, $employee];
    }
}
