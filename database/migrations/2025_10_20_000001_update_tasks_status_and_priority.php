<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL we need to modify the enum definitions. Use DB::statement for reliability.
        // Expand status enum to include in_progress and cancelled
        DB::statement("ALTER TABLE `tasks` MODIFY `status` ENUM('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending'");

        // Expand priority enum to include urgent
        DB::statement("ALTER TABLE `tasks` MODIFY `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status and priority to previous smaller sets.
        DB::statement("ALTER TABLE `tasks` MODIFY `status` ENUM('pending','completed') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE `tasks` MODIFY `priority` ENUM('low','medium','high') NOT NULL DEFAULT 'medium'");
    }
};
