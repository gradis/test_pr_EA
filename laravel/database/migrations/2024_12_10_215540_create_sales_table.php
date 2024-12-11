<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
                        $table->string('g_number');
                        $table->date('date')->nullable();
                        $table->dateTime('last_change_date')->nullable();
                        $table->string('supplier_article')->nullable();
                        $table->string('tech_size')->nullable();
                        $table->bigInteger('barcode')->nullable();
                        $table->decimal('total_price', 15, 2)->nullable();
                        $table->decimal('discount_percent', 5, 2)->nullable();
                        $table->boolean('is_supply')->nullable();
                        $table->boolean('is_realization')->nullable();
                        $table->decimal('promo_code_discount', 15, 2)->nullable();
                        $table->string('warehouse_name')->nullable();
                        $table->string('country_name')->nullable();
                        $table->string('oblast_okrug_name')->nullable();
                        $table->string('region_name')->nullable();
                        $table->bigInteger('income_id')->nullable();
                        $table->string('sale_id')->nullable();
                        $table->bigInteger('odid')->nullable();
                        $table->decimal('spp', 15, 2)->nullable();
                        $table->decimal('for_pay', 15, 2)->nullable();
                        $table->decimal('finished_price', 15, 2)->nullable();
                        $table->decimal('price_with_disc', 15, 2)->nullable();
                        $table->bigInteger('nm_id')->nullable();
                        $table->string('subject')->nullable();
                        $table->string('category')->nullable();
                        $table->string('brand')->nullable();
                        $table->boolean('is_storno')->nullable();
                        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};