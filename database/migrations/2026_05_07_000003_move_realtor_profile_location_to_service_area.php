<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('realtor_profiles')) {
            return;
        }

        Schema::table('realtor_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('realtor_profiles', 'service_city')) {
                $table->string('service_city', 120)->nullable()->after('slug');
            }
            if (! Schema::hasColumn('realtor_profiles', 'service_state')) {
                $table->string('service_state', 2)->nullable()->after('service_city');
            }
            if (! Schema::hasColumn('realtor_profiles', 'service_zip_code')) {
                $table->string('service_zip_code', 10)->nullable()->after('service_state');
            }
        });

        // Backfill service area from existing realtor_profiles city/state/zip_code where possible.
        if (
            Schema::hasColumn('realtor_profiles', 'city')
            && Schema::hasColumn('realtor_profiles', 'state')
            && Schema::hasColumn('realtor_profiles', 'zip_code')
        ) {
            DB::table('realtor_profiles')->whereNull('service_city')->update([
                'service_city' => DB::raw('city'),
            ]);
            DB::table('realtor_profiles')->whereNull('service_state')->update([
                'service_state' => DB::raw('state'),
            ]);
            DB::table('realtor_profiles')->whereNull('service_zip_code')->update([
                'service_zip_code' => DB::raw('zip_code'),
            ]);
        }

        // Backfill any missing service area from users table (users is source of truth).
        if (Schema::hasTable('users')) {
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                // SQLite doesn't support UPDATE .. JOIN; do it in PHP.
                DB::table('realtor_profiles')
                    ->select(['id', 'user_id', 'service_city', 'service_state', 'service_zip_code'])
                    ->orderBy('id')
                    ->chunkById(200, function ($rows) {
                        foreach ($rows as $row) {
                            $user = DB::table('users')
                                ->where('id', $row->user_id)
                                ->first(['city', 'state', 'zip_code']);

                            if (! $user) {
                                continue;
                            }

                            DB::table('realtor_profiles')->where('id', $row->id)->update([
                                'service_city' => $row->service_city ?? $user->city,
                                'service_state' => $row->service_state ?? $user->state,
                                'service_zip_code' => $row->service_zip_code ?? $user->zip_code,
                            ]);
                        }
                    });
            } else {
                DB::statement("
                    UPDATE realtor_profiles rp
                    JOIN users u ON u.id = rp.user_id
                    SET
                        rp.service_city = COALESCE(rp.service_city, u.city),
                        rp.service_state = COALESCE(rp.service_state, u.state),
                        rp.service_zip_code = COALESCE(rp.service_zip_code, u.zip_code)
                ");
            }
        }

        // Normalize obvious bad values (best-effort).
        DB::table('realtor_profiles')
            ->whereNotNull('service_state')
            ->whereRaw('LENGTH(service_state) != 2')
            ->update(['service_state' => null]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('realtor_profiles')) {
            return;
        }

        Schema::table('realtor_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('realtor_profiles', 'service_zip_code')) {
                $table->dropColumn('service_zip_code');
            }
            if (Schema::hasColumn('realtor_profiles', 'service_state')) {
                $table->dropColumn('service_state');
            }
            if (Schema::hasColumn('realtor_profiles', 'service_city')) {
                $table->dropColumn('service_city');
            }
        });
    }
};

