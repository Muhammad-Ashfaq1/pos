<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();


            $table->foreignId('tenant_id')
                  ->constrained()
                  ->cascadeOnDelete();


            $table->foreignId('category_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('name');
            $table->string('code', 50)->nullable();


            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);


            $table->timestamps();
            $table->softDeletes();


            $table->index(['tenant_id', 'category_id']);
            $table->index(['tenant_id', 'is_active']);


            $table->unique(['tenant_id', 'category_id', 'name']);
            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_categories');
    }
};
