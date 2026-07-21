<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_subscription_id')->nullable()->constrained('agent_subscriptions')->nullOnDelete();
            $table->foreignId('from_package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->foreignId('to_package_id')->nullable()->constrained('packages')->nullOnDelete();
            // assigned | upgraded | downgraded | changed | cancelled | reactivated
            $table->string('action', 30);
            $table->string('performed_by', 30)->default('admin'); // admin | self-service | system | stripe | gohighlevel
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_history');
    }
};
