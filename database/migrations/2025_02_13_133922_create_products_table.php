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
            $table->id();
            $table->foreignId('categorieID') // Clave foránea
                ->constrained('categories') // Hace referencia a la tabla categories
                ->onDelete('cascade');
            $table->string('name', 50);
            $table->text('description');
            $table->string('image', 100);
            $table->double('priceNow');
            $table->double('priceBefore');
            $table->integer('numSales')->default(0);
            $table->integer('stock');
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
