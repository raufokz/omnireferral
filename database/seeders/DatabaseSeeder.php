<?php

namespace Database\Seeders;

use Database\Seeders\OmniReferralSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed RBAC first so seeded users can be assigned roles/permissions later.
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(OmniReferralSeeder::class);
    }
}
