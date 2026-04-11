<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Expand enum values in a MySQL-safe way (avoids doctrine/dbal dependency).
        if (Schema::hasTable('leads') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `leads` MODIFY `intent` ENUM('buyer','seller','investor') NOT NULL");
            DB::statement("ALTER TABLE `leads` MODIFY `status` ENUM('new','contacted','in_progress','qualified','assigned','closed','not_interested') NOT NULL DEFAULT 'new'");
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'must_reset_password')) {
                $table->boolean('must_reset_password')->default(false)->after('password');
            }
            if (! Schema::hasColumn('users', 'password_set_at')) {
                $table->timestamp('password_set_at')->nullable()->after('must_reset_password');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('leads') && DB::getDriverName() === 'mysql') {
            // Best-effort rollback (drops new enum values). Any rows using new values must be cleaned manually.
            DB::statement("ALTER TABLE `leads` MODIFY `intent` ENUM('buyer','seller') NOT NULL");
            DB::statement("ALTER TABLE `leads` MODIFY `status` ENUM('new','qualified','assigned','contacted','closed') NOT NULL DEFAULT 'new'");
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'must_reset_password')) {
                $table->dropColumn('must_reset_password');
            }
            if (Schema::hasColumn('users', 'password_set_at')) {
                $table->dropColumn('password_set_at');
            }
        });
    }
};

