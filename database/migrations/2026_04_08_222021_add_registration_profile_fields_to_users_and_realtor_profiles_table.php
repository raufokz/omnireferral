<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address_line_1')->nullable()->after('phone');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->string('city', 100)->nullable()->after('address_line_2');
            $table->string('state', 2)->nullable()->after('city');
            $table->string('zip_code', 10)->nullable()->after('state');
        });

        Schema::table('realtor_profiles', function (Blueprint $table) {
            $table->string('license_number')->nullable()->after('brokerage_name');
            $table->string('address_line_1')->nullable()->after('license_number');
            $table->string('address_line_2')->nullable()->after('address_line_1');
        });
    }

    public function down(): void
    {
        Schema::table('realtor_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'license_number',
                'address_line_1',
                'address_line_2',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address_line_1',
                'address_line_2',
                'city',
                'state',
                'zip_code',
            ]);
        });
    }
};
