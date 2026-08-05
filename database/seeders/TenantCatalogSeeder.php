<?php

namespace Database\Seeders;

use App\Helpers\FileUploadManager;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\DiscountGroup;
use App\Models\Image;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Service;
use App\Models\ServiceProduct;
use App\Models\SubCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
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
            // Synthetic (3)
            ['name' => 'Mobil 1 5W-30 Full Synthetic',  'brand' => 'Mobil 1',   'cost' => 28.00, 'price' => 39.99, 'unit' => 'liter', 'type' => Product::TYPE_OIL, 'sub' => 'Synthetic',      'image' => 'mobil-1-5w30.jpg'],
            ['name' => 'Pennzoil Ultra Platinum 5W-30', 'brand' => 'Pennzoil',  'cost' => 26.50, 'price' => 37.99, 'unit' => 'liter', 'type' => Product::TYPE_OIL, 'sub' => 'Synthetic'],
            ['name' => 'Amsoil Signature 0W-20',        'brand' => 'Amsoil',    'cost' => 32.00, 'price' => 44.99, 'unit' => 'liter', 'type' => Product::TYPE_OIL, 'sub' => 'Synthetic'],
            // Semi-Synthetic (3)
            ['name' => 'Castrol GTX 10W-40',            'brand' => 'Castrol',   'cost' => 18.50, 'price' => 27.99, 'unit' => 'liter', 'type' => Product::TYPE_OIL, 'sub' => 'Semi-Synthetic', 'image' => 'castrol-gtx-10w40.jpg'],
            ['name' => 'Shell Helix HX7 5W-40',         'brand' => 'Shell',     'cost' => 22.00, 'price' => 32.50, 'unit' => 'liter', 'type' => Product::TYPE_OIL, 'sub' => 'Semi-Synthetic', 'image' => 'shell-helix-hx7.jpg'],
            ['name' => 'Total Quartz 7000 10W-40',      'brand' => 'Total',     'cost' => 17.00, 'price' => 25.99, 'unit' => 'liter', 'type' => Product::TYPE_OIL, 'sub' => 'Semi-Synthetic'],
            // Mineral (3)
            ['name' => 'Valvoline Daily Protection',    'brand' => 'Valvoline', 'cost' => 14.00, 'price' => 22.99, 'unit' => 'liter', 'type' => Product::TYPE_OIL, 'sub' => 'Mineral',        'image' => 'valvoline-daily.jpg'],
            ['name' => 'Havoline Conventional 20W-50',  'brand' => 'Havoline',  'cost' => 12.50, 'price' => 19.99, 'unit' => 'liter', 'type' => Product::TYPE_OIL, 'sub' => 'Mineral'],
            ['name' => 'Petronas Syntium 800 15W-40',   'brand' => 'Petronas',  'cost' => 13.00, 'price' => 20.49, 'unit' => 'liter', 'type' => Product::TYPE_OIL, 'sub' => 'Mineral'],
        ],
        'Filters' => [
            // Oil Filter (3)
            ['name' => 'K&N Oil Filter HP-1004',        'brand' => 'K&N',       'cost' => 8.50,  'price' => 14.99, 'unit' => 'piece', 'type' => Product::TYPE_FILTER, 'sub' => 'Oil Filter',   'image' => 'kn-oil-filter.jpg'],
            ['name' => 'Fram Extra Guard PH3614',       'brand' => 'Fram',      'cost' => 5.50,  'price' => 9.99,  'unit' => 'piece', 'type' => Product::TYPE_FILTER, 'sub' => 'Oil Filter'],
            ['name' => 'Wix 51515 Oil Filter',          'brand' => 'Wix',       'cost' => 7.00,  'price' => 12.49, 'unit' => 'piece', 'type' => Product::TYPE_FILTER, 'sub' => 'Oil Filter'],
            // Air Filter (3)
            ['name' => 'Bosch Premium Air Filter',      'brand' => 'Bosch',     'cost' => 12.00, 'price' => 21.99, 'unit' => 'piece', 'type' => Product::TYPE_FILTER, 'sub' => 'Air Filter',   'image' => 'bosch-air-filter.jpg'],
            ['name' => 'K&N Drop-In Air Filter',        'brand' => 'K&N',       'cost' => 28.00, 'price' => 44.99, 'unit' => 'piece', 'type' => Product::TYPE_FILTER, 'sub' => 'Air Filter'],
            ['name' => 'Mann C 27 130 Air Filter',      'brand' => 'Mann',      'cost' => 11.00, 'price' => 18.99, 'unit' => 'piece', 'type' => Product::TYPE_FILTER, 'sub' => 'Air Filter'],
            // Cabin Filter (3)
            ['name' => 'Mann Cabin Filter CU 26 009',   'brand' => 'Mann',      'cost' => 9.50,  'price' => 16.99, 'unit' => 'piece', 'type' => Product::TYPE_FILTER, 'sub' => 'Cabin Filter', 'image' => 'mann-cabin-filter.jpg'],
            ['name' => 'Bosch Cabin Filter Activated',  'brand' => 'Bosch',     'cost' => 10.50, 'price' => 17.99, 'unit' => 'piece', 'type' => Product::TYPE_FILTER, 'sub' => 'Cabin Filter'],
            ['name' => 'Fram Fresh Breeze Cabin Filter','brand' => 'Fram',      'cost' => 8.00,  'price' => 14.49, 'unit' => 'piece', 'type' => Product::TYPE_FILTER, 'sub' => 'Cabin Filter'],
        ],
        'Brakes' => [
            // Pads (3)
            ['name' => 'Brembo Front Brake Pads',       'brand' => 'Brembo',    'cost' => 45.00, 'price' => 79.99, 'unit' => 'set',   'type' => Product::TYPE_PART, 'sub' => 'Pads',  'image' => 'brembo-brake-pads.jpg'],
            ['name' => 'Akebono ProACT Ceramic Pads',   'brand' => 'Akebono',   'cost' => 38.00, 'price' => 64.99, 'unit' => 'set',   'type' => Product::TYPE_PART, 'sub' => 'Pads'],
            ['name' => 'Wagner ThermoQuiet Pads',       'brand' => 'Wagner',    'cost' => 32.00, 'price' => 54.99, 'unit' => 'set',   'type' => Product::TYPE_PART, 'sub' => 'Pads'],
            // Rotors (3)
            ['name' => 'Brembo Coated Brake Rotor',     'brand' => 'Brembo',    'cost' => 55.00, 'price' => 89.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => 'Rotors'],
            ['name' => 'Raybestos Element3 Rotor',      'brand' => 'Raybestos', 'cost' => 42.00, 'price' => 69.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => 'Rotors'],
            ['name' => 'Centric Premium Brake Rotor',   'brand' => 'Centric',   'cost' => 36.00, 'price' => 59.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => 'Rotors'],
            // Fluid (3)
            ['name' => 'DOT 4 Brake Fluid 1L',          'brand' => 'Bosch',     'cost' => 7.00,  'price' => 12.99, 'unit' => 'liter', 'type' => Product::TYPE_PART, 'sub' => 'Fluid', 'image' => 'dot4-brake-fluid.jpg'],
            ['name' => 'Motul RBF 600 DOT 4',           'brand' => 'Motul',     'cost' => 14.00, 'price' => 22.99, 'unit' => 'liter', 'type' => Product::TYPE_PART, 'sub' => 'Fluid'],
            ['name' => 'Castrol React DOT 4 Low Temp',  'brand' => 'Castrol',   'cost' => 8.50,  'price' => 14.99, 'unit' => 'liter', 'type' => Product::TYPE_PART, 'sub' => 'Fluid'],
        ],
        'Batteries' => [
            // 12V Standard (3)
            ['name' => 'AC Delco 12V 70Ah',             'brand' => 'AC Delco',  'cost' => 85.00, 'price' => 134.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => '12V Standard', 'image' => 'acdelco-battery.jpg'],
            ['name' => 'Interstate MT-65 12V',          'brand' => 'Interstate','cost' => 78.00, 'price' => 124.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => '12V Standard'],
            ['name' => 'DieHard Gold 12V 65Ah',         'brand' => 'DieHard',   'cost' => 82.00, 'price' => 129.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => '12V Standard'],
            // 12V Premium (3)
            ['name' => 'Exide 12V 60Ah Premium',        'brand' => 'Exide',     'cost' => 75.00, 'price' => 119.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => '12V Premium',  'image' => 'exide-battery.jpg'],
            ['name' => 'Optima RedTop 12V AGM',         'brand' => 'Optima',    'cost' => 145.00,'price' => 219.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => '12V Premium'],
            ['name' => 'Odyssey Extreme 12V AGM',       'brand' => 'Odyssey',   'cost' => 160.00,'price' => 249.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => '12V Premium'],
        ],
        'Tires' => [
            // Passenger (3)
            ['name' => 'Michelin Primacy 4 195/65R15',  'brand' => 'Michelin',  'cost' => 90.00, 'price' => 139.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => 'Passenger',   'image' => 'michelin-primacy4.jpg'],
            ['name' => 'Goodyear Assurance 205/55R16',  'brand' => 'Goodyear',  'cost' => 82.00, 'price' => 124.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => 'Passenger'],
            ['name' => 'Continental PureContact 215/60R16', 'brand' => 'Continental', 'cost' => 88.00, 'price' => 134.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => 'Passenger'],
            // SUV (3)
            ['name' => 'Bridgestone Dueler H/T',        'brand' => 'Bridgestone', 'cost' => 110.00, 'price' => 169.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => 'SUV', 'image' => 'bridgestone-dueler.jpg'],
            ['name' => 'Michelin Defender LTX 265/70R17', 'brand' => 'Michelin', 'cost' => 125.00, 'price' => 189.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => 'SUV'],
            ['name' => 'BFGoodrich All-Terrain T/A KO2', 'brand' => 'BFGoodrich', 'cost' => 140.00, 'price' => 209.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => 'SUV'],
            // Performance (3)
            ['name' => 'Michelin Pilot Sport 4S',       'brand' => 'Michelin',  'cost' => 155.00, 'price' => 229.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => 'Performance'],
            ['name' => 'Pirelli P Zero 245/40R18',      'brand' => 'Pirelli',   'cost' => 148.00, 'price' => 219.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => 'Performance'],
            ['name' => 'Continental ExtremeContact Sport', 'brand' => 'Continental', 'cost' => 135.00, 'price' => 199.99, 'unit' => 'piece', 'type' => Product::TYPE_PART, 'sub' => 'Performance'],
        ],
    ];

    /**
     * Local source directory for seed product images, relative to the project base path.
     * Files here are pushed onto the configured storage disk by the seeder and their
     * stored paths are recorded as `images` rows (see seedProductImage()).
     */
    private const IMAGE_SOURCE_DIR = 'database/data/images/products';

    /**
     * Default product types, keyed by the slug referenced in PRODUCTS_BY_CATEGORY['type'].
     */
    private const PRODUCT_TYPES = [
        Product::TYPE_INVENTORY => 'Inventory Item',
        Product::TYPE_OIL => 'Oil',
        Product::TYPE_FILTER => 'Filter',
        Product::TYPE_PART => 'Part',
        Product::TYPE_ADDITIVE => 'Additive',
        Product::TYPE_OTHER => 'Other',
    ];

    private const SERVICES_BY_CATEGORY = [
        'Engine Oils' => [
            ['name' => 'Standard Oil Change',     'duration' => 30, 'price' => 49.99,  'reminder_days' => 90,  'mileage' => 5000,  'products' => [['name' => 'Castrol GTX 10W-40',         'qty' => 4, 'unit' => 'liter', 'required' => true], ['name' => 'K&N Oil Filter HP-1004', 'qty' => 1, 'unit' => 'piece', 'required' => true]]],
            ['name' => 'Full Synthetic Oil Change', 'duration' => 45, 'price' => 89.99, 'reminder_days' => 180, 'mileage' => 10000, 'products' => [['name' => 'Mobil 1 5W-30 Full Synthetic', 'qty' => 4, 'unit' => 'liter', 'required' => true], ['name' => 'K&N Oil Filter HP-1004', 'qty' => 1, 'unit' => 'piece', 'required' => true]]],
        ],
        'Filters' => [
            ['name' => 'Air Filter Replacement',  'duration' => 15, 'price' => 24.99, 'reminder_days' => 365, 'mileage' => 15000, 'products' => [['name' => 'Bosch Premium Air Filter',   'qty' => 1, 'unit' => 'piece', 'required' => true]]],
            ['name' => 'Cabin Filter Replacement', 'duration' => 20, 'price' => 29.99, 'reminder_days' => 365, 'mileage' => 15000, 'products' => [['name' => 'Mann Cabin Filter CU 26 009', 'qty' => 1, 'unit' => 'piece', 'required' => true]]],
        ],
        'Brakes' => [
            ['name' => 'Brake Pad Replacement',   'duration' => 90, 'price' => 149.99, 'reminder_days' => null, 'mileage' => 50000, 'products' => [['name' => 'Brembo Front Brake Pads',    'qty' => 1, 'unit' => 'set',   'required' => true]]],
            ['name' => 'Brake Fluid Flush',       'duration' => 45, 'price' => 79.99, 'reminder_days' => 730, 'mileage' => null,  'products' => [['name' => 'DOT 4 Brake Fluid 1L',       'qty' => 2, 'unit' => 'liter', 'required' => true]]],
        ],
        'Batteries' => [
            ['name' => 'Battery Test & Replace',  'duration' => 30, 'price' => 39.99, 'reminder_days' => null, 'mileage' => null,  'products' => [['name' => 'Exide 12V 60Ah Premium',     'qty' => 1, 'unit' => 'piece', 'required' => false]]],
        ],
        'Tires' => [
            ['name' => 'Tire Rotation',           'duration' => 30, 'price' => 24.99, 'reminder_days' => 180, 'mileage' => 10000, 'products' => []],
            ['name' => 'Wheel Alignment',         'duration' => 60, 'price' => 89.99, 'reminder_days' => null, 'mileage' => 20000, 'products' => []],
        ],
    ];

    private const CUSTOMERS = [
        ['name' => 'John Smith',         'phone' => '+1 555 100 0001', 'email' => 'john.smith@obtainsolutions.com',     'type' => Customer::TYPE_REGISTERED, 'group' => 'silver-tier'],
        ['name' => 'Sarah Johnson',      'phone' => '+1 555 100 0002', 'email' => 'sarah.j@obtainsolutions.com',        'type' => Customer::TYPE_REGISTERED, 'group' => 'gold-tier'],
        ['name' => 'Michael Williams',   'phone' => '+1 555 100 0003', 'email' => 'mwilliams@obtainsolutions.com',      'type' => Customer::TYPE_REGISTERED, 'group' => null],
        ['name' => 'Emily Davis',        'phone' => '+1 555 100 0004', 'email' => 'emily.davis@obtainsolutions.com',    'type' => Customer::TYPE_REGISTERED, 'group' => 'silver-tier'],
        ['name' => 'Robert Brown',       'phone' => '+1 555 100 0005', 'email' => 'rbrown@obtainsolutions.com',         'type' => Customer::TYPE_REGISTERED, 'group' => 'platinum-tier'],
        ['name' => 'Acme Logistics Inc', 'phone' => '+1 555 100 0006', 'email' => 'fleet@obtainsolutions.com',          'type' => Customer::TYPE_CORPORATE,  'group' => 'fleet-account'],
        ['name' => 'Sunrise Cab Co',     'phone' => '+1 555 100 0007', 'email' => 'ops@obtainsolutions.com',            'type' => Customer::TYPE_CORPORATE,  'group' => 'platinum-tier'],
        ['name' => Customer::DEFAULT_WALK_IN_NAME, 'phone' => null,    'email' => null,                         'type' => Customer::TYPE_WALK_IN,    'group' => null],
    ];

    private const DISCOUNT_GROUPS = [
        ['name' => 'Silver Tier',   'type' => 'percentage', 'value' => 5.00,  'min_limit' => 100.00],
        ['name' => 'Gold Tier',     'type' => 'percentage', 'value' => 10.00, 'min_limit' => 250.00],
        ['name' => 'Platinum Tier', 'type' => 'percentage', 'value' => 15.00, 'min_limit' => 500.00],
        ['name' => 'Fleet Account', 'type' => 'fixed',      'value' => 25.00, 'min_limit' => 200.00],
    ];

    /**
     * Demo products that receive the seeded item-level discount (matched by name).
     */
    private const PRODUCTS_WITH_ITEM_DISCOUNT = [
        'Valvoline Daily Protection',
        'Bosch Premium Air Filter',
        'Bridgestone Dueler H/T',
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
        ['name' => 'Senior Citizen Discount', 'code' => 'SENIOR15', 'type' => Discount::TYPE_PERCENTAGE, 'applies_to' => Discount::APPLIES_TO_CUSTOMER_PROFILE, 'value' => 15.00, 'max' => 100.00],
        ['name' => 'Holiday Promo $20',    'code' => 'HOLIDAY20', 'type' => Discount::TYPE_FIXED,      'applies_to' => Discount::APPLIES_TO_PROMOTION,        'value' => 20.00, 'max' => null],
        ['name' => 'Loyalty Voucher 5%',   'code' => 'LOYAL5',    'type' => Discount::TYPE_PERCENTAGE, 'applies_to' => Discount::APPLIES_TO_VOUCHER,          'value' => 5.00,  'max' => 30.00],
        ['name' => 'Item Clearance 25%',   'code' => 'CLEAR25',   'type' => Discount::TYPE_PERCENTAGE, 'applies_to' => Discount::APPLIES_TO_ITEM,             'value' => 25.00, 'max' => null],
    ];

    public function run(): void
    {
        Tenant::query()->orderBy('id')->get()->each(function (Tenant $tenant): void {
            $this->command?->info("Seeding catalog data for tenant #{$tenant->id} - {$tenant->name}...");

            $adminId = User::query()
                ->where('tenant_id', $tenant->id)
                ->where('role', User::TENANT_ADMIN)
                ->value('id');

            $this->seedCategoriesAndSubs($tenant, $adminId);
            $this->seedProductTypes($tenant, $adminId);
            $this->seedProducts($tenant, $adminId);
            $this->seedServices($tenant, $adminId);
            $this->seedDiscountGroups($tenant);
            $this->seedCustomersAndVehicles($tenant, $adminId);
            $this->seedDiscounts($tenant, $adminId);
            $this->assignProductDiscounts($tenant);
            $this->seedOrders($tenant, $adminId);
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

    private function seedProductTypes(Tenant $tenant, ?int $adminId): void
    {
        $sortOrder = 1;

        foreach (self::PRODUCT_TYPES as $slug => $name) {
            $exists = ProductType::withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->where('slug', $slug)
                ->exists();

            if ($exists) {
                continue;
            }

            $productType = new ProductType;
            $productType->tenant_id = $tenant->id;
            $productType->name = $name;
            $productType->slug = $slug;
            $productType->code = strtoupper(Str::slug($slug, '_'));
            $productType->description = "{$name} product type";
            $productType->sort_order = $sortOrder++;
            $productType->is_active = true;
            $productType->created_by = $adminId;
            $productType->updated_by = $adminId;
            $productType->save();
        }
    }

    private function productTypeId(Tenant $tenant, string $slug): ?int
    {
        return ProductType::withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->where('slug', $slug)
            ->value('id');
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
                $slug = Str::slug($p['name']);
                $sku = sprintf('%s-%s-%03d', $category->code, strtoupper(Str::slug($p['brand'], '')), $idx + 1);

                $product = Product::withoutTenantScope()
                    ->where('tenant_id', $tenant->id)
                    ->where(function ($query) use ($slug, $sku): void {
                        $query->where('slug', $slug)->orWhere('sku', $sku);
                    })
                    ->first();

                if (! $product) {
                    $sub = SubCategory::withoutTenantScope()
                        ->where('tenant_id', $tenant->id)
                        ->where('category_id', $category->id)
                        ->where('slug', Str::slug($p['sub']))
                        ->first();

                    $product = new Product;
                    $product->tenant_id = $tenant->id;
                    $product->category_id = $category->id;
                    $product->sub_category_id = $sub?->id;
                    $product->product_type_id = $this->productTypeId($tenant, $p['type']);
                    $product->product_type = $p['type'];
                    $product->name = $p['name'];
                    $product->slug = $slug;
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
                } else {
                    // Keep existing rows linked to the correct subcategory on re-seed.
                    $sub = SubCategory::withoutTenantScope()
                        ->where('tenant_id', $tenant->id)
                        ->where('category_id', $category->id)
                        ->where('slug', Str::slug($p['sub']))
                        ->first();

                    $product->forceFill([
                        'category_id' => $category->id,
                        'sub_category_id' => $sub?->id,
                        'name' => $p['name'],
                        'brand' => $p['brand'],
                        'unit' => $p['unit'],
                        'cost_price' => $p['cost'],
                        'sale_price' => $p['price'],
                        'updated_by' => $adminId,
                    ])->save();
                }

                $this->seedProductImage($tenant, $product, $p['image'] ?? null, $adminId);
            }
        }
    }

    /**
     * Push a local seed image onto the storage disk and record it as a primary
     * `images` row for the product. Idempotent: a product that already has an
     * image is left untouched, so the seeder is safe to re-run.
     */
    private function seedProductImage(Tenant $tenant, Product $product, ?string $imageFile, ?int $adminId): void
    {
        if (! $imageFile || $product->images()->exists()) {
            return;
        }

        $sourcePath = base_path(self::IMAGE_SOURCE_DIR.'/'.$imageFile);

        if (! is_file($sourcePath)) {
            $this->command?->warn("  Missing seed image, skipping: {$imageFile}");

            return;
        }

        $upload = new UploadedFile(
            $sourcePath,
            $imageFile,
            mime_content_type($sourcePath) ?: 'image/jpeg',
            null,
            true, // test mode: bypass is_uploaded_file() so it works outside an HTTP request
        );

        $prefix = sprintf('tenants/%s/%s/%s/images/', $tenant->id, $product->getTable(), $product->getKey());
        $stored = FileUploadManager::uploadFile($upload, $prefix, 'public');

        if (($stored['path'] ?? null) === null) {
            $this->command?->warn("  Failed to store seed image: {$imageFile}");

            return;
        }

        $image = new Image;
        $image->tenant_id = $tenant->id;
        $image->imageable_type = $product->getMorphClass();
        $image->imageable_id = $product->getKey();
        $image->disk = 'public';
        $image->path = $stored['path'];
        $image->file_name = $stored['doc_name'] ?? null;
        $image->original_name = $stored['original_doc_name'] ?? $imageFile;
        $image->extension = $stored['doc_type'] ?? 'jpg';
        $image->mime_type = $upload->getClientMimeType();
        $image->size = filesize($sourcePath) ?: 0;
        $image->collection = 'gallery';
        $image->sort_order = 1;
        $image->is_primary = true;
        $image->uploaded_by = $adminId;
        $image->save();
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

    private function seedDiscountGroups(Tenant $tenant): void
    {
        foreach (self::DISCOUNT_GROUPS as $g) {
            $slug = Str::slug($g['name']);

            $exists = DiscountGroup::withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->where('slug', $slug)
                ->exists();

            if ($exists) {
                continue;
            }

            $group = new DiscountGroup;
            $group->tenant_id = $tenant->id;
            $group->name = $g['name'];
            $group->slug = $slug;
            $group->type = $g['type'];
            $group->value = $g['value'];
            $group->min_limit = $g['min_limit'];
            $group->is_active = true;
            $group->save();
        }
    }

    private function discountGroupId(Tenant $tenant, ?string $slug): ?int
    {
        if (! $slug) {
            return null;
        }

        return DiscountGroup::withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->where('slug', $slug)
            ->value('id');
    }

    /**
     * Attach the seeded item-level discount to a handful of demo products so the
     * per-product discount path has data. Idempotent: only fills products that
     * don't already have a discount assigned.
     */
    private function assignProductDiscounts(Tenant $tenant): void
    {
        $itemDiscountId = Discount::withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->where('applies_to', Discount::APPLIES_TO_ITEM)
            ->where('is_active', true)
            ->value('id');

        if (! $itemDiscountId) {
            return;
        }

        Product::withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->whereIn('name', self::PRODUCTS_WITH_ITEM_DISCOUNT)
            ->whereNull('discount_id')
            ->update(['discount_id' => $itemDiscountId]);
    }

    private function seedCustomersAndVehicles(Tenant $tenant, ?int $adminId): void
    {
        foreach (self::CUSTOMERS as $idx => $c) {
            $customer = Customer::withoutTenantScope()
                ->where('tenant_id', $tenant->id)
                ->where('name', $c['name'])
                ->first();

            $groupId = $this->discountGroupId($tenant, $c['group'] ?? null);

            if (! $customer) {
                $customer = new Customer;
                $customer->tenant_id = $tenant->id;
                $customer->customer_type = $c['type'];
                $customer->discount_group_id = $groupId;
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
            } elseif ($groupId && $customer->discount_group_id === null) {
                // Backfill the group for customers seeded before discount groups existed.
                $customer->discount_group_id = $groupId;
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

    private function seedOrders(Tenant $tenant, ?int $adminId): void
    {
        // Check if orders already exist to ensure idempotency.
        if (Order::withoutTenantScope()->where('tenant_id', $tenant->id)->count() > 1) {
            return;
        }

        $customer = Customer::withoutTenantScope()->where('tenant_id', $tenant->id)->first();
        $vehicle = Vehicle::withoutTenantScope()->where('tenant_id', $tenant->id)->where('customer_id', $customer?->id)->first();
        $products = Product::withoutTenantScope()->where('tenant_id', $tenant->id)->limit(3)->get();

        if (! $customer || ! $products->count()) {
            return;
        }

        // Helper to generate a unique order number.
        $makeOrderNumber = function (string $prefix) use ($tenant) {
            $idx = 1;
            do {
                $num = sprintf('%s-T%d-%s-%03d', $prefix, $tenant->id, now()->format('Ymd'), $idx++);
            } while (Order::withoutTenantScope()->where('tenant_id', $tenant->id)->where('order_number', $num)->exists());

            return $num;
        };

        // 1. Seed Estimate
        // Subtotal = $39.99, Service Fee = $15.00, Discount = $5.00, Taxable Base = $49.99, Tax (5%) = $2.50, Total = $52.49
        $estProduct = $products->first();
        $estQty = 1;
        $estPrice = (float) $estProduct->sale_price;
        $estSubtotal = round($estPrice * $estQty, 2);
        $estFee = 15.00;
        $estDiscount = 5.00;
        $estTax = round(($estSubtotal + $estFee - $estDiscount) * 0.05, 2);
        $estTotal = round($estSubtotal + $estFee - $estDiscount + $estTax, 2);

        $estDiscountDetails = [
            'product_discount_amount' => $estDiscount,
            'customer_discount_amount' => 0.00,
            'customer_discount_eligible' => false,
            'customer_discount_reason' => null,
            'product_discounts' => [
                [
                    'product_id' => $estProduct->id,
                    'product_name' => $estProduct->name,
                    'discount_id' => 1,
                    'discount_name' => 'Clearance Off',
                    'discount_type' => 'fixed',
                    'discount_value' => 5.00,
                    'quantity' => 1,
                    'amount' => $estDiscount,
                ],
            ],
            'customer_discount' => null,
            'tax' => [
                'base_amount' => round($estSubtotal + $estFee - $estDiscount, 2),
                'amount' => $estTax,
                'lines' => [
                    [
                        'type' => 'Product',
                        'name' => $estProduct->name,
                        'quantity' => 1,
                        'tax_percentage' => 5.00,
                        'base_amount' => $estSubtotal,
                        'discount_amount' => $estDiscount,
                        'taxable_amount' => round($estSubtotal - $estDiscount, 2),
                        'tax_amount' => round(($estSubtotal - $estDiscount) * 0.05, 2),
                    ],
                    [
                        'type' => 'Service',
                        'name' => 'Standard Oil Change Service Fee',
                        'quantity' => 1,
                        'tax_percentage' => 5.00,
                        'base_amount' => $estFee,
                        'discount_amount' => 0.00,
                        'taxable_amount' => $estFee,
                        'tax_amount' => round($estFee * 0.05, 2),
                    ],
                ],
            ],
        ];

        $estServiceFeeDetails = [
            [
                'type' => 'service',
                'service_id' => 1,
                'name' => 'Standard Oil Change Service Fee',
                'code' => 'SVC-OIL-001',
                'amount' => $estFee,
                'tax_percentage' => 5.00,
            ],
        ];

        $estimate = Order::create([
            'tenant_id' => $tenant->id,
            'order_number' => $makeOrderNumber('EST'),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle?->id,
            'status' => Order::STATUS_ESTIMATE,
            'is_invoice' => false,
            'total_quantity' => $estQty,
            'subtotal_amount' => $estSubtotal,
            'discount_amount' => $estDiscount,
            'service_fee_amount' => $estFee,
            'service_fee_details' => $estServiceFeeDetails,
            'discount_details' => $estDiscountDetails,
            'tax_amount' => $estTax,
            'total_amount' => $estTotal,
            'payment_method' => null,
            'payment_amount' => 0.00,
            'change_amount' => 0.00,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);
        $estimate->items()->create([
            'tenant_id' => $tenant->id,
            'product_id' => $estProduct->id,
            'product_name' => $estProduct->name,
            'sku' => $estProduct->sku,
            'unit' => $estProduct->unit,
            'quantity' => $estQty,
            'unit_price' => $estPrice,
            'line_total' => $estSubtotal,
        ]);

        // 2. Seed Pending Order
        // Subtotal = 2 * $39.99 = $79.98, Fee = $10.00, Discount = 0.0, Taxable Base = $89.98, Tax (5%) = $4.50, Total = $94.48
        $pendProduct = $products->first();
        $pendQty = 2;
        $pendPrice = (float) $pendProduct->sale_price;
        $pendSubtotal = round($pendPrice * $pendQty, 2);
        $pendFee = 10.00;
        $pendTax = round(($pendSubtotal + $pendFee) * 0.05, 2);
        $pendTotal = round($pendSubtotal + $pendFee + $pendTax, 2);

        $pendDiscountDetails = [
            'product_discount_amount' => 0.00,
            'customer_discount_amount' => 0.00,
            'customer_discount_eligible' => false,
            'customer_discount_reason' => null,
            'product_discounts' => [],
            'customer_discount' => null,
            'tax' => [
                'base_amount' => round($pendSubtotal + $pendFee, 2),
                'amount' => $pendTax,
                'lines' => [
                    [
                        'type' => 'Product',
                        'name' => $pendProduct->name,
                        'quantity' => $pendQty,
                        'tax_percentage' => 5.00,
                        'base_amount' => $pendSubtotal,
                        'discount_amount' => 0.00,
                        'taxable_amount' => $pendSubtotal,
                        'tax_amount' => round($pendSubtotal * 0.05, 2),
                    ],
                    [
                        'type' => 'Service',
                        'name' => 'Disposal & Environmental Fee',
                        'quantity' => 1,
                        'tax_percentage' => 5.00,
                        'base_amount' => $pendFee,
                        'discount_amount' => 0.00,
                        'taxable_amount' => $pendFee,
                        'tax_amount' => round($pendFee * 0.05, 2),
                    ],
                ],
            ],
        ];

        $pendServiceFeeDetails = [
            [
                'type' => 'manual',
                'service_id' => null,
                'name' => 'Disposal & Environmental Fee',
                'code' => null,
                'amount' => $pendFee,
                'tax_percentage' => 5.00,
            ],
        ];

        $pending = Order::create([
            'tenant_id' => $tenant->id,
            'order_number' => $makeOrderNumber('ORD'),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle?->id,
            'status' => Order::STATUS_PENDING,
            'is_invoice' => false,
            'total_quantity' => $pendQty,
            'subtotal_amount' => $pendSubtotal,
            'discount_amount' => 0.00,
            'service_fee_amount' => $pendFee,
            'service_fee_details' => $pendServiceFeeDetails,
            'discount_details' => $pendDiscountDetails,
            'tax_amount' => $pendTax,
            'total_amount' => $pendTotal,
            'payment_method' => null,
            'payment_amount' => 0.00,
            'change_amount' => 0.00,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);
        $pending->items()->create([
            'tenant_id' => $tenant->id,
            'product_id' => $pendProduct->id,
            'product_name' => $pendProduct->name,
            'sku' => $pendProduct->sku,
            'unit' => $pendProduct->unit,
            'quantity' => $pendQty,
            'unit_price' => $pendPrice,
            'line_total' => $pendSubtotal,
        ]);

        // 3. Seed Partially Paid Order (with multiple payments)
        $partProduct1 = $products->first();
        $partProduct2 = $products->last();
        $partSubtotal = round(((float) $partProduct1->sale_price * 2) + (float) $partProduct2->sale_price, 2);
        $partFee = 25.00;
        $partDiscount = 10.00;
        $partTax = round(($partSubtotal + $partFee - $partDiscount) * 0.05, 2);
        $partTotal = round($partSubtotal + $partFee - $partDiscount + $partTax, 2);
        $partPaymentAmount = round($partTotal * 0.6, 2); // 60% paid

        $partDiscountDetails = [
            'product_discount_amount' => $partDiscount,
            'customer_discount_amount' => 0.00,
            'customer_discount_eligible' => false,
            'customer_discount_reason' => null,
            'product_discounts' => [
                [
                    'product_id' => $partProduct1->id,
                    'product_name' => $partProduct1->name,
                    'discount_id' => 1,
                    'discount_name' => 'Promotion Discount',
                    'discount_type' => 'fixed',
                    'discount_value' => 10.00,
                    'quantity' => 2,
                    'amount' => $partDiscount,
                ],
            ],
            'customer_discount' => null,
            'tax' => [
                'base_amount' => round($partSubtotal + $partFee - $partDiscount, 2),
                'amount' => $partTax,
                'lines' => [
                    [
                        'type' => 'Product',
                        'name' => $partProduct1->name,
                        'quantity' => 2,
                        'tax_percentage' => 5.00,
                        'base_amount' => round((float) $partProduct1->sale_price * 2, 2),
                        'discount_amount' => $partDiscount,
                        'taxable_amount' => round(((float) $partProduct1->sale_price * 2) - $partDiscount, 2),
                        'tax_amount' => round((((float) $partProduct1->sale_price * 2) - $partDiscount) * 0.05, 2),
                    ],
                    [
                        'type' => 'Product',
                        'name' => $partProduct2->name,
                        'quantity' => 1,
                        'tax_percentage' => 5.00,
                        'base_amount' => (float) $partProduct2->sale_price,
                        'discount_amount' => 0.00,
                        'taxable_amount' => (float) $partProduct2->sale_price,
                        'tax_amount' => round((float) $partProduct2->sale_price * 0.05, 2),
                    ],
                    [
                        'type' => 'Service',
                        'name' => 'Standard Oil Change Service Fee',
                        'quantity' => 1,
                        'tax_percentage' => 5.00,
                        'base_amount' => 15.00,
                        'discount_amount' => 0.00,
                        'taxable_amount' => 15.00,
                        'tax_amount' => 0.75,
                    ],
                    [
                        'type' => 'Service',
                        'name' => 'Disposal & Environmental Fee',
                        'quantity' => 1,
                        'tax_percentage' => 5.00,
                        'base_amount' => 10.00,
                        'discount_amount' => 0.00,
                        'taxable_amount' => 10.00,
                        'tax_amount' => 0.50,
                    ],
                ],
            ],
        ];

        $partServiceFeeDetails = [
            [
                'type' => 'service',
                'service_id' => 1,
                'name' => 'Standard Oil Change Service Fee',
                'code' => 'SVC-OIL-001',
                'amount' => 15.00,
                'tax_percentage' => 5.00,
            ],
            [
                'type' => 'manual',
                'service_id' => null,
                'name' => 'Disposal & Environmental Fee',
                'code' => null,
                'amount' => 10.00,
                'tax_percentage' => 5.00,
            ],
        ];

        $partOrder = Order::create([
            'tenant_id' => $tenant->id,
            'order_number' => $makeOrderNumber('ORD'),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle?->id,
            'status' => Order::STATUS_PARTIALLY_PAID,
            'is_invoice' => false,
            'total_quantity' => 3,
            'subtotal_amount' => $partSubtotal,
            'discount_amount' => $partDiscount,
            'service_fee_amount' => $partFee,
            'service_fee_details' => $partServiceFeeDetails,
            'discount_details' => $partDiscountDetails,
            'tax_amount' => $partTax,
            'total_amount' => $partTotal,
            'payment_method' => 'card',
            'payment_amount' => $partPaymentAmount,
            'change_amount' => 0.00,
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $partOrder->items()->createMany([
            [
                'tenant_id' => $tenant->id,
                'product_id' => $partProduct1->id,
                'product_name' => $partProduct1->name,
                'sku' => $partProduct1->sku,
                'unit' => $partProduct1->unit,
                'quantity' => 2,
                'unit_price' => (float) $partProduct1->sale_price,
                'line_total' => round((float) $partProduct1->sale_price * 2, 2),
            ],
            [
                'tenant_id' => $tenant->id,
                'product_id' => $partProduct2->id,
                'product_name' => $partProduct2->name,
                'sku' => $partProduct2->sku,
                'unit' => $partProduct2->unit,
                'quantity' => 1,
                'unit_price' => (float) $partProduct2->sale_price,
                'line_total' => (float) $partProduct2->sale_price,
            ],
        ]);

        // Create 3 partial payments
        $partAmt1 = round($partPaymentAmount * 0.3, 2);
        $partAmt2 = round($partPaymentAmount * 0.4, 2);
        $partAmt3 = round($partPaymentAmount - $partAmt1 - $partAmt2, 2);

        $partOrder->payments()->createMany([
            [
                'tenant_id' => $tenant->id,
                'amount' => $partAmt1,
                'payment_method' => 'cash',
                'created_by' => $adminId,
                'created_at' => now()->subDays(3)->addHours(1),
            ],
            [
                'tenant_id' => $tenant->id,
                'amount' => $partAmt2,
                'payment_method' => 'card',
                'created_by' => $adminId,
                'created_at' => now()->subDays(3)->addHours(3),
            ],
            [
                'tenant_id' => $tenant->id,
                'amount' => $partAmt3,
                'payment_method' => 'check',
                'created_by' => $adminId,
                'created_at' => now()->subDays(2),
            ],
        ]);

        // 4. Seed Fully Paid Order (with multiple payments summing to total)
        $paidProduct = $products->skip(1)->first() ?? $products->first();
        $paidSubtotal = round((float) $paidProduct->sale_price * 3, 2);
        $paidFee = 15.00;
        $paidDiscount = 20.00;
        $paidTax = round(($paidSubtotal + $paidFee - $paidDiscount) * 0.05, 2);
        $paidTotal = round($paidSubtotal + $paidFee - $paidDiscount + $paidTax, 2);

        $paidDiscountDetails = [
            'product_discount_amount' => $paidDiscount,
            'customer_discount_amount' => 0.00,
            'customer_discount_eligible' => false,
            'customer_discount_reason' => null,
            'product_discounts' => [
                [
                    'product_id' => $paidProduct->id,
                    'product_name' => $paidProduct->name,
                    'discount_id' => 3,
                    'discount_name' => 'Holiday Promo $20',
                    'discount_type' => 'fixed',
                    'discount_value' => 20.00,
                    'quantity' => 3,
                    'amount' => $paidDiscount,
                ],
            ],
            'customer_discount' => null,
            'tax' => [
                'base_amount' => round($paidSubtotal + $paidFee - $paidDiscount, 2),
                'amount' => $paidTax,
                'lines' => [
                    [
                        'type' => 'Product',
                        'name' => $paidProduct->name,
                        'quantity' => 3,
                        'tax_percentage' => 5.00,
                        'base_amount' => $paidSubtotal,
                        'discount_amount' => $paidDiscount,
                        'taxable_amount' => round($paidSubtotal - $paidDiscount, 2),
                        'tax_amount' => round(($paidSubtotal - $paidDiscount) * 0.05, 2),
                    ],
                    [
                        'type' => 'Service',
                        'name' => 'Standard Oil Change Service Fee',
                        'quantity' => 1,
                        'tax_percentage' => 5.00,
                        'base_amount' => $paidFee,
                        'discount_amount' => 0.00,
                        'taxable_amount' => $paidFee,
                        'tax_amount' => round($paidFee * 0.05, 2),
                    ],
                ],
            ],
        ];

        $paidServiceFeeDetails = [
            [
                'type' => 'service',
                'service_id' => 1,
                'name' => 'Standard Oil Change Service Fee',
                'code' => 'SVC-OIL-001',
                'amount' => $paidFee,
                'tax_percentage' => 5.00,
            ],
        ];

        $paidOrder = Order::create([
            'tenant_id' => $tenant->id,
            'order_number' => $makeOrderNumber('INV'),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle?->id,
            'status' => Order::STATUS_PAID,
            'is_invoice' => true,
            'invoice_date' => now()->subDays(1)->toDateString(),
            'total_quantity' => 3,
            'subtotal_amount' => $paidSubtotal,
            'discount_amount' => $paidDiscount,
            'service_fee_amount' => $paidFee,
            'service_fee_details' => $paidServiceFeeDetails,
            'discount_details' => $paidDiscountDetails,
            'tax_amount' => $paidTax,
            'total_amount' => $paidTotal,
            'payment_method' => 'card',
            'payment_amount' => $paidTotal,
            'change_amount' => 0.00,
            'paid_at' => now()->subDays(1),
            'created_by' => $adminId,
            'updated_by' => $adminId,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(1),
        ]);

        $paidOrder->items()->create([
            'tenant_id' => $tenant->id,
            'product_id' => $paidProduct->id,
            'product_name' => $paidProduct->name,
            'sku' => $paidProduct->sku,
            'unit' => $paidProduct->unit,
            'quantity' => 3,
            'unit_price' => (float) $paidProduct->sale_price,
            'line_total' => $paidSubtotal,
        ]);

        // Create 4 payments adding up to total
        $amt1 = round($paidTotal * 0.2, 2);
        $amt2 = round($paidTotal * 0.3, 2);
        $amt3 = round($paidTotal * 0.25, 2);
        $amt4 = round($paidTotal - $amt1 - $amt2 - $amt3, 2);

        $paidOrder->payments()->createMany([
            [
                'tenant_id' => $tenant->id,
                'amount' => $amt1,
                'payment_method' => 'cash',
                'created_by' => $adminId,
                'created_at' => now()->subDays(2)->addHours(2),
            ],
            [
                'tenant_id' => $tenant->id,
                'amount' => $amt2,
                'payment_method' => 'card',
                'created_by' => $adminId,
                'created_at' => now()->subDays(2)->addHours(5),
            ],
            [
                'tenant_id' => $tenant->id,
                'amount' => $amt3,
                'payment_method' => 'cash',
                'created_by' => $adminId,
                'created_at' => now()->subDays(1)->addHours(1),
            ],
            [
                'tenant_id' => $tenant->id,
                'amount' => $amt4,
                'payment_method' => 'card',
                'created_by' => $adminId,
                'created_at' => now()->subDays(1)->addHours(4),
            ],
        ]);
    }
}
