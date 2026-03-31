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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', [
                'cash',
                'cheque',
                'credit'
            ])->default('cash');
            $table->string('payment_reference')->nullable();
            $table->string('card_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->boolean('is_completed')->default(true);
            $table->timestamp('payment_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('due_payment_method')->nullable();
            $table->string('due_payment_attachment')->nullable();
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'paid'
            ])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
