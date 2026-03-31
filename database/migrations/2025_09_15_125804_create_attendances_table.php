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
        Schema::create('attendances', function (Blueprint $table) {
            $table->bigIncrements('attendance_id'); // primary key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // foreign key to users table
            $table->string('fingerprint_id')->nullable();
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->time('check_out')->nullable();
            $table->decimal('time_worked', 5, 2)->nullable();
            $table->decimal('late_hours', 5, 2)->default(0.00);
            $table->decimal('over_time', 5, 2)->default(0.00);
            $table->enum('status', ['present', 'absent'])->default('present');
            $table->enum('present_status', ['late', 'early', 'ontime'])->nullable();
            $table->text('description')->nullable();
            $table->timestamps(); // creates created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
