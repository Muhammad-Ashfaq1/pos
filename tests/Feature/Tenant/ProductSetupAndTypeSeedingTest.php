<?php

namespace Tests\Feature\Tenant;

use App\Enums\DefaultProductType;
use App\Enums\TenantStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SubCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Permissions\PermissionTeamScope;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProductSetupAndTypeSeedingTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_tenant_automatically_seeds_default_product_types(): void
    {
        $tenant = $this->createTenant('Auto Seed Shop', 'auto-seed-shop');

        $types = ProductType::withoutTenantScope()->where('tenant_id', $tenant->id)->get();

        $this->assertCount(6, $types);
        $this->assertEqualsCanonicalizing(
            DefaultProductType::values(),
            $types->pluck('slug')->all()
        );
    }

    public function test_seeding_defaults_is_idempotent_and_prevents_duplicates(): void
    {
        $tenant = $this->createTenant('Idempotent Shop', 'idempotent-shop');

        $initialCount = ProductType::withoutTenantScope()->where('tenant_id', $tenant->id)->count();
        $this->assertEquals(6, $initialCount);

        // Call again explicitly
        DefaultProductType::seedDefaultsForTenant($tenant);

        $afterCount = ProductType::withoutTenantScope()->where('tenant_id', $tenant->id)->count();
        $this->assertEquals(6, $afterCount);
    }

    public function test_can_create_product_without_category_subcategory_or_product_type(): void
    {
        [$tenant, $user] = $this->makeTenantAdminWithPermissions([
            'product.view', 'product.create', 'products.view', 'products.manage',
        ]);
        app(TenantContext::class)->initialize($tenant);

        $response = $this->actingAs($user)->postJson(
            route('tenant.ecommerce.products.save'),
            [
                'name' => 'Simple Brake Pad',
                'cost_price' => '25.00',
                'sale_price' => '45.00',
                'opening_stock' => 10,
                // category_id, sub_category_id, product_type_id intentionally omitted
            ]
        );

        $response->assertOk()
            ->assertJsonPath('message', 'Product created successfully.');

        $product = Product::withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->where('name', 'Simple Brake Pad')
            ->first();

        $this->assertNotNull($product);
        $this->assertNull($product->category_id);
        $this->assertNull($product->sub_category_id);
        $this->assertNotNull($product->product_type_id);
        $this->assertEquals('part', $product->product_type);
    }

    public function test_can_create_product_with_nullable_cost_price(): void
    {
        [$tenant, $user] = $this->makeTenantAdminWithPermissions([
            'product.view', 'product.create', 'products.view', 'products.manage',
        ]);
        app(TenantContext::class)->initialize($tenant);

        $response = $this->actingAs($user)->postJson(
            route('tenant.ecommerce.products.save'),
            [
                'name' => 'Zero Cost Accessory',
                'sale_price' => '20.00',
                // cost_price intentionally omitted
            ]
        );

        $response->assertOk()
            ->assertJsonPath('message', 'Product created successfully.');

        $product = Product::withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->where('name', 'Zero Cost Accessory')
            ->first();

        $this->assertNotNull($product);
        $this->assertEquals('0.00', (string) $product->cost_price);
        $this->assertEquals('20.00', (string) $product->sale_price);
    }

    public function test_saving_product_with_zero_sale_price_is_rejected(): void
    {
        [$tenant, $user] = $this->makeTenantAdminWithPermissions([
            'product.view', 'product.create', 'products.view', 'products.manage',
        ]);
        app(TenantContext::class)->initialize($tenant);

        $response = $this->actingAs($user)->postJson(
            route('tenant.ecommerce.products.save'),
            [
                'name' => 'Zero Price Item',
                'cost_price' => '10.00',
                'sale_price' => '0.00',
                'opening_stock' => 5,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sale_price']);
    }

    public function test_creating_product_with_only_subcategory_auto_links_parent_category(): void
    {
        [$tenant, $user] = $this->makeTenantAdminWithPermissions([
            'product.view', 'product.create', 'products.view', 'products.manage',
        ]);
        app(TenantContext::class)->initialize($tenant);

        $category = Category::create([
            'name' => 'Fluids',
            'slug' => 'fluids',
            'code' => 'FLD',
            'is_active' => true,
        ]);

        $subCategory = SubCategory::create([
            'category_id' => $category->id,
            'name' => 'Coolant',
            'slug' => 'coolant',
            'code' => 'CLT',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson(
            route('tenant.ecommerce.products.save'),
            [
                'name' => 'Green Coolant 5L',
                'sub_category_id' => $subCategory->id,
                // category_id omitted
                'cost_price' => '15.00',
                'sale_price' => '25.00',
                'opening_stock' => 5,
            ]
        );

        $response->assertOk()
            ->assertJsonPath('message', 'Product created successfully.');

        $product = Product::withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->where('name', 'Green Coolant 5L')
            ->first();

        $this->assertNotNull($product);
        $this->assertEquals($category->id, $product->category_id);
        $this->assertEquals($subCategory->id, $product->sub_category_id);
    }

    public function test_tenant_can_add_update_and_delete_custom_product_types(): void
    {
        [$tenant, $user] = $this->makeTenantAdminWithPermissions([
            'product-type.view', 'product-type.create', 'product-type.update', 'product-type.delete',
        ]);
        app(TenantContext::class)->initialize($tenant);

        // Add
        $addResponse = $this->actingAs($user)->postJson(
            route('tenant.ecommerce.product-types.save'),
            [
                'name' => 'Custom Accessories',
                'code' => 'ACC',
                'description' => 'Accessories for vehicles',
                'sort_order' => 10,
                'is_active' => true,
            ]
        );
        $addResponse->assertOk()
            ->assertJsonPath('message', 'Product type created successfully.');

        $customType = ProductType::withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->where('name', 'Custom Accessories')
            ->first();
        $this->assertNotNull($customType);

        // Update
        $updateResponse = $this->actingAs($user)->postJson(
            route('tenant.ecommerce.product-types.save'),
            [
                'id' => $customType->id,
                'name' => 'Car Accessories',
                'code' => 'ACC',
                'description' => 'Updated description',
                'sort_order' => 10,
                'is_active' => true,
            ]
        );
        $updateResponse->assertOk()
            ->assertJsonPath('message', 'Product type updated successfully.');
        $this->assertEquals('Car Accessories', $customType->fresh()->name);

        // Delete
        $deleteResponse = $this->actingAs($user)->deleteJson(
            route('tenant.ecommerce.product-types.destroy', $customType)
        );
        $deleteResponse->assertOk()
            ->assertJsonPath('message', 'Product type deleted successfully.');
        $this->assertNull(ProductType::withoutTenantScope()->find($customType->id));
    }

    private function createTenant(string $name, string $slug): Tenant
    {
        return Tenant::create([
            'name' => $name,
            'slug' => $slug,
            'shop_name' => $name,
            'business_name' => $name,
            'email' => "{$slug}@example.test",
            'phone' => '+1 555 123 4567',
            'owner_name' => 'Owner',
            'owner_email' => "owner-{$slug}@example.test",
            'status' => TenantStatus::Approved->value,
            'approved_at' => now(),
            'onboarding_status' => 'completed',
        ]);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Tenant, 1: User}
     */
    private function makeTenantAdminWithPermissions(array $permissions): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $tenant = $this->createTenant('Test POS Shop', 'test-pos-shop');

        $user = User::create([
            'name' => 'Tenant Admin',
            'email' => 'admin-test@test-pos-shop.test',
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
