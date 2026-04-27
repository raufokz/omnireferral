<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('property_favorites_new')) {
            Schema::drop('property_favorites_new');
        }

        Schema::create('property_favorites_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('device_fingerprint', 64);
            $table->timestamps();
            $table->unique(['property_id', 'device_fingerprint']);
        });

        $source = DB::table('property_favorites')->orderBy('id')->get();
        foreach ($source as $row) {
            $device = null;
            if (isset($row->device_fingerprint) && $row->device_fingerprint !== null && $row->device_fingerprint !== '') {
                $device = (string) $row->device_fingerprint;
            }
            if ($device === null || $device === '') {
                $device = 'legacy-user-' . str_pad((string) $row->user_id, 12, '0', STR_PAD_LEFT);
            }
            DB::table('property_favorites_new')->insert([
                'id' => $row->id,
                'property_id' => $row->property_id,
                'user_id' => $row->user_id,
                'device_fingerprint' => $device,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        $maxId = (int) DB::table('property_favorites_new')->max('id');
        if ($maxId > 0 && Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE property_favorites_new AUTO_INCREMENT = ' . ($maxId + 1));
        }

        Schema::drop('property_favorites');
        Schema::rename('property_favorites_new', 'property_favorites');

        if (! Schema::hasTable('property_comments')) {
            Schema::create('property_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('author_name', 120)->nullable();
                $table->text('body');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('property_comments');

        if (! Schema::hasTable('property_favorites')) {
            return;
        }

        if (Schema::hasTable('property_favorites_legacy')) {
            Schema::drop('property_favorites_legacy');
        }

        Schema::create('property_favorites_legacy', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['property_id', 'user_id']);
        });

        foreach (DB::table('property_favorites')->whereNotNull('user_id')->orderBy('id')->get() as $row) {
            DB::table('property_favorites_legacy')->insert([
                'id' => $row->id,
                'property_id' => $row->property_id,
                'user_id' => $row->user_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        $maxId = (int) DB::table('property_favorites_legacy')->max('id');
        if ($maxId > 0 && Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE property_favorites_legacy AUTO_INCREMENT = ' . ($maxId + 1));
        }

        Schema::drop('property_favorites');
        Schema::rename('property_favorites_legacy', 'property_favorites');
    }
};
