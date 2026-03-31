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
        Schema::table('product_stocks', function (Blueprint $table) {
            // Add product_id as a separate column after 'id'
            $table->unsignedBigInteger('product_id')->after('id');

            // Add foreign key constraint
            $table->foreign('product_id')
                  ->references('id')
                  ->on('product_details')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['product_id']);

            // Then drop the column
            $table->dropColumn('product_id');
        });
    }
};
