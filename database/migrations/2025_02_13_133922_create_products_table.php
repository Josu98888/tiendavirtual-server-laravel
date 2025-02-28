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
        Schema::create('products', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('categorieID')->nullable()->index('product_fk');
            $table->string('name', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('image', 100)->nullable();
            $table->double('priceNow', null, 0)->nullable();
            $table->double('priceBefore', null, 0)->nullable();
            $table->integer('numSales')->nullable()->default(0);
            $table->integer('stock')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
