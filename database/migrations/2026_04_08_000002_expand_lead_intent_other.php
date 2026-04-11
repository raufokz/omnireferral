<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `leads` MODIFY `intent` ENUM('buyer','seller','investor','other') NOT NULL");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leads') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `leads` MODIFY `intent` ENUM('buyer','seller','investor') NOT NULL");
        }
    }
};

