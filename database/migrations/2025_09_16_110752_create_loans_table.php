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
        Schema::create('loans', function (Blueprint $table) {
                $table->bigIncrements('loan_id');
                $table->unsignedBigInteger('user_id');
                $table->decimal('loan_amount', 15, 2);
                $table->decimal('interest_rate', 5, 2);
                $table->date('start_date');
                $table->integer('term_month');
                $table->decimal('remaining_balance', 15, 2)->default(0);
                $table->string('status')->default('active');
                $table->decimal('monthly_payment', 15, 2)->default(0);
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
