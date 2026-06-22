<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mail_settings')) {
            return;
        }

        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mailer', 20)->default('smtp');
            $table->string('host', 255)->nullable();
            $table->integer('port')->nullable();
            $table->string('encryption', 20)->nullable();
            $table->string('username', 255)->nullable();
            $table->text('password')->nullable();
            $table->string('from_address', 255)->nullable();
            $table->string('from_name', 255)->nullable();
            $table->string('credentials_from_address', 255)->nullable();
            $table->string('credentials_from_name', 255)->nullable();
            $table->string('connection_status', 20)->default('unknown');
            $table->timestamp('last_tested_at')->nullable();
            $table->foreignId('last_tested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
    }
};
