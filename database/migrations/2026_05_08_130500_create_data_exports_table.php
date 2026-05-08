<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 60);   // users | enquiries | leads
            $table->string('format', 10); // csv | xlsx
            $table->json('filters')->nullable();
            $table->string('status', 20)->default('pending'); // pending | running | complete | failed
            $table->string('file_path')->nullable();
            $table->string('content_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('finished_at')->nullable()->index();
            $table->timestamps();

            $table->index(['type', 'status', 'created_at']);
            $table->index(['requested_by_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_exports');
    }
};

