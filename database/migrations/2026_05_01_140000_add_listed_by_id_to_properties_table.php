<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('listed_by_id')
                ->nullable()
                ->after('owner_user_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        foreach (DB::table('properties')
            ->whereNotNull('realtor_profile_id')
            ->whereNull('listed_by_id')
            ->get(['id', 'realtor_profile_id']) as $row) {
            $userId = DB::table('realtor_profiles')
                ->where('id', $row->realtor_profile_id)
                ->value('user_id');
            if ($userId) {
                DB::table('properties')->where('id', $row->id)->update(['listed_by_id' => $userId]);
            }
        }

        DB::table('properties')
            ->whereNull('listed_by_id')
            ->whereNotNull('owner_user_id')
            ->update(['listed_by_id' => DB::raw('owner_user_id')]);
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('listed_by_id');
        });
    }
};
