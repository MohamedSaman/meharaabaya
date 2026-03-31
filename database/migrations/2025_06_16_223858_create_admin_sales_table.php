<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminSalesTable extends Migration
{
    public function up()
    {
        Schema::create('admin_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->integer('total_quantity');
            $table->decimal('total_value', 12, 2);
            $table->integer('sold_quantity')->default(0);
            $table->decimal('sold_value', 12, 2)->default(0.00);
            $table->enum('status', ['assigned', 'partial', 'completed', 'cancelled'])->default('assigned');
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_sales');
    }
}
