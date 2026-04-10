<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use App\Services\LeadRoutingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeadRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_does_not_auto_assign_matching_zip_lead(): void
    {
        $agentUser = User::create([
            'name' => 'Agent Test',
            'email' => 'agent@test.com',
            'password' => bcrypt('password'),
            'role' => 'agent',
            'status' => 'active',
        ]);

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

        $service = new LeadRoutingService();
        $assignedUser = $service->routeLead($lead);

        $this->assertNull($assignedUser);

        $lead->refresh();
        $this->assertSame('new', $lead->status);
        $this->assertNull($lead->assigned_agent_id);
    }

    public function test_it_returns_existing_assigned_agent_without_reassigning(): void
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
