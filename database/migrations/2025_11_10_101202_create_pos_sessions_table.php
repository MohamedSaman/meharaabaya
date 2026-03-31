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
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('session_date');
            $table->decimal('opening_cash', 15, 2)->default(0);
            $table->decimal('closing_cash', 15, 2)->nullable();

            // Sales breakdown
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('cash_sales', 15, 2)->default(0);
            $table->decimal('cheque_payment', 15, 2)->default(0);
            $table->decimal('credit_card_payment', 15, 2)->default(0);
            $table->decimal('bank_transfer', 15, 2)->default(0);
            $table->decimal('late_payment_bulk', 15, 2)->default(0);

            // Expenses and other deductions
            $table->decimal('refunds', 15, 2)->default(0);
            $table->decimal('expenses', 15, 2)->default(0);
            $table->decimal('cash_deposit_bank', 15, 2)->default(0);

            // Calculated totals
            $table->decimal('expected_cash', 15, 2)->default(0); // opening + cash_sales - refunds - expenses - deposit
            $table->decimal('cash_difference', 15, 2)->default(0); // closing - expected

            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();

            // Ensure only one open session per user per day
            $table->unique(['user_id', 'session_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};
