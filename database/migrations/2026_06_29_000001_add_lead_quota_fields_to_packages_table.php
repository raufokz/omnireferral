<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (! Schema::hasColumn('packages', 'monthly_lead_quota')) {
                $table->unsignedSmallInteger('monthly_lead_quota')->default(0)->after('sort_order');
            }
            if (! Schema::hasColumn('packages', 'lead_priority')) {
                $table->tinyInteger('lead_priority')->default(0)->after('monthly_lead_quota');
            }
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['monthly_lead_quota', 'lead_priority']);
        });
    }
};
