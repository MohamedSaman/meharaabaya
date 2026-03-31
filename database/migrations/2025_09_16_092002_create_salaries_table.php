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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id('salary_id'); // Primary Key
            
            // Foreign key relationship to the 'users' table
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->date('salary_month'); // e.g., '2025-09-01' to represent September 2025
            $table->string('salary_type')->default('monthly'); // e.g., 'monthly', 'weekly', 'contract'

            // Salary components - using decimal for financial accuracy
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('bonus', 10, 2)->default(0.00);
            $table->decimal('allowance', 10, 2)->default(0.00);
            $table->decimal('deductions', 10, 2)->default(0.00);
            $table->decimal('overtime', 10, 2)->default(0.00); // Overtime pay amount
            $table->decimal('additional_salary', 10, 2)->default(0.00);
            $table->decimal('net_salary', 10, 2);

            // Work hours
            $table->decimal('total_hours', 8, 2)->nullable();
            $table->decimal('overtime_hours', 8, 2)->nullable();

            // Payment status
            $table->string('payment_status')->default('pending'); // e.g., 'pending', 'paid', 'failed'
            
            // Timestamps for record creation and updates
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};