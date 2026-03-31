<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Modify the ENUM column to include 'others'
        DB::statement("ALTER TABLE purchase_payments MODIFY payment_method ENUM('cash', 'cheque', 'bank_transfer', 'others') DEFAULT 'cash'");
    }

    public function down()
    {
        // Revert to the previous ENUM definition
        DB::statement("ALTER TABLE purchase_payments MODIFY payment_method ENUM('cash', 'cheque', 'bank_transfer') DEFAULT 'cash'");
    }
};
