<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds an overpayment column to track credit balance for suppliers.
     * When a supplier return is made and there are no dues, the return amount is stored as overpayment.
     * This overpayment can be used to reduce the amount in future purchases.
     */
    public function up(): void
    {
        Schema::table('product_suppliers', function (Blueprint $table) {
            $table->decimal('overpayment', 15, 2)->default(0)->after('notes')
                ->comment('Credit balance from supplier returns when no dues exist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_suppliers', function (Blueprint $table) {
            $table->dropColumn('overpayment');
        });
    }
};
