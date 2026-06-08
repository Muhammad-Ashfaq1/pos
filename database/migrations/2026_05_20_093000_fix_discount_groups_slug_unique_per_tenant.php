<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Older databases ran the create migration when `slug` carried a GLOBAL unique
 * index (`discount_groups_slug_unique`), which prevents two tenants from sharing
 * a slug (e.g. "silver-tier"). This realigns the schema with the intended
 * per-tenant uniqueness: drop the global index, add a composite (tenant_id, slug).
 *
 * Guarded so it is a no-op on fresh databases that already have the composite.
 */
return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('discount_groups'))->pluck('name');

        Schema::table('discount_groups', function (Blueprint $table) use ($indexes): void {
            if ($indexes->contains('discount_groups_slug_unique')) {
                $table->dropUnique('discount_groups_slug_unique');
            }

            if (! $indexes->contains('discount_groups_tenant_id_slug_unique')) {
                $table->unique(['tenant_id', 'slug'], 'discount_groups_tenant_id_slug_unique');
            }
        });
    }

    public function down(): void
    {
        $indexes = collect(Schema::getIndexes('discount_groups'))->pluck('name');

        Schema::table('discount_groups', function (Blueprint $table) use ($indexes): void {
            if ($indexes->contains('discount_groups_tenant_id_slug_unique')) {
                $table->dropUnique('discount_groups_tenant_id_slug_unique');
            }

            if (! $indexes->contains('discount_groups_slug_unique')) {
                $table->unique('slug', 'discount_groups_slug_unique');
            }
        });
    }
};
