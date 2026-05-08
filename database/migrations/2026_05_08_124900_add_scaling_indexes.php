<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Leads: admin/staff filters and routing dashboards.
        Schema::table('leads', function (Blueprint $table) {
            // Use distinct index names to avoid collisions with earlier migrations / test databases.
            $table->index(['status', 'created_at'], 'leads_status_created_at_scaling_idx');
            $table->index(['assigned_agent_id', 'status'], 'leads_assigned_status_scaling_idx');
            $table->index(['source', 'created_at'], 'leads_source_created_at_scaling_idx');
            $table->index('intent', 'leads_intent_scaling_idx');
            $table->index('rep_name', 'leads_rep_name_scaling_idx');
        });

        // Contacts: agent inbox + status management.
        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->index(['recipient_user_id', 'message_status', 'created_at'], 'contacts_recipient_status_created_index');
                $table->index(['property_id', 'created_at'], 'contacts_property_created_index');
                $table->index(['realtor_profile_id', 'created_at'], 'contacts_realtor_profile_created_index');
            });
        }

        // Webhook events: operational queries (unprocessed queue).
        Schema::table('webhook_events', function (Blueprint $table) {
            $table->index(['provider', 'processed_at'], 'webhook_events_provider_processed_scaling_idx');
            $table->index(['provider', 'event', 'created_at'], 'webhook_events_provider_event_created_scaling_idx');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_status_created_at_scaling_idx');
            $table->dropIndex('leads_assigned_status_scaling_idx');
            $table->dropIndex('leads_source_created_at_scaling_idx');
            $table->dropIndex('leads_intent_scaling_idx');
            $table->dropIndex('leads_rep_name_scaling_idx');
        });

        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->dropIndex('contacts_recipient_status_created_index');
                $table->dropIndex('contacts_property_created_index');
                $table->dropIndex('contacts_realtor_profile_created_index');
            });
        }

        Schema::table('webhook_events', function (Blueprint $table) {
            $table->dropIndex('webhook_events_provider_processed_scaling_idx');
            $table->dropIndex('webhook_events_provider_event_created_scaling_idx');
        });
    }
};

