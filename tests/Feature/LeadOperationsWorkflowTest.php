<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use App\Services\LeadMultiFormatImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LeadOperationsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_lead_submissions_start_unassigned_and_duplicates_are_blocked(): void
    {
        Bus::fake();
        Notification::fake();

        $payload = [
            'name' => 'Taylor Prospect',
            'email' => 'taylor@example.com',
            'phone' => '(800) 555-1000',
            'intent' => 'buyer',
            'zip_code' => '75201',
            'property_type' => 'House',
            'budget' => 450000,
            'timeline' => '0-30 days',
            'preferences' => 'Wants a walkable neighborhood.',
        ];

        $this->post(route('leads.store'), $payload)->assertSessionHas('success');

        $lead = Lead::firstOrFail();
        $this->assertNull($lead->assigned_agent_id);
        $this->assertSame('new', $lead->status);

        $this->post(route('leads.store'), [
            ...$payload,
            'name' => 'Taylor Prospect Duplicate',
        ])->assertSessionHas('info');

        $this->assertSame(1, Lead::count());
    }

    public function test_import_preview_and_commit_map_structured_rows_and_skip_duplicates(): void
    {
        Lead::create([
            'lead_number' => 'OMNI-TEST-0001',
            'intent' => 'buyer',
            'package_type' => 'quick',
            'status' => 'new',
            'source' => 'seed',
            'name' => 'Existing Lead',
            'email' => 'existing@example.com',
            'phone' => '8005552000',
            'zip_code' => '73301',
        ]);

        $csv = <<<CSV
Timestamp,Lead Name,Buyer/Seller/Investor/Other,Property Address or Desired Area,Beds & Baths,Budget or Asking Price,Working with Realtor (Yes/No),Timeline,DNC Disclaimer,Notes,Phone Number,Email,Rep Name,State,Sent To,Status,Assignment,Reason (In-House),Realtor Response
2026-04-08 09:15:00,Casey Buyer,Buyer,123 Main St Dallas TX 75201,3/2,450000,Yes,0-30 days,Cleared,Hot lead,8005553000,casey@example.com,Ana Rep,TX,North Desk,Qualified,Legacy Desk,House-ready,Waiting call back
2026-04-08 10:00:00,Existing Lead,Buyer,500 Elm St Austin TX 73301,2/1,350000,No,30-60 days,Cleared,Duplicate row,8005552000,existing@example.com,Sam Rep,TX,South Desk,New,Old Assignment,Already in CRM,No response
CSV;

        $tempPath = tempnam(sys_get_temp_dir(), 'lead-import-');
        file_put_contents($tempPath, $csv);

        $file = new UploadedFile(
            $tempPath,
            'lead-import.csv',
            'text/csv',
            null,
            true
        );

        $service = app(LeadMultiFormatImportService::class);
        $preview = $service->previewFile($file);

        $this->assertCount(2, $preview);
        $this->assertFalse($preview[0]['_duplicate']);
        $this->assertTrue($preview[1]['_duplicate']);
        $this->assertSame('Email already exists', $preview[1]['_duplicate_reason']);

        $result = $service->importPreparedRows($preview);

        $this->assertSame(['created' => 1, 'skipped' => 1], $result);
        $this->assertSame(2, Lead::count());

        $importedLead = Lead::where('email', 'casey@example.com')->firstOrFail();
        $this->assertNull($importedLead->assigned_agent_id);
        $this->assertSame('qualified', $importedLead->status);
        $this->assertSame('123 Main St Dallas TX 75201', $importedLead->property_address);
        $this->assertSame('3/2', $importedLead->beds_baths);
        $this->assertTrue($importedLead->working_with_realtor);
        $this->assertSame('Ana Rep', $importedLead->rep_name);
        $this->assertSame('North Desk', $importedLead->sent_to);
        $this->assertSame('Legacy Desk', $importedLead->assignment);
        $this->assertSame('House-ready', $importedLead->reason_in_house);
        $this->assertSame('Waiting call back', $importedLead->realtor_response);
        $this->assertNotNull($importedLead->source_timestamp);
    }

    public function test_admin_upload_can_import_directly_without_stopping_at_preview(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'must_reset_password' => false,
        ]);

        $csv = <<<CSV
