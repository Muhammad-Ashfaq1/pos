<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->morphs('imageable');
            $table->string('disk', 50)->default('public');
            $table->string('path');
            $table->string('file_name')->nullable();
            $table->string('original_name');
            $table->string('extension', 20)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('collection', 50)->default('gallery');
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_primary')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'collection'], 'images_tenant_collection_index');
            $table->index(['tenant_id', 'is_primary'], 'images_tenant_primary_index');
            $table->index(['tenant_id', 'imageable_type', 'imageable_id'], 'images_tenant_imageable_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
