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
        Schema::table('sales_detail', function (Blueprint $table) {
            $table->foreign(['idProduct'], 'sales_detail_ibfk_1')->references(['id'])->on('products')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['idUser'], 'sales_detail_ibfk_2')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_detail', function (Blueprint $table) {
            $table->dropForeign('sales_detail_ibfk_1');
            $table->dropForeign('sales_detail_ibfk_2');
        });
    }
};
