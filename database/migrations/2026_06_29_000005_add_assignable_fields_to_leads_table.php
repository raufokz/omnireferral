<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'is_assignable')) {
                $table->boolean('is_assignable')->default(true)->after('assigned_at');
            }
            if (! Schema::hasColumn('leads', 'row_color')) {
                $table->string('row_color', 30)->nullable()->after('is_assignable');
            }
            if (! Schema::hasColumn('leads', 'lead_quality_score')) {
                $table->tinyInteger('lead_quality_score')->nullable()->after('row_color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['is_assignable', 'row_color', 'lead_quality_score']);
        });
    }
};
