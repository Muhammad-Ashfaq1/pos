<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const RENAMES = [
        'OIL' => 'Oil Change',
        'FLT' => 'Filter Replacement',
        'BRK' => 'Brake Service',
        'BAT' => 'Battery Service',
        'TIR' => 'Tire Service',
        // Legacy product-category names (pre-rename)
        'Engine Oils' => 'Oil Change',
        'Filters' => 'Filter Replacement',
        'Brakes' => 'Brake Service',
        'Batteries' => 'Battery Service',
        'Tires' => 'Tire Service',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('service_categories')) {
            return;
        }

        $now = now();

        foreach (self::RENAMES as $from => $to) {
            $slug = Str::slug($to);

            // Match by stable service code (preferred).
            if (strlen($from) <= 5 && $from === strtoupper($from)) {
                DB::table('service_categories')
                    ->where('code', $from)
                    ->update([
                        'name' => $to,
                        'slug' => $slug,
                        'description' => "{$to} services",
                        'updated_at' => $now,
                    ]);

                continue;
            }

            // Match leftover product-style names.
            DB::table('service_categories')
                ->where('name', $from)
                ->update([
                    'name' => $to,
                    'slug' => $slug,
                    'description' => "{$to} services",
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_categories')) {
            return;
        }

        $now = now();
        $revert = [
            'OIL' => 'Engine Oils',
            'FLT' => 'Filters',
            'BRK' => 'Brakes',
            'BAT' => 'Batteries',
            'TIR' => 'Tires',
        ];

        foreach ($revert as $code => $name) {
            DB::table('service_categories')
                ->where('code', $code)
                ->update([
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => "{$name} services",
                    'updated_at' => $now,
                ]);
        }
    }
};
