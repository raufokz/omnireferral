<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('city')->nullable()->after('zip_code');
        });

        Schema::table('lead_assignments', function (Blueprint $table) {
            $table->timestamp('assigned_at')->nullable()->after('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('city');
        });

        Schema::table('lead_assignments', function (Blueprint $table) {
            $table->dropColumn('assigned_at');
        });
    }
};

