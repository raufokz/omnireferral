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

    public function test_it_does_not_auto_assign_when_disabled(): void
    {
        config(['omnireferral.lead.auto_assignment_enabled' => false]);

        User::create([
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

        app(LeadRoutingService::class)->assignIfConfigured($lead);

        $lead->refresh();
        $this->assertSame('new', $lead->status);
        $this->assertNull($lead->assigned_agent_id);
    }

    public function test_it_keeps_existing_assignment_when_auto_routing_runs(): void
    {
        config(['omnireferral.lead.auto_assignment_enabled' => true]);

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
            'package_type' => 'quick',
            'status' => 'assigned',
            'name' => 'Sue Buyer',
            'email' => 'sue@test.com',
            'phone' => '555-0102',
            'zip_code' => '75000',
            'assigned_agent_id' => $agentUser->id,
            'source' => 'website',
        ]);

        app(LeadRoutingService::class)->assignIfConfigured($lead);

        $this->assertEquals($agentUser->id, $lead->fresh()->assigned_agent_id);
    }
}
