<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('zip_code');
            $table->foreignId('recipient_user_id')->nullable()->after('source')->constrained('users')->nullOnDelete();
            $table->foreignId('realtor_profile_id')->nullable()->after('recipient_user_id')->constrained('realtor_profiles')->nullOnDelete();
            $table->foreignId('property_id')->nullable()->after('realtor_profile_id')->constrained('properties')->nullOnDelete();
            $table->string('message_status')->default('new')->after('property_id');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('property_id');
            $table->dropConstrainedForeignId('realtor_profile_id');
            $table->dropConstrainedForeignId('recipient_user_id');
            $table->dropColumn([
                'subject',
                'message_status',
            ]);
        });
    }
};
