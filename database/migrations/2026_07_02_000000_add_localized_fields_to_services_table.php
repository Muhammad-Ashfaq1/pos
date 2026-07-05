<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->string('title_en', 150)->nullable()->after('name');
            $table->string('title_ar', 150)->nullable()->after('title_en');
            $table->text('description_en')->nullable()->after('description');
            $table->text('description_ar')->nullable()->after('description_en');
        });

        DB::table('services')->update([
            'title_en' => DB::raw('name'),
            'description_en' => DB::raw('description'),
        ]);
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn([
                'title_en',
                'title_ar',
                'description_en',
                'description_ar',
            ]);
        });
    }
};
