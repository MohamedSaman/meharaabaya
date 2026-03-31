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
        Schema::create('staff_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product_details')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_per_unit', 12, 2)->default(0);
            $table->decimal('total_discount', 12, 2)->default(0);
            $table->decimal('total_value', 12, 2);
            $table->integer('sold_quantity')->default(0);
            $table->decimal('sold_value', 12, 2)->default(0);
            $table->enum('status', ['assigned', 'partial', 'completed'])->default('assigned');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_products');
    }
};
