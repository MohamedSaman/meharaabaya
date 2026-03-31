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
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('batch_number')->unique();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->decimal('supplier_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->integer('remaining_quantity')->default(0);
            $table->date('received_date');
            $table->enum('status', ['active', 'depleted', 'expired'])->default('active');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('product_details')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('set null');

            $table->index(['product_id', 'status']);
            $table->index('received_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
