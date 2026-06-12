<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Unique slug constraint already enforced by earlier migration(s).
        // Keep this migration as a no-op to avoid duplicate key name errors.
    }

    public function down(): void
    {
        // no-op
    }
};

