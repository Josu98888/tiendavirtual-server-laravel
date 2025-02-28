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
        Schema::create('sales_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idUser') // Clave foránea
                ->constrained('users') // Hace referencia a la tabla users
                ->onDelete('cascade');
            $table->foreignId('idProduct') // Clave foránea
                ->constrained('products') // Hace referencia a la tabla products
                ->onDelete('cascade');
            $table->integer('quantity');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_detail');
    }
};
