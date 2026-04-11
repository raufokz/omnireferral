<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('approval_status')->default('approved')->after('status');
            $table->text('approval_notes')->nullable()->after('approval_status');
            $table->foreignId('reviewed_by_user_id')->nullable()->after('approval_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by_user_id');
        });

        DB::table('properties')
            ->whereNull('reviewed_at')
            ->update([
                'reviewed_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by_user_id');
            $table->dropColumn([
                'approval_status',
                'approval_notes',
                'reviewed_at',
            ]);
        });
    }
};
