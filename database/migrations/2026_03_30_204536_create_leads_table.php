<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_number')->unique();
            $table->enum('intent', ['buyer', 'seller']);
            $table->enum('package_type', ['quick', 'power', 'prime'])->default('quick');
            $table->enum('status', ['new', 'qualified', 'assigned', 'contacted', 'closed'])->default('new');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('zip_code', 10);
            $table->string('property_type')->nullable();
            $table->unsignedInteger('budget')->nullable();
            $table->unsignedInteger('asking_price')->nullable();
            $table->text('preferences')->nullable();
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
