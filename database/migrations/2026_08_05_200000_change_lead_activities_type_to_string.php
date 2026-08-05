<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_activities')) {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `lead_activities` MODIFY `type` VARCHAR(50) NOT NULL DEFAULT 'note'");
            } else {
                Schema::table('lead_activities', function (Blueprint $table) {
                    $table->string('type', 50)->default('note')->change();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lead_activities') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `lead_activities` MODIFY `type` ENUM('note','tag','reminder') NOT NULL DEFAULT 'note'");
        }
    }
};
