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
            if (! Schema::hasColumn('realtor_profiles', 'years_of_experience')) {
                $table->unsignedTinyInteger('years_of_experience')->nullable()->after('license_number');
            }
            if (! Schema::hasColumn('realtor_profiles', 'languages')) {
                $table->string('languages', 255)->nullable()->after('years_of_experience');
            }
            if (! Schema::hasColumn('realtor_profiles', 'market_areas')) {
                $table->text('market_areas')->nullable()->after('languages');
            }
            if (! Schema::hasColumn('realtor_profiles', 'social_links')) {
                $table->json('social_links')->nullable()->after('market_areas');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('realtor_profiles')) {
            return;
        }

        Schema::table('realtor_profiles', function (Blueprint $table) {
            foreach (['social_links', 'market_areas', 'languages', 'years_of_experience'] as $column) {
                if (Schema::hasColumn('realtor_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
