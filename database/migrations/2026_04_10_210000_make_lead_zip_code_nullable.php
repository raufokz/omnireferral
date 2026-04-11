<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('zip_code', 10)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('leads')->whereNull('zip_code')->update(['zip_code' => '00000']);

        Schema::table('leads', function (Blueprint $table) {
            $table->string('zip_code', 10)->nullable(false)->change();
        });
    }
};
