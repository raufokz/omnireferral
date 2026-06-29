<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_lead_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('month', 7);
            $table->unsignedSmallInteger('monthly_quota')->default(0);
            $table->unsignedSmallInteger('assigned_count')->default(0);
            $table->smallInteger('remaining_count')->default(0);
            $table->unsignedSmallInteger('overdue_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'month']);
            $table->index(['user_id', 'month', 'remaining_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_lead_quotas');
    }
};
