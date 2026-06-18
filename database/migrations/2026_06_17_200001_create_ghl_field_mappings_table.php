<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ghl_field_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('ghl_field', 100);
            $table->string('db_table', 50);
            $table->string('db_column', 100);
            $table->string('label', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['ghl_field', 'db_table', 'db_column']);
            $table->index(['db_table', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ghl_field_mappings');
    }
};
