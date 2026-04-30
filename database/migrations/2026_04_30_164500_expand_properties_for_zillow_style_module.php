<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('street_address')->nullable()->after('location');
            $table->string('city')->nullable()->after('street_address');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->nullable()->after('state');
            $table->string('price_type', 20)->default('sale')->after('price');
            $table->decimal('area_size', 12, 2)->nullable()->after('sqft');
            $table->string('area_unit', 20)->default('sqft')->after('area_size');
            $table->unsignedSmallInteger('year_built')->nullable()->after('area_unit');
            $table->unsignedTinyInteger('parking_spaces')->nullable()->after('year_built');
            $table->unsignedTinyInteger('garage_spaces')->nullable()->after('parking_spaces');
            $table->string('furnishing_status', 30)->nullable()->after('garage_spaces');
            $table->string('property_condition', 30)->nullable()->after('furnishing_status');
            $table->string('video_tour_url')->nullable()->after('images');
            $table->string('view_360_url')->nullable()->after('video_tour_url');
            $table->json('amenities')->nullable()->after('view_360_url');
            $table->text('neighborhood_info')->nullable()->after('amenities');
            $table->unsignedTinyInteger('walk_score')->nullable()->after('neighborhood_info');
            $table->text('location_highlights')->nullable()->after('walk_score');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'street_address',
                'city',
                'state',
                'country',
                'price_type',
                'area_size',
                'area_unit',
                'year_built',
                'parking_spaces',
                'garage_spaces',
                'furnishing_status',
                'property_condition',
                'video_tour_url',
                'view_360_url',
                'amenities',
                'neighborhood_info',
                'walk_score',
                'location_highlights',
            ]);
        });
    }
};
