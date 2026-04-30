<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('name');
            $table->string('social_facebook_url')->nullable()->after('zip_code');
            $table->string('social_linkedin_url')->nullable()->after('social_facebook_url');
            $table->boolean('notify_email')->default(true)->after('social_linkedin_url');
            $table->boolean('notify_marketing')->default(false)->after('notify_email');
            $table->boolean('two_factor_enabled')->default(false)->after('notify_marketing');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'display_name',
                'social_facebook_url',
                'social_linkedin_url',
                'notify_email',
                'notify_marketing',
                'two_factor_enabled',
            ]);
        });
    }
};
