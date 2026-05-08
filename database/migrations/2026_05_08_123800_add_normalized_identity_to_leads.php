<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'email_normalized')) {
                $table->string('email_normalized')->nullable()->after('email');
            }
            if (! Schema::hasColumn('leads', 'phone_normalized')) {
                $table->string('phone_normalized', 32)->nullable()->after('phone');
            }
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->index('email_normalized');
            $table->index('phone_normalized');
            $table->index(['email_normalized', 'phone_normalized'], 'leads_email_phone_normalized_index');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['email_normalized']);
            $table->dropIndex(['phone_normalized']);
            $table->dropIndex('leads_email_phone_normalized_index');
        });

        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'email_normalized')) {
                $table->dropColumn('email_normalized');
            }
            if (Schema::hasColumn('leads', 'phone_normalized')) {
                $table->dropColumn('phone_normalized');
            }
        });
    }
};

