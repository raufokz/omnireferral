<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use App\Services\LeadMultiFormatImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class GoogleSheetSyncAndLeadAddTest extends \Tests\TestCase
{
    use RefreshDatabase;

    public function test_google_sheet_columns_mapping_and_email_less_import()
    {
        $rawRows = [
            [
                'Timestamp' => '2026-07-21 14:30:00',
                'Lead Name:' => 'John NoEmail',
                'Buyer/Seller/Investor/Other?' => 'Investor',
                'Desired area to Buy OR Selling property address:' => '789 Ocean Drive, Miami FL',
                'How many Beds n Baths?' => '4 Beds / 3 Baths',
                'Budget OR Asking price?' => '$750,000',
                'Working with a realtor already? Yes/No' => 'No',
                'Timeline?' => 'Immediate',
                'DNC Disclaimer clear YES?' => 'YES',
                'Notes:' => 'High intent buyer interested in beachfront property',
                'Number:' => '(305) 555-0199',
                'Email:' => '', // No Email provided!
                'Rep Name:' => 'Sarah Representative',
                'STATE OF BUYING/SELLING' => 'FL',
                'Realtor:' => 'Agent Smith',
                'Sent to:' => 'In-House Desk',
                'status' => 'new',
                'Whom to send' => 'Primary Broker',
                'Reason: In-House' => 'VIP Lead',
                'Response from Realtor' => 'Pending review',
            ]
        ];

        $service = app(LeadMultiFormatImportService::class);
        $result = $service->importRawRows($rawRows, 'google_sheets');

        $this->assertEquals(1, $result['created']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertEquals(0, $result['failed']);

        $lead = Lead::where('name', 'John NoEmail')->first();
        $this->assertNotNull($lead);
        $this->assertNull($lead->email);
        $this->assertEquals('(305) 555-0199', $lead->phone);
        $this->assertEquals('investor', $lead->intent);
        $this->assertEquals('789 Ocean Drive, Miami FL', $lead->property_address);
        $this->assertEquals('4 Beds / 3 Baths', $lead->beds_baths);
        $this->assertEquals(750000, $lead->budget);
        $this->assertFalse($lead->working_with_realtor);
        $this->assertEquals('YES', $lead->dnc_disclaimer);
        $this->assertEquals('Sarah Representative', $lead->rep_name);
        $this->assertEquals('FL', $lead->state);
        $this->assertEquals('In-House Desk', $lead->sent_to);
        $this->assertEquals('VIP Lead', $lead->reason_in_house);
        $this->assertEquals('Pending review', $lead->realtor_response);
    }

    public function test_staff_and_admin_can_add_lead_without_email()
    {
        $staffUser = User::factory()->create(['role' => 'staff', 'status' => 'active']);

        $response = $this->actingAs($staffUser)->postJson(route('admin.leads.store'), [
            'name' => 'Jane StaffLead',
            'email' => '', // Empty email allowed!
            'phone' => '555-4321',
            'intent' => 'seller',
            'status' => 'new',
            'property_address' => '456 Palm Ave',
            'notes' => 'Created by staff user',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $lead = Lead::where('name', 'Jane StaffLead')->first();
        $this->assertNotNull($lead);
        $this->assertNull($lead->email);
        $this->assertEquals('staff_entry', $lead->source);
    }

    public function test_sync_google_sheet_endpoint_ajax()
    {
        $adminUser = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $csvContent = implode("\n", [
            "Timestamp,Lead Name:,Buyer/Seller/Investor/Other?,Desired area to Buy OR Selling property address:,How many Beds n Baths?,Budget OR Asking price?,Working with a realtor already? Yes/No,Timeline?,DNC Disclaimer clear YES?,Notes:,Number:,Email:,Rep Name:,STATE OF BUYING/SELLING,Realtor:,Sent to:,status,Whom to send,Reason: In-House,Response from Realtor",
            "2026-07-21,Sheet Lead Test,Buyer,123 Pine St,2 Beds,300k,No,Soon,YES,Some notes,1234567890,,Agent Ray,FL,Agent B,Team A,new,Team A,Direct,Accepted"
        ]);

        Http::fake([
            'docs.google.com/*' => Http::response($csvContent, 200, ['Content-Type' => 'text/csv']),
        ]);

        $response = $this->actingAs($adminUser)->postJson(route('admin.leads.sync.google-sheets'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'created' => 1]);

        $lead = Lead::where('name', 'Sheet Lead Test')->first();
        $this->assertNotNull($lead);
        $this->assertNull($lead->email);
    }

    public function test_live_data_endpoint_returns_json()
    {
        $staffUser = User::factory()->create(['role' => 'staff', 'status' => 'active']);
        Lead::factory()->create(['name' => 'Existing Lead']);

        $response = $this->actingAs($staffUser)->getJson(route('admin.leads.live-data'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertStringContainsString('Existing Lead', $response->json('html'));
    }
}
