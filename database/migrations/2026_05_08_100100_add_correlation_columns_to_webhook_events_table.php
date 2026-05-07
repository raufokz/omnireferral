<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webhook_events', function (Blueprint $table) {
            $table->string('related_type', 120)->nullable()->after('provider')->index();
            $table->unsignedBigInteger('related_id')->nullable()->after('related_type')->index();
            $table->index(['related_type', 'related_id'], 'webhook_events_related_morph_index');
        });
    }

    public function down(): void
    {
        Schema::table('webhook_events', function (Blueprint $table) {
            $table->dropIndex('webhook_events_related_morph_index');
            $table->dropColumn(['related_type', 'related_id']);
        });
    }
};
