<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\RealtorProfile;
use App\Models\User;
use App\Services\LeadRoutingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeadRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_routes_to_agent_in_matching_zip_code(): void
    {
        // 1. Setup active Agent User
        $agentUser = User::create([
            'name' => 'Agent Test',
            'email' => 'agent@test.com',
            'password' => bcrypt('password'),
            'role' => 'agent',
            'status' => 'active',
        ]);

        RealtorProfile::create([
            'user_id' => $agentUser->id,
            'slug' => Str::slug($agentUser->name),
            'brokerage_name' => 'Test Brokerage',
            'zip_code' => '75201',
            'city' => 'Dallas',
            'state' => 'TX',
        ]);

        // 2. Setup Lead in same ZIP
        $lead = Lead::create([
            'lead_number' => 'LD-' . Str::random(6),
            'intent' => 'buyer',
            'package_type' => 'quick',
            'status' => 'new',
            'name' => 'John Buyer',
            'email' => 'buyer@test.com',
            'phone' => '555-0101',
            'zip_code' => '75201',
            'source' => 'website',
        ]);

        // 3. Execute Routing
        $service = new LeadRoutingService();
        $assignedUser = $service->routeLead($lead);

        // 4. Assertions
        $this->assertNotNull($assignedUser);
        $this->assertEquals($agentUser->id, $assignedUser->id);

        $lead->refresh();
        $this->assertEquals('assigned', $lead->status);
        $this->assertEquals($agentUser->id, $lead->assigned_agent_id);
    }

    public function test_it_does_not_route_already_assigned_lead(): void
    {
        $agentUser = User::create([
            'name' => 'Agent Test 2',
            'email' => 'agent2@test.com',
            'password' => bcrypt('password'),
            'role' => 'agent',
            'status' => 'active',
        ]);

        $lead = Lead::create([
            'lead_number' => 'LD-' . Str::random(6),
            'intent' => 'buyer',
            'status' => 'assigned',
            'name' => 'Sue Buyer',
            'email' => 'sue@test.com',
            'phone' => '555-0102',
            'zip_code' => '75000',
            'assigned_agent_id' => $agentUser->id,
        ]);

        $service = new LeadRoutingService();
        $routedUser = $service->routeLead($lead);

        $this->assertEquals($agentUser->id, $routedUser->id);
    }
}
