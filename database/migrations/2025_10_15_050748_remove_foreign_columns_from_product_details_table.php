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
        Schema::table('product_details', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['stock_id']);
            $table->dropForeign(['price_id']);
            $table->dropForeign(['supplier_id']);

            // Then drop the columns
            $table->dropColumn(['stock_id', 'price_id', 'supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->foreignId('stock_id')
                ->constrained('product_stocks')
                ->onDelete('cascade');
            $table->foreignId('price_id')
                ->constrained('product_prices');
            $table->foreignId('supplier_id')
                ->constrained('product_suppliers')
                ->onDelete('cascade');
        });
    }
};
