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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // 🧹 First drop the foreign key
            $table->dropForeign(['product_id']);

            // 🗑️ Then drop the column
            $table->dropColumn('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Optional rollback
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('product_details');
        });
    }
};
