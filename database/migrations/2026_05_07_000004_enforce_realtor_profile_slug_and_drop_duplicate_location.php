<?php

use App\Models\RealtorProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('realtor_profiles')) {
            return;
        }

        // Ensure every row has a unique slug before adding a unique index.
        $existing = [];
        RealtorProfile::query()
            ->select(['id', 'slug', 'user_id'])
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$existing) {
                foreach ($rows as $row) {
                    $slug = trim((string) ($row->slug ?? ''));
                    if ($slug === '' || isset($existing[$slug])) {
                        $base = $slug !== '' ? $slug : 'agent';
                        for ($i = 0; $i < 10; $i++) {
                            $candidate = Str::slug($base . '-' . Str::lower(Str::random(6)));
                            if (! isset($existing[$candidate]) && ! RealtorProfile::query()->where('slug', $candidate)->exists()) {
                                $slug = $candidate;
                                break;
                            }
                        }
                        if ($slug === '' || isset($existing[$slug])) {
                            $slug = Str::slug('agent-' . Str::lower(Str::random(10)));
                        }

                        RealtorProfile::query()->whereKey($row->id)->update(['slug' => $slug]);
                    }

                    $existing[$slug] = true;
                }
            });

        Schema::table('realtor_profiles', function (Blueprint $table) {
            // Enforce unique slug (public directory route key), but only if not already present.
            // Some older schema versions already have an auto-named unique index on `slug`.
            $driver = DB::getDriverName();
            $hasSlugUnique = false;

            try {
                if ($driver === 'sqlite') {
                    $indexes = DB::select("PRAGMA index_list('realtor_profiles')");
                    foreach ($indexes as $index) {
                        $name = $index->name ?? null;
                        if (! $name) {
                            continue;
                        }

                        $cols = DB::select("PRAGMA index_info('{$name}')");
                        $colNames = collect($cols)->map(fn ($c) => $c->name ?? null)->filter()->values();
                        if ($colNames->count() === 1 && $colNames->first() === 'slug' && ((int) ($index->unique ?? 0) === 1)) {
                            $hasSlugUnique = true;
                            break;
                        }
                    }
                } else {
                    $dbName = DB::getDatabaseName();
                    $rows = DB::select(
                        "SELECT INDEX_NAME, NON_UNIQUE, COLUMN_NAME
                         FROM information_schema.statistics
                         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'realtor_profiles' AND COLUMN_NAME = 'slug'",
                        [$dbName]
                    );

                    $byIndex = collect($rows)->groupBy('INDEX_NAME');
                    foreach ($byIndex as $indexName => $group) {
                        if ($group->count() === 1 && (int) ($group->first()->NON_UNIQUE ?? 1) === 0) {
                            $hasSlugUnique = true;
                            break;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $hasSlugUnique = false;
            }

            if (! $hasSlugUnique) {
                $table->unique('slug', 'realtor_profiles_slug_unique');
            }

            // Drop duplicated account location fields (users is source of truth).
            foreach (['address_line_1', 'address_line_2', 'city', 'state', 'zip_code'] as $column) {
                if (Schema::hasColumn('realtor_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('realtor_profiles')) {
            return;
        }

        Schema::table('realtor_profiles', function (Blueprint $table) {
            $table->dropUnique('realtor_profiles_slug_unique');

            // Restore dropped columns (nullable; data may not be recoverable).
            if (! Schema::hasColumn('realtor_profiles', 'address_line_1')) {
                $table->string('address_line_1')->nullable();
            }
            if (! Schema::hasColumn('realtor_profiles', 'address_line_2')) {
                $table->string('address_line_2')->nullable();
            }
            if (! Schema::hasColumn('realtor_profiles', 'city')) {
                $table->string('city', 120)->nullable();
            }
            if (! Schema::hasColumn('realtor_profiles', 'state')) {
                $table->string('state', 2)->nullable();
            }
            if (! Schema::hasColumn('realtor_profiles', 'zip_code')) {
                $table->string('zip_code', 10)->nullable();
            }
        });
    }
};

