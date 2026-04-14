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
        Schema::dropIfExists('domains');

        if (! Schema::hasTable('tenants')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('tenants', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }

            if (! Schema::hasColumn('tenants', 'owner_email')) {
                $table->string('owner_email')->nullable()->after('owner_name');
            }

            if (! Schema::hasColumn('tenants', 'owner_phone')) {
                $table->string('owner_phone')->nullable()->after('owner_email');
            }

            if (! Schema::hasColumn('tenants', 'business_name')) {
                $table->string('business_name')->nullable()->after('owner_phone');
            }

            if (! Schema::hasColumn('tenants', 'business_email')) {
                $table->string('business_email')->nullable()->after('business_name');
            }

            if (! Schema::hasColumn('tenants', 'business_phone')) {
                $table->string('business_phone')->nullable()->after('business_email');
            }

            if (! Schema::hasColumn('tenants', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('tenants', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('rejected_at');
            }

            if (! Schema::hasColumn('tenants', 'onboarding_completed_at')) {
                $table->timestamp('onboarding_completed_at')->nullable()->after('suspended_at');
            }

            if (! Schema::hasColumn('tenants', 'settings')) {
                $table->json('settings')->nullable()->after('onboarding_completed_at');
            }
        });

        if ($this->isMySql()) {
            DB::statement("ALTER TABLE tenants MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        }

        DB::table('tenants')->whereNull('name')->update([
            'name' => DB::raw('COALESCE(shop_name, business_name, owner_name)'),
        ]);

        DB::table('tenants')->whereNull('owner_email')->update([
            'owner_email' => DB::raw('email'),
        ]);

        DB::table('tenants')->whereNull('owner_phone')->update([
            'owner_phone' => DB::raw('phone'),
        ]);

        DB::table('tenants')->whereNull('business_name')->update([
            'business_name' => DB::raw('COALESCE(shop_name, name)'),
        ]);

        DB::table('tenants')->whereNull('business_email')->update([
            'business_email' => DB::raw('COALESCE(email, owner_email)'),
        ]);

        DB::table('tenants')->whereNull('business_phone')->update([
            'business_phone' => DB::raw('COALESCE(phone, owner_phone)'),
        ]);

        DB::table('tenants')
            ->whereNull('onboarding_completed_at')
            ->where('onboarding_status', 'completed')
            ->update([
                'onboarding_completed_at' => DB::raw('COALESCE(approved_at, updated_at, created_at)'),
            ]);

        DB::table('tenants')
            ->whereNull('rejected_at')
            ->where('status', 'rejected')
            ->update([
                'rejected_at' => DB::raw('updated_at'),
            ]);

        DB::table('tenants')
            ->whereNull('suspended_at')
            ->where('status', 'suspended')
            ->update([
                'suspended_at' => DB::raw('updated_at'),
            ]);

        DB::table('tenants')
            ->select('id', 'slug', 'name', 'business_name', 'shop_name')
            ->orderBy('id')
            ->get()
            ->each(function (object $tenant): void {
                if (! blank($tenant->slug)) {
                    return;
                }

                $base = $tenant->name ?: $tenant->business_name ?: $tenant->shop_name ?: 'tenant';
                $slug = Str::slug($base);
                $slug = $slug !== '' ? "{$slug}-{$tenant->id}" : "tenant-{$tenant->id}";

                DB::table('tenants')
                    ->where('id', $tenant->id)
                    ->update(['slug' => $slug]);
            });

        Schema::table('tenants', function (Blueprint $table) {
            $table->unique('slug', 'tenants_slug_unique');
            $table->unique('owner_email', 'tenants_owner_email_unique');
            $table->index('status', 'tenants_status_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tenants')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'slug')) {
                $table->dropUnique('tenants_slug_unique');
                $table->dropColumn('slug');
            }

            if (Schema::hasColumn('tenants', 'owner_email')) {
                $table->dropUnique('tenants_owner_email_unique');
                $table->dropColumn('owner_email');
            }

            if (Schema::hasColumn('tenants', 'status')) {
                $table->dropIndex('tenants_status_index');
            }

            foreach ([
                'name',
                'owner_phone',
                'business_name',
                'business_email',
                'business_phone',
                'rejected_at',
                'suspended_at',
                'onboarding_completed_at',
                'settings',
            ] as $column) {
                if (Schema::hasColumn('tenants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function isMySql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
};
