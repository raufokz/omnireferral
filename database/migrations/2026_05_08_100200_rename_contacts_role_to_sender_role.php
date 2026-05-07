<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        if (Schema::hasColumn('contacts', 'role') && ! Schema::hasColumn('contacts', 'sender_role')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->renameColumn('role', 'sender_role');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        if (Schema::hasColumn('contacts', 'sender_role') && ! Schema::hasColumn('contacts', 'role')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->renameColumn('sender_role', 'role');
            });
        }
    }
};
