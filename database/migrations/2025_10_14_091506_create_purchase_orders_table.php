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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->foreignId('product_id')->constrained('product_details')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('product_suppliers')->onDelete('cascade');
            $table->date('order_date');
            $table->date('received_date')->nullable();
            $table->enum('status',['pending','complete','received','cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
