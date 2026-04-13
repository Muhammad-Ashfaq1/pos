<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

       
    $table->string('shop_name');
    $table->string('business_type')->nullable();

    $table->string('owner_name');
    $table->string('email')->unique();
    $table->string('phone')->nullable();

    $table->string('website_url')->nullable();

    $table->text('address')->nullable();
    $table->string('city')->nullable();
    $table->string('state')->nullable();
    $table->string('country')->nullable();

    $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])
          ->default('pending');

     $table->unsignedBigInteger('approved_by')->nullable();
  ;

    $table->timestamp('approved_at')->nullable();
    $table->text('rejected_reason')->nullable();

    $table->enum('onboarding_status', [
        'not_started',
        'in_progress',
        'completed'
    ])->default('not_started');



       

            $table->timestamps();

            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
