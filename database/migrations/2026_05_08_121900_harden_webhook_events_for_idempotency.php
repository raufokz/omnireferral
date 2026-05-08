<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webhook_events', function (Blueprint $table) {
            if (! Schema::hasColumn('webhook_events', 'payload_hash')) {
                $table->string('payload_hash', 64)->nullable()->after('remote_id')->index();
            }
        });

        Schema::table('webhook_events', function (Blueprint $table) {
            // Enforce uniqueness where remote_id is present (Stripe event id, etc).
            // MySQL allows multiple NULLs in unique keys, so this remains backward-compatible for rows without remote_id.
            $table->unique(['provider', 'event', 'remote_id'], 'webhook_events_provider_event_remote_unique');

            // Additional safety net for providers without stable ids: hash of raw payload per provider.
            $table->unique(['provider', 'payload_hash'], 'webhook_events_provider_payload_hash_unique');
        });
    }

    public function down(): void
    {
        Schema::table('webhook_events', function (Blueprint $table) {
            $table->dropUnique('webhook_events_provider_event_remote_unique');
            $table->dropUnique('webhook_events_provider_payload_hash_unique');
        });

        Schema::table('webhook_events', function (Blueprint $table) {
            if (Schema::hasColumn('webhook_events', 'payload_hash')) {
                $table->dropColumn('payload_hash');
            }
        });
    }
};

