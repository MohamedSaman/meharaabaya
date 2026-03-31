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
        Schema::create('user_details', function (Blueprint $table) {
            $table->bigIncrements('user_details_id'); // primary key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // link to users table
            $table->date('dob')->nullable();
            $table->integer('age')->nullable();
            $table->string('nic_num')->nullable();
            $table->text('address')->nullable();
            $table->string('work_role')->nullable();
            $table->string('department')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('join_date')->nullable();
            $table->string('fingerprint_id')->nullable();
            $table->json('allowance')->nullable(); // stores {"fix","food"} as JSON
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->string('user_image')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
