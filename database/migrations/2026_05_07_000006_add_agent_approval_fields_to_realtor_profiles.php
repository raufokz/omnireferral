<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('realtor_profiles')) {
            return;
        }

        Schema::table('realtor_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('realtor_profiles', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('headshot')->index();
            }
            if (! Schema::hasColumn('realtor_profiles', 'approved_by_user_id')) {
                $table->foreignId('approved_by_user_id')
                    ->nullable()
                    ->after('approved_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('realtor_profiles', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_by_user_id')->index();
            }
            if (! Schema::hasColumn('realtor_profiles', 'rejected_by_user_id')) {
                $table->foreignId('rejected_by_user_id')
                    ->nullable()
                    ->after('rejected_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('realtor_profiles', 'approval_notes')) {
                $table->string('approval_notes', 1000)->nullable()->after('rejected_by_user_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('realtor_profiles')) {
            return;
        }

        Schema::table('realtor_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('realtor_profiles', 'approval_notes')) {
                $table->dropColumn('approval_notes');
            }
            if (Schema::hasColumn('realtor_profiles', 'rejected_by_user_id')) {
                $table->dropConstrainedForeignId('rejected_by_user_id');
            }
            if (Schema::hasColumn('realtor_profiles', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
            if (Schema::hasColumn('realtor_profiles', 'approved_by_user_id')) {
                $table->dropConstrainedForeignId('approved_by_user_id');
            }
            if (Schema::hasColumn('realtor_profiles', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
        });
    }
};

