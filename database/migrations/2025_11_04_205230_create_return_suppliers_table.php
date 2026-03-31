<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('return_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product_stocks')->onDelete('cascade');
            $table->integer('return_quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->enum('return_reason', ['damaged', 'defective', 'wrong_item', 'excess', 'other'])->default('damaged');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('purchase_order_id');
            $table->index('product_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('return_suppliers');
    }
};