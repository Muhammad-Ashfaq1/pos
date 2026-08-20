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
        Schema::table('services', function (Blueprint $table): void {
            $table->string('slug', 170)->nullable()->after('name');
        });

        DB::table('services')->orderBy('id')->chunkById(100, function ($services): void {
            foreach ($services as $service) {
                $source = (string) ($service->code ?: $service->name ?: 'service');
                $slug = Str::slug($source) ?: 'service';

                DB::table('services')
                    ->where('id', $service->id)
                    ->update(['slug' => $slug]);
            }
        });

        // Ensure tenant-level uniqueness after slugify collisions.
        $rows = DB::table('services')
            ->select(['id', 'tenant_id', 'slug'])
            ->orderBy('tenant_id')
            ->orderBy('id')
            ->get();

        $seen = [];
        foreach ($rows as $row) {
            $tenantId = (string) $row->tenant_id;
            $base = $row->slug ?: 'service';
            $slug = $base;
            $suffix = 2;

            while (isset($seen[$tenantId][$slug])) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            $seen[$tenantId][$slug] = true;

            if ($slug !== $row->slug) {
                DB::table('services')->where('id', $row->id)->update(['slug' => $slug]);
            }
        }

        Schema::table('services', function (Blueprint $table): void {
            $table->dropUnique('services_tenant_code_unique');
            $table->dropColumn('code');
            $table->unique(['tenant_id', 'slug'], 'services_tenant_slug_unique');
            $table->index(['tenant_id', 'slug'], 'services_tenant_slug_index');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->string('code', 50)->nullable()->after('name');
        });

        DB::table('services')->orderBy('id')->chunkById(100, function ($services): void {
            foreach ($services as $service) {
                $code = Str::upper(Str::slug((string) ($service->slug ?: $service->name ?: 'SVC'), '_'));
                $code = Str::limit($code !== '' ? $code : 'SVC', 50, '');

                DB::table('services')
                    ->where('id', $service->id)
                    ->update(['code' => $code]);
            }
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->dropUnique('services_tenant_slug_unique');
            $table->dropIndex('services_tenant_slug_index');
            $table->dropColumn('slug');
            $table->unique(['tenant_id', 'code'], 'services_tenant_code_unique');
        });
    }
};
