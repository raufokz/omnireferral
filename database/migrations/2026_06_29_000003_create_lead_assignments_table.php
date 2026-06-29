<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('assignment_month', 7);
            $table->string('assignment_status', 30)->default('assigned');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('response_from_realtor')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['assigned_to_user_id', 'assignment_month']);
            $table->index(['lead_id', 'assignment_status']);
            $table->index('assignment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_assignments');
    }
};
