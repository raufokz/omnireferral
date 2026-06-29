<?php

namespace Tests\Feature;

use App\Models\AgentLeadQuota;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\Package;
use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LeadAssignmentQuotaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $agent;
    private Package $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->package = Package::factory()->create([
            'is_active' => true,
            'monthly_lead_quota' => 5,
            'lead_priority' => 1,
        ]);

        $this->agent = User::withoutEvents(function () {
            return User::factory()->create([
                'role' => 'agent',
                'status' => 'active',
                'current_plan_id' => $this->package->id,
                'onboarding_completed_at' => now(),
            ]);
        });

        RealtorProfile::factory()->create([
            'user_id' => $this->agent->id,
        ]);

        $this->agent->agentSubscription()->create([
            'package_id' => $this->package->id,
            'payment_status' => 'paid',
            'is_active' => true,
            'starts_at' => now(),
        ]);
    }

    public function test_admin_can_view_assignments_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.lead-assignments.index'))
            ->assertOk();
    }

    public function test_admin_can_view_create_form(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.lead-assignments.create'))
            ->assertOk();
    }

    public function test_admin_can_assign_lead_to_agent(): void
    {
        Notification::fake();

        $lead = Lead::factory()->create([
            'is_assignable' => true,
            'assigned_agent_id' => null,
            'status' => 'new',
        ]);

        $this->actingAs($this->admin)->post(route('admin.lead-assignments.store'), [
            'lead_id' => $lead->id,
            'agent_id' => $this->agent->id,
        ])->assertRedirect();

        $lead->refresh();
        $this->assertEquals($this->agent->id, $lead->assigned_agent_id);
        $this->assertEquals('assigned', $lead->status);

        $assignment = LeadAssignment::first();
        $this->assertNotNull($assignment);
        $this->assertEquals($lead->id, $assignment->lead_id);
        $this->assertEquals($this->agent->id, $assignment->assigned_to_user_id);
        $this->assertEquals(now()->format('Y-m'), $assignment->assignment_month);

        $quota = AgentLeadQuota::first();
        $this->assertNotNull($quota);
        $this->assertEquals(1, $quota->assigned_count);
        $this->assertEquals(4, $quota->remaining_count);
    }

    public function test_auto_assign_prioritizes_highest_priority_package(): void
    {
        $growthPackage = Package::factory()->create([
            'is_active' => true,
            'monthly_lead_quota' => 15,
            'lead_priority' => 2,
        ]);

        $elitePackage = Package::factory()->create([
            'is_active' => true,
            'monthly_lead_quota' => 35,
            'lead_priority' => 3,
        ]);

        $priorityAgent = User::withoutEvents(function () {
            return User::factory()->create([
                'role' => 'agent',
                'status' => 'active',
                'onboarding_completed_at' => now(),
            ]);
        });
        RealtorProfile::factory()->create(['user_id' => $priorityAgent->id]);
        $priorityAgent->agentSubscription()->create([
            'package_id' => $elitePackage->id,
            'payment_status' => 'paid',
            'is_active' => true,
            'starts_at' => now(),
        ]);

        $leads = Lead::factory()->count(3)->create([
            'is_assignable' => true,
            'assigned_agent_id' => null,
            'status' => 'new',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.lead-assignments.auto-assign'))
            ->assertRedirect();

        $eliteQuota = AgentLeadQuota::where('user_id', $priorityAgent->id)->first();
        $this->assertNotNull($eliteQuota);
        $this->assertEquals(3, $eliteQuota->assigned_count);
    }

    public function test_assign_previously_assigned_lead_fails(): void
    {
        $lead = Lead::factory()->create([
            'is_assignable' => true,
            'assigned_agent_id' => $this->agent->id,
            'status' => 'assigned',
        ]);

        $this->actingAs($this->admin)->post(route('admin.lead-assignments.store'), [
            'lead_id' => $lead->id,
            'agent_id' => $this->agent->id,
        ])->assertSessionHasErrors('lead_id');
    }

    public function test_admin_can_update_assignment_status(): void
    {
        $lead = Lead::factory()->create([
            'is_assignable' => true,
            'assigned_agent_id' => null,
            'status' => 'new',
        ]);

        $this->actingAs($this->admin)->post(route('admin.lead-assignments.store'), [
            'lead_id' => $lead->id,
            'agent_id' => $this->agent->id,
        ]);

        $assignment = LeadAssignment::first();

        $this->actingAs($this->admin)->patch(route('admin.lead-assignments.update-status', $assignment), [
            'assignment_status' => 'accepted',
            'response_from_realtor' => 'Interested, will follow up',
        ])->assertRedirect();

        $assignment->refresh();
        $this->assertEquals('accepted', $assignment->assignment_status);
        $this->assertEquals('Interested, will follow up', $assignment->response_from_realtor);
        $this->assertNotNull($assignment->accepted_at);
    }

    public function test_rejected_assignment_clears_lead_assignment(): void
    {
        $lead = Lead::factory()->create([
            'is_assignable' => true,
            'assigned_agent_id' => $this->agent->id,
            'status' => 'assigned',
        ]);

        $assignment = LeadAssignment::create([
            'lead_id' => $lead->id,
            'assigned_to_user_id' => $this->agent->id,
            'assigned_by_user_id' => $this->admin->id,
            'package_id' => $this->package->id,
            'assignment_month' => now()->format('Y-m'),
            'assignment_status' => 'assigned',
        ]);

        $this->actingAs($this->admin)->patch(route('admin.lead-assignments.update-status', $assignment), [
            'assignment_status' => 'rejected',
        ]);

        $lead->refresh();
        $this->assertNull($lead->assigned_agent_id);
    }

    public function test_admin_can_view_quotas_index(): void
    {
        AgentLeadQuota::create([
            'user_id' => $this->agent->id,
            'package_id' => $this->package->id,
            'month' => now()->format('Y-m'),
            'monthly_quota' => 5,
            'assigned_count' => 2,
            'remaining_count' => 3,
            'overdue_count' => 0,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.agent-lead-quotas.index'))
            ->assertOk()
            ->assertSee($this->agent->name);
    }

    public function test_admin_can_edit_quota(): void
    {
        $quota = AgentLeadQuota::create([
            'user_id' => $this->agent->id,
            'package_id' => $this->package->id,
            'month' => now()->format('Y-m'),
            'monthly_quota' => 5,
            'assigned_count' => 2,
            'remaining_count' => 3,
            'overdue_count' => 0,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.agent-lead-quotas.update', $quota), [
                'monthly_quota' => 10,
            ])->assertRedirect();

        $quota->refresh();
        $this->assertEquals(10, $quota->monthly_quota);
        $this->assertEquals(8, $quota->remaining_count);
    }

    public function test_admin_can_view_package_lead_settings(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.package-lead-settings.index'))
            ->assertOk();
    }

    public function test_admin_can_update_package_lead_settings(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.package-lead-settings.update', $this->package), [
                'monthly_lead_quota' => 10,
                'lead_priority' => 5,
            ])->assertRedirect();

        $this->package->refresh();
        $this->assertEquals(10, $this->package->monthly_lead_quota);
        $this->assertEquals(5, $this->package->lead_priority);
    }

    public function test_non_admin_cannot_access_assignment_pages(): void
    {
        $regularUser = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($regularUser)
            ->get(route('admin.lead-assignments.index'))
            ->assertStatus(403);

        $this->actingAs($regularUser)
            ->get(route('admin.agent-lead-quotas.index'))
            ->assertStatus(403);
    }
}
