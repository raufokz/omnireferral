<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('submitted_by_email')->nullable()->after('location');
            $table->foreignId('submitted_by_user_id')->nullable()->after('submitted_by_email')->constrained('users')->nullOnDelete();
            $table->string('submission_status')->default('approved')->after('sort_order');
            $table->foreignId('reviewed_by_user_id')->nullable()->after('submission_status')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by_user_id');
        });

        DB::table('testimonials')
            ->whereNull('submission_status')
            ->update([
                'submission_status' => 'approved',
                'reviewed_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('submitted_by_user_id');
            $table->dropConstrainedForeignId('reviewed_by_user_id');
            $table->dropColumn([
                'submitted_by_email',
                'submission_status',
                'reviewed_at',
            ]);
        });
    }
};
