<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->string('slug', 170)->nullable()->after('name');
        });

        $categories = DB::table('categories')
            ->select('id', 'tenant_id', 'name')
            ->orderBy('tenant_id')
            ->orderBy('id')
            ->get();

        $usedSlugsByTenant = [];

        foreach ($categories as $category) {
            $tenantId = (string) $category->tenant_id;
            $usedSlugsByTenant[$tenantId] ??= [];

            $baseSlug = Str::slug((string) $category->name);
            $baseSlug = $baseSlug !== '' ? $baseSlug : "category-{$category->id}";
            $slug = $baseSlug;
            $suffix = 2;

            while (in_array($slug, $usedSlugsByTenant[$tenantId], true)) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            DB::table('categories')
                ->where('id', $category->id)
                ->update(['slug' => $slug]);

            $usedSlugsByTenant[$tenantId][] = $slug;
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->unique(['tenant_id', 'slug'], 'categories_tenant_slug_unique');
            $table->index(['tenant_id', 'slug'], 'categories_tenant_slug_index');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropUnique('categories_tenant_slug_unique');
            $table->dropIndex('categories_tenant_slug_index');
            $table->dropColumn('slug');
        });
    }
};
