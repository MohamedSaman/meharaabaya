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
        Schema::create('product_details', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Unique product code/SKU');
            $table->string('name');
            $table->string('model')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->foreignId('brand_id')->constrained('brand_lists')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('category_lists')->onDelete('cascade');
            $table->foreignId('stock_id')->constrained('product_stocks')->onDelete('cascade');
            $table->foreignId('price_id')->constrained('product_prices');
            $table->foreignId('supplier_id')->constrained('product_suppliers')->onDelete('cascade');    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_details');
    }
};
