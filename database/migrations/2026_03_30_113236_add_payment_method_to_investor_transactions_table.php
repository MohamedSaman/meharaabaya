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
        Schema::table('investor_transactions', function (Blueprint $table) {
            $table->string('payment_method', 20)->default('cash')->after('description');
            $table->unsignedBigInteger('cheque_id')->nullable()->after('payment_method');

            $table->foreign('cheque_id')->references('id')->on('cheques')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investor_transactions', function (Blueprint $table) {
            $table->dropForeign(['cheque_id']);
            $table->dropColumn(['payment_method', 'cheque_id']);
        });
    }
};
