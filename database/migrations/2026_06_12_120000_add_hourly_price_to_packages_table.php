<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('packages', 'hourly_price')) {
            return;
        }

        Schema::table('packages', function (Blueprint $table) {
            $table->unsignedInteger('hourly_price')->nullable()->after('monthly_price');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('packages', 'hourly_price')) {
            return;
        }

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('hourly_price');
        });
    }
};
