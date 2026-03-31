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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('category'); // Category name
            $table->string('expense_type'); // 'daily', 'monthly', etc.
            $table->decimal('amount', 10, 2); // Amount with 2 decimal places
            $table->date('date'); // Expense date
            $table->string('status')->nullable(); // Optional status
            $table->text('description')->nullable(); // Optional description
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
