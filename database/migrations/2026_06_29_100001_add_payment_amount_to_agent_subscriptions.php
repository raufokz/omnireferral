<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_subscriptions', function (Blueprint $table) {
            $table->string('payment_amount', 50)->nullable()->after('payment_reference');
            $table->string('ghl_contact_id')->nullable()->after('payment_amount');
        });
    }

    public function down(): void
    {
        Schema::table('agent_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['payment_amount', 'ghl_contact_id']);
        });
    }
};
