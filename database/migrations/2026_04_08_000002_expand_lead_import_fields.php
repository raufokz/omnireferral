<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `leads` MODIFY `intent` ENUM('buyer','seller','investor','other') NOT NULL");
        }

        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('source_timestamp')->nullable()->after('source');
            $table->text('property_address')->nullable()->after('zip_code');
            $table->string('beds_baths')->nullable()->after('property_address');
            $table->boolean('working_with_realtor')->nullable()->after('beds_baths');
            $table->text('dnc_disclaimer')->nullable()->after('working_with_realtor');
            $table->text('notes')->nullable()->after('preferences');
            $table->string('rep_name')->nullable()->after('notes');
            $table->string('state')->nullable()->after('rep_name');
            $table->string('sent_to')->nullable()->after('state');
            $table->string('assignment')->nullable()->after('sent_to');
            $table->text('reason_in_house')->nullable()->after('assignment');
            $table->text('realtor_response')->nullable()->after('reason_in_house');

            $table->index('email');
            $table->index('phone');
            $table->index('source_timestamp');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('leads') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `leads` MODIFY `intent` ENUM('buyer','seller','investor') NOT NULL");
        }

        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['source_timestamp']);
            $table->dropColumn([
                'source_timestamp',
                'property_address',
                'beds_baths',
                'working_with_realtor',
                'dnc_disclaimer',
                'notes',
                'rep_name',
                'state',
                'sent_to',
                'assignment',
                'reason_in_house',
                'realtor_response',
            ]);
        });
    }
};
