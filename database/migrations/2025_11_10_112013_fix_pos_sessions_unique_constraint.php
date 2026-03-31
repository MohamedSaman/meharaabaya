<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Simply add index without unique constraint issues
        // The application logic will handle ensuring only one open session per day
        DB::statement('CREATE INDEX idx_pos_sessions_user_date_status ON pos_sessions (user_id, session_date, status)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_pos_sessions_user_date_status ON pos_sessions');
    }
};
