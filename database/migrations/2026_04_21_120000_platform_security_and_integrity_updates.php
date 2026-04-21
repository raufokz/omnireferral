<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('owner_user_id')
                ->nullable()
                ->after('realtor_profile_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::create('affiliate_referral_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('referral_code', 32);
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['affiliate_profile_id', 'created_at']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->index('status');
            $table->index('intent');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->index('approval_status');
            $table->index('status');
        });

        DB::table('users')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                $password = (string) ($row->password ?? '');
                if ($password === '') {
                    continue;
                }

                $algo = password_get_info($password)['algoName'] ?? 'unknown';
                if ($algo !== 'unknown') {
                    continue;
                }

                DB::table('users')->where('id', $row->id)->update([
                    'password' => Hash::make($password),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_referral_clicks');

        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['approval_status']);
            $table->dropIndex(['status']);
            $table->dropConstrainedForeignId('owner_user_id');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['intent']);
        });
    }
};
