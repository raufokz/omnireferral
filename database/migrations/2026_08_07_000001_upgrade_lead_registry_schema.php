<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Budget must accept free text ("300K", "$350,000", "Above 1M", "Luxury", etc.),
        // not just plain integers. MySQL-only ALTER (no doctrine/dbal in this project —
        // see 2026_04_08_000001_expand_lead_statuses_and_user_security_flags.php for the
        // established pattern). SQLite (used in tests) has dynamic type affinity and
        // already accepts text in an integer-affinity column, so no action is needed there.
        if (Schema::hasTable('leads') && DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `leads` MODIFY `budget` VARCHAR(100) NULL');

            // Purely additive status expansion — no existing values removed.
            DB::statement("ALTER TABLE `leads` MODIFY `status` ENUM('new','contacted','in_progress','qualified','assigned','closed','not_interested','appointment_scheduled','lost','duplicate','spam') NOT NULL DEFAULT 'new'");
        }

        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'credit_score')) {
                $table->string('credit_score', 50)->nullable()->after('financing_status');
            }
            if (! Schema::hasColumn('leads', 'dop')) {
                $table->date('dop')->nullable()->after('budget');
            }
        });

        Schema::table('lead_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('lead_assignments', 'previous_agent_id')) {
                $table->foreignId('previous_agent_id')->nullable()->after('assigned_to_user_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('lead_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('lead_assignments', 'previous_agent_id')) {
                $table->dropConstrainedForeignId('previous_agent_id');
            }
        });

        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'dop')) {
                $table->dropColumn('dop');
            }
            if (Schema::hasColumn('leads', 'credit_score')) {
                $table->dropColumn('credit_score');
            }
        });

        if (Schema::hasTable('leads') && DB::getDriverName() === 'mysql') {
            // Best-effort rollback. Any rows using the new status values must be cleaned
            // up manually before this will succeed, matching the existing precedent.
            DB::statement("ALTER TABLE `leads` MODIFY `status` ENUM('new','contacted','in_progress','qualified','assigned','closed','not_interested') NOT NULL DEFAULT 'new'");
            DB::statement('ALTER TABLE `leads` MODIFY `budget` INT UNSIGNED NULL');
        }
    }
};
