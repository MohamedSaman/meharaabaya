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
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('available_stock')->default(0)->comment('Stock available for sale');
            $table->integer('damage_stock')->default(0)->comment('Quantity of damaged items');
            $table->integer('total_stock')->default(0)->comment('Total of all stock types');
            $table->integer('sold_count')->default(0);
            $table->integer('restocked_quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};