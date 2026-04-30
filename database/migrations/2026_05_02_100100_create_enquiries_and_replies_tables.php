<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('sender_phone')->nullable();
            $table->foreignId('receiver_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['receiver_user_id', 'status']);
            $table->index(['sender_user_id', 'created_at']);
        });

        Schema::create('enquiry_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained('enquiries')->cascadeOnDelete();
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_display')->nullable();
            $table->text('message');
            $table->timestamps();

            $table->index(['enquiry_id', 'created_at']);
        });

        if (Schema::hasTable('contacts')) {
            DB::table('contacts')
                ->whereNotNull('property_id')
                ->whereNotNull('recipient_user_id')
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $c) {
                        $exists = DB::table('enquiries')->where('contact_id', $c->id)->exists();
                        if ($exists) {
                            continue;
                        }

                        $receiverId = (int) $c->recipient_user_id;
                        $ownerId = DB::table('properties')->where('id', $c->property_id)->value('owner_user_id');
                        if ($ownerId) {
                            $receiverId = (int) $ownerId;
                        }

                        DB::table('enquiries')->insert([
                            'property_id' => $c->property_id,
                            'contact_id' => $c->id,
                            'sender_user_id' => null,
                            'sender_name' => $c->name,
                            'sender_email' => $c->email,
                            'sender_phone' => $c->phone,
                            'receiver_user_id' => $receiverId,
                            'subject' => $c->subject,
                            'message' => $c->message,
                            'status' => match ((string) ($c->message_status ?? 'new')) {
                                'closed' => 'closed',
                                default => 'pending',
                            },
                            'created_at' => $c->created_at ?? now(),
                            'updated_at' => $c->updated_at ?? now(),
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiry_replies');
        Schema::dropIfExists('enquiries');
    }
};
