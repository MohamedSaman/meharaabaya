<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds an overpayment_used column to track how much supplier 
     * overpayment credit was applied to a specific payment transaction.
     */
    public function up(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->decimal('overpayment_used', 15, 2)->default(0)->after('notes')
                ->comment('Amount of supplier overpayment credit used in this payment');
        });

        // Update payment_method enum to include overpayment_credit option
        // Note: MySQL specific - you may need to adjust for other databases
        DB::statement("ALTER TABLE purchase_payments MODIFY COLUMN payment_method ENUM('cash', 'cheque', 'bank_transfer', 'others', 'overpayment_credit') DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->dropColumn('overpayment_used');
        });

        // Revert payment_method enum
        DB::statement("ALTER TABLE purchase_payments MODIFY COLUMN payment_method ENUM('cash', 'cheque', 'bank_transfer', 'others') DEFAULT 'cash'");
    }
};
