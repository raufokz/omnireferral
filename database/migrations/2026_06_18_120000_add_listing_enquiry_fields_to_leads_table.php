<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'type')) {
                $table->string('type')->default('buyer')->after('lead_number');
            }
            if (!Schema::hasColumn('leads', 'listing_id')) {
                $table->foreignId('listing_id')->nullable()->constrained('properties')->nullOnDelete();
            }
            if (!Schema::hasColumn('leads', 'assigned_agent_id')) {
                $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('leads', 'enquiry_type')) {
                $table->string('enquiry_type')->nullable();
            }
            if (!Schema::hasColumn('leads', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('leads', 'budget_range')) {
                $table->string('budget_range')->nullable();
            }
            if (!Schema::hasColumn('leads', 'timeline')) {
                $table->string('timeline')->nullable();
            }
            if (!Schema::hasColumn('leads', 'property_address')) {
                $table->string('property_address')->nullable();
            }
            if (!Schema::hasColumn('leads', 'estimated_value')) {
                $table->string('estimated_value')->nullable();
            }
            if (!Schema::hasColumn('leads', 'area_of_interest')) {
                $table->string('area_of_interest')->nullable();
            }
            if (!Schema::hasColumn('leads', 'source')) {
                $table->string('source')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'listing_id',
                'assigned_agent_id',
                'enquiry_type',
                'phone',
                'budget_range',
                'timeline',
                'property_address',
                'estimated_value',
                'area_of_interest',
                'source',
            ]);
        });
    }
};
