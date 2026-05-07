<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('property_id')
                ->nullable()
                ->after('property_image')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->index(['property_id', 'assigned_agent_id']);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['property_id', 'assigned_agent_id']);
            $table->dropConstrainedForeignId('property_id');
        });
    }
};
