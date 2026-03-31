<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessControlLogsTable extends Migration
{
    public function up()
    {
        Schema::create('access_control_logs', function (Blueprint $table) {
            $table->id();

            // Basic Access Info
            $table->string('employee_id', 50);
            $table->dateTime('access_datetime');
            $table->date('access_date');
            $table->time('access_time');
            $table->enum('authentication_result', ['Succeeded', 'Failed']);

            // Image, Device & Reader Info
            $table->text('captured_picture_url')->nullable();
            $table->string('authentication_type', 100)->nullable();
            $table->string('device_name', 100)->nullable();
            $table->string('device_serial_no', 100)->nullable();
            $table->string('resource_name', 100)->nullable();
            $table->string('reader_name', 100)->nullable();

            // Person Info
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('person_name', 100)->nullable();
            $table->string('person_group', 100)->nullable();

            // Extra Info
            $table->string('card_number', 100)->nullable();
            $table->enum('direction', ['Enter', 'Exit']);
            $table->string('skin_surface_temperature', 10)->nullable();
            $table->enum('temperature_status', ['Normal', 'Abnormal', 'Unknown'])->nullable();
            $table->enum('mask_wearing_status', ['With Mask', 'No Mask', 'Unknown'])->nullable();
            $table->enum('attendance_status', [
                'Check-In',
                'Check-Out',
                'Break Out',
                'Break In',
                'Overtime In',
                'Overtime Out'
            ])->nullable();

            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    public function down()
    {
        Schema::dropIfExists('access_control_logs');
    }
}
// This migration creates the access_control_logs table with various fields to log access control events.