Timestamp,Lead Name,Buyer/Seller/Investor/Other,Property Address or Desired Area,Beds & Baths,Budget or Asking Price,Working with Realtor (Yes/No),Timeline,DNC Disclaimer,Notes,Phone Number,Email,Rep Name,State,Sent To,Status,Assignment,Reason (In-House),Realtor Response
2026-04-08 11:30:00,Riley Seller,Seller,700 Oak St Houston TX 77002,4/3,775000,No,30-60 days,Cleared,Ready to list,8005554111,riley@example.com,Kim Rep,TX,Listing Desk,Qualified,,Warm seller,Pending review
CSV;

        $tempPath = tempnam(sys_get_temp_dir(), 'lead-import-direct-');
        file_put_contents($tempPath, $csv);

        $file = new UploadedFile(
            $tempPath,
            'lead-direct-import.csv',
            'text/csv',
            null,
            true
        );

        $this->actingAs($admin)
            ->post(route('admin.leads.import.csv'), [
                'lead_file' => $file,
                'mode' => 'import',
            ])
            ->assertRedirect(route('admin.leads.index'))
            ->assertSessionHas('success');

        $lead = Lead::where('email', 'riley@example.com')->firstOrFail();
        $this->assertSame('Riley Seller', $lead->name);
        $this->assertNull($lead->assigned_agent_id);
        $this->assertSame('qualified', $lead->status);
    }

    public function test_leads_remain_unassigned_until_admin_assigns_them_manually(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'must_reset_password' => false,
        ]);

        $agent = User::factory()->create([
            'role' => 'agent',
            'must_reset_password' => false,
        ]);

        $lead = Lead::create([
            'lead_number' => 'OMNI-TEST-0009',
            'intent' => 'buyer',
            'package_type' => 'quick',
            'status' => 'new',
            'source' => 'website',
            'name' => 'Manual Queue Lead',
            'email' => 'manual-queue@example.com',
            'phone' => '8005554999',
            'zip_code' => '75201',
        ]);

        $this->assertFalse(Route::has('admin.leads.route'));
        $this->assertNull($lead->assigned_agent_id);

        $this->actingAs($admin)
            ->post(route('admin.leads.assign', $lead), [
                'agent_id' => $agent->id,
            ])
            ->assertRedirect();

        $lead->refresh();

        $this->assertSame($agent->id, $lead->assigned_agent_id);
        $this->assertSame('assigned', $lead->status);
        $this->assertSame('Assigned to ' . $agent->name, $lead->assignment);
    }

    public function test_google_sheet_edit_url_sync_imports_rows_and_preserves_website_source_without_duplicates(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'must_reset_password' => false,
        ]);

        $sheetUrl = 'https://docs.google.com/spreadsheets/d/1p8ECebCNqdL0aJiqfdsZjBiLiSVtHI9qMc_aynZw6Po/edit?gid=1123474577#gid=1123474577';
        $csv = <<<CSV
Timestamp,Lead Name,Buyer/Seller/Investor/Other,Property Address or Desired Area,Phone Number,Email,Rep Name,State,Status,Source
2026-04-08 12:30:00,Jordan Buyer,Buyer,123 Main St Dallas TX 75201,8005556111,jordan@example.com,Ana Rep,TX,Qualified,Website
CSV;

        Http::fake([
            'https://docs.google.com/*' => Http::response($csv, 200),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.leads.index'))
            ->post(route('admin.leads.sync.google-sheets'), [
                'sheet_url' => $sheetUrl,
            ])
            ->assertRedirect(route('admin.leads.index'))
            ->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://docs.google.com/spreadsheets/d/1p8ECebCNqdL0aJiqfdsZjBiLiSVtHI9qMc_aynZw6Po/export?format=csv&gid=1123474577';
        });

        $lead = Lead::where('email', 'jordan@example.com')->firstOrFail();
        $this->assertSame('Ana Rep', $lead->rep_name);
        $this->assertSame('website', $lead->source);
        $this->assertSame('qualified', $lead->status);

        $this->actingAs($admin)
            ->from(route('admin.leads.index'))
            ->post(route('admin.leads.sync.google-sheets'), [
                'sheet_url' => $sheetUrl,
            ])
            ->assertRedirect(route('admin.leads.index'))
            ->assertSessionHas('success');

        $this->assertSame(1, Lead::where('email', 'jordan@example.com')->count());
    }

    public function test_admin_leads_index_supports_rep_source_and_date_filters_with_summary_counts(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'must_reset_password' => false,
        ]);

        Lead::create([
            'lead_number' => 'OMNI-TEST-0101',
            'intent' => 'buyer',
            'package_type' => 'quick',
            'status' => 'qualified',
            'source' => 'website',
            'source_timestamp' => '2026-04-07 09:00:00',
            'name' => 'Qualified Ana',
            'email' => 'qualified-ana@example.com',
            'phone' => '8005557101',
            'zip_code' => '75201',
            'rep_name' => 'Ana Rep',
        ]);

        Lead::create([
            'lead_number' => 'OMNI-TEST-0102',
            'intent' => 'seller',
            'package_type' => 'quick',
            'status' => 'not_interested',
            'source' => 'website',
            'source_timestamp' => '2026-04-08 11:00:00',
            'name' => 'Rejected Ana',
            'email' => 'rejected-ana@example.com',
            'phone' => '8005557102',
            'zip_code' => '75202',
            'rep_name' => 'Ana Rep',
        ]);

        Lead::create([
            'lead_number' => 'OMNI-TEST-0103',
            'intent' => 'buyer',
            'package_type' => 'quick',
            'status' => 'qualified',
            'source' => 'google_sheets',
            'source_timestamp' => '2026-04-08 12:00:00',
            'name' => 'Qualified Bob',
            'email' => 'qualified-bob@example.com',
            'phone' => '8005557103',
            'zip_code' => '75203',
            'rep_name' => 'Bob Rep',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.leads.index', [
                'rep_name' => 'Ana Rep',
                'source' => 'website',
                'date_from' => '2026-04-07',
                'date_to' => '2026-04-08',
            ]))
            ->assertOk()
            ->assertViewHas('filters', function (array $filters) {
                return $filters['rep_name'] === 'Ana Rep'
                    && $filters['source'] === 'website'
                    && $filters['date_from'] === '2026-04-07'
                    && $filters['date_to'] === '2026-04-08';
            })
            ->assertViewHas('summary', function (array $summary) {
                return $summary['total'] === 2
                    && $summary['qualified'] === 1
                    && $summary['rejected'] === 1
                    && $summary['website'] === 2;
            });
    }
}
