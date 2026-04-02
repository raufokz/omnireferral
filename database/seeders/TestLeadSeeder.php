<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestLeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test agent user if it doesn't exist
        $agent = User::where('email', 'agent@test.com')->first();
        if (! $agent) {
            $agent = User::factory()->create([
                'name' => 'Test Agent',
                'email' => 'agent@test.com',
                'role' => 'agent',
            ]);
        }

        // Create a stale test lead (created 5 days ago, not yet contacted)
        Lead::factory()->create([
            'name' => 'John Prospect',
            'email' => 'john@prospect.com',
            'phone' => '555-0001',
            'intent' => 'buy',
            'status' => 'new',
            'zip_code' => '90210',
            'property_type' => 'single_family',
            'timeline' => '1_month',
            'assigned_agent_id' => $agent->id,
            'contacted_at' => null,
            'created_at' => now()->subDays(5),
        ]);

        $this->command->info('Test lead seeded successfully');
    }
}
