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
        Schema::table('payments', function (Blueprint $table) {
            // Add notes column if it doesn't exist
            if (!Schema::hasColumn('payments', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            
            // Add created_by column if it doesn't exist
            if (!Schema::hasColumn('payments', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('notes');
            }
            
            // Add transfer_date column if it doesn't exist (for bank transfers)
            if (!Schema::hasColumn('payments', 'transfer_date')) {
                $table->date('transfer_date')->nullable()->after('bank_name');
            }
            
            // Add transfer_reference column if it doesn't exist (for bank transfers)
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
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('payments', 'created_by')) {
                $table->dropColumn('created_by');
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
