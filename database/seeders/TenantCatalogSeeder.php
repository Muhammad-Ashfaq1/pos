<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceProduct;
use App\Models\SubCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantCatalogSeeder extends Seeder
{
    private const CATEGORIES = [
        ['name' => 'Engine Oils',     'code' => 'OIL', 'subcategories' => ['Synthetic', 'Semi-Synthetic', 'Mineral']],
        ['name' => 'Filters',         'code' => 'FLT', 'subcategories' => ['Oil Filter', 'Air Filter', 'Cabin Filter']],
        ['name' => 'Brakes',          'code' => 'BRK', 'subcategories' => ['Pads', 'Rotors', 'Fluid']],
        ['name' => 'Batteries',       'code' => 'BAT', 'subcategories' => ['12V Standard', '12V Premium']],
        ['name' => 'Tires',           'code' => 'TIR', 'subcategories' => ['Passenger', 'SUV', 'Performance']],
    ];

    private const PRODUCTS_BY_CATEGORY = [
        'Engine Oils' => [
            ['name' => 'Mobil 1 5W-30 Full Synthetic',  'brand' => 'Mobil 1',   'cost' => 28.00, 'price' => 39.99, 'unit' => 'liter', 'type' => Product::TYPE_OIL,    'sub' => 'Synthetic'],
            ['name' => 'Castrol GTX 10W-40',            'brand' => 'Castrol',   'cost' => 18.50, 'price' => 27.99, 'unit' => 'liter', 'type' => Product::TYPE_OIL,    'sub' => 'Semi-Synthetic'],
            ['name' => 'Shell Helix HX7 5W-40',         'brand' => 'Shell',     'cost' => 22.00, 'price' => 32.50, 'unit' => 'liter', 'type' => Product::TYPE_OIL,    'sub' => 'Semi-Synthetic'],
            ['name' => 'Valvoline Daily Protection',    'brand' => 'Valvoline', 'cost' => 14.00, 'price' => 22.99, 'unit' => 'liter', 'type' => Product::TYPE_OIL,    'sub' => 'Mineral'],
        ],
        'Filters' => [
            ['name' => 'K&N Oil Filter HP-1004',        'brand' => 'K&N',       'cost' => 8.50,  'price' => 14.99, 'unit' => 'piece', 'type' => Product::TYPE_FILTER, 'sub' => 'Oil Filter'],
            ['name' => 'Bosch Premium Air Filter',      'brand' => 'Bosch',     'cost' => 12.00, 'price' => 21.99, 'unit' => 'piece', 'type' => Product::TYPE_FILTER, 'sub' => 'Air Filter'],
            ['name' => 'Mann Cabin Filter CU 26 009',   'brand' => 'Mann',      'cost' => 9.50,  'price' => 16.99, 'unit' => 'piece', 'type' => Product::TYPE_FILTER, 'sub' => 'Cabin Filter'],
        ],
        'Brakes' => [
            ['name' => 'Brembo Front Brake Pads',       'brand' => 'Brembo',    'cost' => 45.00, 'price' => 79.99, 'unit' => 'set',   'type' => Product::TYPE_PART,   'sub' => 'Pads'],
            ['name' => 'DOT 4 Brake Fluid 1L',          'brand' => 'Bosch',     'cost' => 7.00,  'price' => 12.99, 'unit' => 'liter', 'type' => Product::TYPE_PART,   'sub' => 'Fluid'],
        ],
        'Batteries' => [
            ['name' => 'Exide 12V 60Ah Premium',        'brand' => 'Exide',     'cost' => 75.00, 'price' => 119.99,'unit' => 'piece', 'type' => Product::TYPE_PART,   'sub' => '12V Premium'],
            ['name' => 'AC Delco 12V 70Ah',             'brand' => 'AC Delco',  'cost' => 85.00, 'price' => 134.99,'unit' => 'piece', 'type' => Product::TYPE_PART,   'sub' => '12V Standard'],
        ],
        'Tires' => [
            ['name' => 'Michelin Primacy 4 195/65R15',  'brand' => 'Michelin',  'cost' => 90.00, 'price' => 139.99,'unit' => 'piece', 'type' => Product::TYPE_PART,   'sub' => 'Passenger'],
            ['name' => 'Bridgestone Dueler H/T',        'brand' => 'Bridgestone','cost' => 110.00,'price' => 169.99,'unit' => 'piece','type' => Product::TYPE_PART,   'sub' => 'SUV'],
        ],
    ];

    private const SERVICES_BY_CATEGORY = [
        'Engine Oils' => [
            ['name' => 'Standard Oil Change',     'duration' => 30, 'price' => 49.99,  'reminder_days' => 90,  'mileage' => 5000,  'products' => [['name' => 'Castrol GTX 10W-40',         'qty' => 4, 'unit' => 'liter', 'required' => true], ['name' => 'K&N Oil Filter HP-1004', 'qty' => 1, 'unit' => 'piece', 'required' => true]]],
            ['name' => 'Full Synthetic Oil Change','duration' => 45, 'price' => 89.99, 'reminder_days' => 180, 'mileage' => 10000, 'products' => [['name' => 'Mobil 1 5W-30 Full Synthetic','qty' => 4, 'unit' => 'liter', 'required' => true], ['name' => 'K&N Oil Filter HP-1004', 'qty' => 1, 'unit' => 'piece', 'required' => true]]],
        ],
        'Filters' => [
            ['name' => 'Air Filter Replacement',  'duration' => 15, 'price' => 24.99, 'reminder_days' => 365, 'mileage' => 15000, 'products' => [['name' => 'Bosch Premium Air Filter',   'qty' => 1, 'unit' => 'piece', 'required' => true]]],
            ['name' => 'Cabin Filter Replacement','duration' => 20, 'price' => 29.99, 'reminder_days' => 365, 'mileage' => 15000, 'products' => [['name' => 'Mann Cabin Filter CU 26 009','qty' => 1, 'unit' => 'piece', 'required' => true]]],
        ],
        'Brakes' => [
            ['name' => 'Brake Pad Replacement',   'duration' => 90, 'price' => 149.99,'reminder_days' => null,'mileage' => 50000, 'products' => [['name' => 'Brembo Front Brake Pads',    'qty' => 1, 'unit' => 'set',   'required' => true]]],
            ['name' => 'Brake Fluid Flush',       'duration' => 45, 'price' => 79.99, 'reminder_days' => 730, 'mileage' => null,  'products' => [['name' => 'DOT 4 Brake Fluid 1L',       'qty' => 2, 'unit' => 'liter', 'required' => true]]],
        ],
        'Batteries' => [
            ['name' => 'Battery Test & Replace',  'duration' => 30, 'price' => 39.99, 'reminder_days' => null,'mileage' => null,  'products' => [['name' => 'Exide 12V 60Ah Premium',     'qty' => 1, 'unit' => 'piece', 'required' => false]]],
        ],
        'Tires' => [
            ['name' => 'Tire Rotation',           'duration' => 30, 'price' => 24.99, 'reminder_days' => 180, 'mileage' => 10000, 'products' => []],
            ['name' => 'Wheel Alignment',         'duration' => 60, 'price' => 89.99, 'reminder_days' => null,'mileage' => 20000, 'products' => []],
        ],
    ];

    private const CUSTOMERS = [
        ['name' => 'John Smith',         'phone' => '+1 555 100 0001', 'email' => 'john.smith@example.com',     'type' => Customer::TYPE_REGISTERED],
        ['name' => 'Sarah Johnson',      'phone' => '+1 555 100 0002', 'email' => 'sarah.j@example.com',        'type' => Customer::TYPE_REGISTERED],
        ['name' => 'Michael Williams',   'phone' => '+1 555 100 0003', 'email' => 'mwilliams@example.com',      'type' => Customer::TYPE_REGISTERED],
        ['name' => 'Emily Davis',        'phone' => '+1 555 100 0004', 'email' => 'emily.davis@example.com',    'type' => Customer::TYPE_REGISTERED],
        ['name' => 'Robert Brown',       'phone' => '+1 555 100 0005', 'email' => 'rbrown@example.com',         'type' => Customer::TYPE_REGISTERED],
        ['name' => 'Acme Logistics Inc', 'phone' => '+1 555 100 0006', 'email' => 'fleet@acme-logistics.com',   'type' => Customer::TYPE_CORPORATE],
        ['name' => 'Sunrise Cab Co',     'phone' => '+1 555 100 0007', 'email' => 'ops@sunrisecab.com',         'type' => Customer::TYPE_CORPORATE],
        ['name' => Customer::DEFAULT_WALK_IN_NAME, 'phone' => null,    'email' => null,                         'type' => Customer::TYPE_WALK_IN],
    ];

    private const VEHICLES = [
        ['plate' => 'ABC-1234', 'make' => 'Toyota',    'model' => 'Camry',     'year' => 2020, 'color' => 'Silver', 'engine' => 'Petrol',  'odometer' => 45230.5],
        ['plate' => 'XYZ-5678', 'make' => 'Honda',     'model' => 'Civic',     'year' => 2019, 'color' => 'Black',  'engine' => 'Petrol',  'odometer' => 62100.0],
        ['plate' => 'JKL-9012', 'make' => 'Ford',      'model' => 'F-150',     'year' => 2021, 'color' => 'White',  'engine' => 'Diesel',  'odometer' => 28750.2],
        ['plate' => 'MNO-3456', 'make' => 'Chevrolet', 'model' => 'Silverado', 'year' => 2018, 'color' => 'Red',    'engine' => 'Petrol',  'odometer' => 89540.8],
        ['plate' => 'PQR-7890', 'make' => 'Nissan',    'model' => 'Altima',    'year' => 2022, 'color' => 'Blue',   'engine' => 'Hybrid',  'odometer' => 12330.0],
        ['plate' => 'STU-2345', 'make' => 'Hyundai',   'model' => 'Tucson',    'year' => 2020, 'color' => 'Gray',   'engine' => 'Petrol',  'odometer' => 38920.5],
        ['plate' => 'VWX-6789', 'make' => 'BMW',       'model' => 'X3',        'year' => 2021, 'color' => 'Black',  'engine' => 'Petrol',  'odometer' => 22450.0],
    ];

    private const DISCOUNTS = [
        ['name' => 'New Customer 10% Off', 'code' => 'WELCOME10', 'type' => Discount::TYPE_PERCENTAGE, 'applies_to' => Discount::APPLIES_TO_BILL,             'value' => 10.00, 'max' => 50.00],
        ['name' => 'Senior Citizen Discount','code' => 'SENIOR15', 'type' => Discount::TYPE_PERCENTAGE,'applies_to' => Discount::APPLIES_TO_CUSTOMER_PROFILE, 'value' => 15.00, 'max' => 100.00],
        ['name' => 'Holiday Promo $20',    'code' => 'HOLIDAY20', 'type' => Discount::TYPE_FIXED,      'applies_to' => Discount::APPLIES_TO_PROMOTION,        'value' => 20.00, 'max' => null],
        ['name' => 'Loyalty Voucher 5%',   'code' => 'LOYAL5',    'type' => Discount::TYPE_PERCENTAGE, 'applies_to' => Discount::APPLIES_TO_VOUCHER,          'value' => 5.00,  'max' => 30.00],
        ['name' => 'Item Clearance 25%',   'code' => 'CLEAR25',   'type' => Discount::TYPE_PERCENTAGE, 'applies_to' => Discount::APPLIES_TO_ITEM,             'value' => 25.00, 'max' => null],
    ];

    public function run(): void
    {
        if (app()->environment('production') && ! app()->runningUnitTests()) {
            $this->command?->warn('Skipping demo catalog data in production.');

            return;
        }

        Tenant::query()->orderBy('id')->get()->each(function (Tenant $tenant): void {
            $this->command?->info("Seeding catalog data for tenant #{$tenant->id} - {$tenant->name}...");

            $adminId = User::query()
                ->where('tenant_id', $tenant->id)
                ->where('role', User::TENANT_ADMIN)
                ->value('id');

            $this->seedCategoriesAndSubs($tenant, $adminId);
            $this->seedProducts($tenant, $adminId);
            $this->seedServices($tenant, $adminId);
            $this->seedCustomersAndVehicles($tenant, $adminId);
            $this->seedDiscounts($tenant, $adminId);
        });
    }

    private function seedCategoriesAndSubs(Tenant $tenant, ?int $adminId): void
    {
        foreach (self::CATEGORIES as $idx => $cat) {
            $category = Category::withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->where('slug', Str::slug($cat['name']))
                ->first();

            if (! $category) {
                $category = new Category;
                $category->tenant_id = $tenant->id;
                $category->name = $cat['name'];
                $category->slug = Str::slug($cat['name']);
                $category->code = $cat['code'];
                $category->description = "{$cat['name']} category";
                $category->sort_order = $idx + 1;
                $category->is_active = true;
                $category->created_by = $adminId;
                $category->updated_by = $adminId;
                $category->save();
            }

            foreach ($cat['subcategories'] as $subIdx => $subName) {
                $exists = SubCategory::withoutTenantScope()
                    ->where('tenant_id', $tenant->id)
                    ->where('category_id', $category->id)
                    ->where('slug', Str::slug($subName))
                    ->exists();

                if ($exists) {
                    continue;
                }

                $sub = new SubCategory;
                $sub->tenant_id = $tenant->id;
                $sub->category_id = $category->id;
                $sub->name = $subName;
                $sub->slug = Str::slug($subName);
                $sub->code = strtoupper(Str::slug($subName, ''));
                $sub->description = "{$subName} sub-category";
                $sub->sort_order = $subIdx + 1;
                $sub->is_active = true;
                $sub->created_by = $adminId;
                $sub->updated_by = $adminId;
                $sub->save();
            }
        }
    }

    private function seedProducts(Tenant $tenant, ?int $adminId): void
    {
        foreach (self::PRODUCTS_BY_CATEGORY as $catName => $products) {
            $category = Category::withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->where('slug', Str::slug($catName))
                ->first();

            if (! $category) {
                continue;
            }

            foreach ($products as $idx => $p) {
                $sku = sprintf('%s-%s-%03d', $category->code, strtoupper(Str::slug($p['brand'], '')), $idx + 1);

                $exists = Product::withoutTenantScope()
                    ->where('tenant_id', $tenant->id)
                    ->where('sku', $sku)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $sub = SubCategory::withoutTenantScope()
                    ->where('tenant_id', $tenant->id)
                    ->where('category_id', $category->id)
                    ->where('slug', Str::slug($p['sub']))
                    ->first();

                $product = new Product;
                $product->tenant_id = $tenant->id;
                $product->category_id = $category->id;
                $product->sub_category_id = $sub?->id;
                $product->product_type = $p['type'];
                $product->name = $p['name'];
                $product->slug = Str::slug($p['name']);
                $product->sku = $sku;
                $product->barcode = (string) random_int(1000000000000, 9999999999999);
                $product->brand = $p['brand'];
                $product->unit = $p['unit'];
                $product->description = "{$p['brand']} - {$p['name']}";
                $product->cost_price = $p['cost'];
                $product->sale_price = $p['price'];
                $product->tax_percentage = 5.00;
                $product->opening_stock = 100;
                $product->current_stock = 100;
                $product->minimum_stock_level = 10;
                $product->reorder_level = 20;
                $product->track_inventory = true;
                $product->is_active = true;
                $product->created_by = $adminId;
                $product->updated_by = $adminId;
                $product->save();
            }
        }
    }

    private function seedServices(Tenant $tenant, ?int $adminId): void
    {
        foreach (self::SERVICES_BY_CATEGORY as $catName => $services) {
            $category = Category::withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->where('slug', Str::slug($catName))
                ->first();

            if (! $category) {
                continue;
            }

            foreach ($services as $idx => $s) {
                $code = sprintf('SVC-%s-%03d', $category->code, $idx + 1);

                $service = Service::withoutTenantScope()
                    ->where('tenant_id', $tenant->id)
                    ->where('code', $code)
                    ->first();

                if (! $service) {
                    $service = new Service;
                    $service->tenant_id = $tenant->id;
                    $service->category_id = $category->id;
                    $service->name = $s['name'];
                    $service->code = $code;
                    $service->description = "{$s['name']} service";
                    $service->standard_price = $s['price'];
                    $service->estimated_duration_minutes = $s['duration'];
                    $service->tax_percentage = 5.00;
                    $service->reminder_interval_days = $s['reminder_days'];
                    $service->mileage_interval = $s['mileage'];
                    $service->requires_technician = true;
                    $service->is_active = true;
                    $service->created_by = $adminId;
                    $service->updated_by = $adminId;
                    $service->save();
                }

                $this->seedServiceProducts($tenant, $service, $s['products'] ?? []);
            }
        }
    }

    private function seedServiceProducts(Tenant $tenant, Service $service, array $products): void
    {
        foreach ($products as $sp) {
            $product = Product::withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->where('name', $sp['name'])
                ->first();

            if (! $product) {
                continue;
            }

            $exists = ServiceProduct::withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->where('service_id', $service->id)
                ->where('product_id', $product->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $link = new ServiceProduct;
            $link->tenant_id = $tenant->id;
            $link->service_id = $service->id;
            $link->product_id = $product->id;
            $link->quantity = $sp['qty'];
            $link->unit = $sp['unit'];
            $link->is_required = $sp['required'];
            $link->save();
        }
    }

    private function seedCustomersAndVehicles(Tenant $tenant, ?int $adminId): void
    {
        foreach (self::CUSTOMERS as $idx => $c) {
            $customer = Customer::withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->where('name', $c['name'])
                ->first();

            if (! $customer) {
                $customer = new Customer;
                $customer->tenant_id = $tenant->id;
                $customer->customer_type = $c['type'];
                $customer->name = $c['name'];
                $customer->phone = $c['phone'];
                $customer->email = $c['email'];
                $customer->address = $c['type'] === Customer::TYPE_WALK_IN ? null : sprintf('%d Main Street, Suite %d', 100 + $idx, $idx + 1);
                $customer->total_visits = $c['type'] === Customer::TYPE_WALK_IN ? 0 : random_int(1, 15);
                $customer->lifetime_value = $c['type'] === Customer::TYPE_WALK_IN ? 0 : random_int(100, 3000);
                $customer->loyalty_points_balance = $c['type'] === Customer::TYPE_WALK_IN ? 0 : random_int(0, 500);
                $customer->credit_balance = 0;
                $customer->created_by = $adminId;
                $customer->updated_by = $adminId;
                $customer->save();
            }

            if ($c['type'] === Customer::TYPE_WALK_IN) {
                continue;
            }

            $this->seedVehiclesFor($tenant, $customer, $adminId, $idx);
        }
    }

    private function seedVehiclesFor(Tenant $tenant, Customer $customer, ?int $adminId, int $customerIdx): void
    {
        $vehicleCount = ($customer->customer_type === Customer::TYPE_CORPORATE) ? 3 : 1;
        $startIdx = $customerIdx % count(self::VEHICLES);

        for ($i = 0; $i < $vehicleCount; $i++) {
            $template = self::VEHICLES[($startIdx + $i) % count(self::VEHICLES)];
            $plate = sprintf('%s-T%dC%d', $template['plate'], $tenant->id, $customer->id + $i);

            $exists = Vehicle::withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->where('plate_number', $plate)
                ->exists();

            if ($exists) {
                continue;
            }

            $vehicle = new Vehicle;
            $vehicle->tenant_id = $tenant->id;
            $vehicle->customer_id = $customer->id;
            $vehicle->plate_number = $plate;
            $vehicle->registration_number = sprintf('REG-%d-%d', $tenant->id, $customer->id * 10 + $i);
            $vehicle->make = $template['make'];
            $vehicle->model = $template['model'];
            $vehicle->year = $template['year'];
            $vehicle->color = $template['color'];
            $vehicle->engine_type = $template['engine'];
            $vehicle->odometer = $template['odometer'];
            $vehicle->is_default = ($i === 0);
            $vehicle->created_by = $adminId;
            $vehicle->updated_by = $adminId;
            $vehicle->save();
        }
    }

    private function seedDiscounts(Tenant $tenant, ?int $adminId): void
    {
        foreach (self::DISCOUNTS as $d) {
            $exists = Discount::withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->where('code', $d['code'])
                ->exists();

            if ($exists) {
                continue;
            }

            $discount = new Discount;
            $discount->tenant_id = $tenant->id;
            $discount->name = $d['name'];
            $discount->code = $d['code'];
            $discount->description = "{$d['name']} discount";
            $discount->discount_type = $d['type'];
            $discount->applies_to = $d['applies_to'];
            $discount->value = $d['value'];
            $discount->max_discount_amount = $d['max'];
            $discount->starts_at = now()->subDays(7);
            $discount->ends_at = now()->addMonths(6);
            $discount->usage_limit = 1000;
            $discount->is_active = true;
            $discount->is_combinable = false;
            $discount->requires_reason = false;
            $discount->requires_manager_approval = ($d['type'] === Discount::TYPE_FIXED);
            $discount->created_by = $adminId;
            $discount->updated_by = $adminId;
            $discount->save();
        }
    }
}
