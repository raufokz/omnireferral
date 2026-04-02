<?php

namespace Database\Seeders;

use Database\Seeders\OmniReferralSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(OmniReferralSeeder::class);
    }
}
