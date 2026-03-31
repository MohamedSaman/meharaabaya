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
        // Create payment_allocations table to track which payment paid which invoice
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->decimal('allocated_amount', 15, 2);
            $table->timestamps();
            
            $table->index('payment_id');
            $table->index('sale_id');
        });

        // Add bank transfer columns to payments table if they don't exist
        Schema::table('payments', function (Blueprint $table) {

            if (Schema::hasColumn('payments', 'sale_id')) {
                $table->foreignId('sale_id')->nullable()->change();
            }
            if (Schema::hasColumn('payments', 'payment_method')) {
                $table->enum('payment_method', [
                'cash',
                'cheque',
                'credit',
                'bank_transfer'])->change();
            }
            if (!Schema::hasColumn('payments', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('payment_reference');
            }
            if (!Schema::hasColumn('payments', 'transfer_date')) {
                $table->date('transfer_date')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('payments', 'transfer_reference')) {
                $table->string('transfer_reference')->nullable()->after('transfer_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
        
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'bank_name')) {
                $table->dropColumn('bank_name');
            }
            if (Schema::hasColumn('payments', 'transfer_date')) {
                $table->dropColumn('transfer_date');
            }
            if (Schema::hasColumn('payments', 'transfer_reference')) {
                $table->dropColumn('transfer_reference');
            }
        });
    }
};