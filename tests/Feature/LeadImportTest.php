<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use App\Services\LeadMultiFormatImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeadImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private LeadMultiFormatImportService $importService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->importService = app(LeadMultiFormatImportService::class);
    }

    public function test_csv_import_works_when_package_type_is_missing(): void
    {
        $csv = "name,email,phone\nJohn Doe,john@example.com,1234567890";
        $file = UploadedFile::fake()->createWithContent('leads.csv', $csv);

        $rows = $this->importService->previewFile($file);
        $result = $this->importService->importPreparedRows($rows);

        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['failed']);
        $this->assertNull(Lead::first()->package_type);
    }

    public function test_csv_import_works_when_package_type_is_null(): void
    {
        $csv = "name,email,phone,package_type\nJane Doe,jane@example.com,9876543210,";
        $file = UploadedFile::fake()->createWithContent('leads.csv', $csv);

        $rows = $this->importService->previewFile($file);
        $result = $this->importService->importPreparedRows($rows);

        $this->assertSame(1, $result['created']);
        $this->assertNull(Lead::first()->package_type);
    }

    public function test_csv_import_does_not_crash_when_package_type_is_invalid(): void
    {
        $csv = "name,email,phone,package_type\nJack Doe,jack@example.com,5551234567,starter";
        $file = UploadedFile::fake()->createWithContent('leads.csv', $csv);

        $rows = $this->importService->previewFile($file);
        $result = $this->importService->importPreparedRows($rows);

        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['failed']);
        $this->assertSame('starter', Lead::first()->package_type);
    }

    public function test_csv_import_accepts_any_package_type_value(): void
    {
        $csv = "name,email,phone,package_type\nJill Doe,jill@example.com,5557654321,gold-premium";
        $file = UploadedFile::fake()->createWithContent('leads.csv', $csv);

        $rows = $this->importService->previewFile($file);
        $result = $this->importService->importPreparedRows($rows);

        $this->assertSame(1, $result['created']);
        $this->assertSame('gold-premium', Lead::first()->package_type);
    }

    public function test_one_bad_row_does_not_stop_full_import(): void
    {
        // Create a lead to be the duplicate
        Lead::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'lead_number' => Lead::generateLeadNumber(),
            'intent' => 'buyer',
            'status' => 'new',
            'source' => 'manual',
        ]);

        $csv = "name,email,phone\nJohn,john@example.com,1234567890\nJane,jane@example.com,9876543210";
        $file = UploadedFile::fake()->createWithContent('leads.csv', $csv);

        $rows = $this->importService->previewFile($file);
        $result = $this->importService->importPreparedRows($rows);

        $this->assertSame(1, $result['created'], 'Only Jane should be created, John is duplicate');
        $this->assertSame(1, $result['skipped'], 'John row should be skipped as duplicate');
        $this->assertSame(0, $result['failed'], 'No rows should fail at DB level');
    }

    public function test_duplicate_leads_are_skipped(): void
    {
        Lead::create([
            'lead_number' => Lead::generateLeadNumber(),
            'name' => 'Existing',
            'email' => 'existing@example.com',
            'phone' => '1112223333',
            'intent' => 'buyer',
            'status' => 'new',
            'zip_code' => '12345',
        ]);

        $csv = "name,email,phone\nNewDup,existing@example.com,1112223333";
        $file = UploadedFile::fake()->createWithContent('leads.csv', $csv);

        $rows = $this->importService->previewFile($file);
        $result = $this->importService->importPreparedRows($rows);

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, $result['skipped']);
        $this->assertSame(1, Lead::count());
    }

    public function test_admin_can_import_csv_via_route(): void
    {
        $csv = "name,email,phone\nJim,jim@example.com,5550001111";
        $file = UploadedFile::fake()->createWithContent('leads.csv', $csv);

        $this->actingAs($this->admin)
            ->post(route('admin.leads.import.csv'), [
                'lead_file' => $file,
                'mode' => 'import',
            ])
            ->assertSessionHas('success')
            ->assertRedirect(route('admin.leads.index'));

        $this->assertSame(1, Lead::count());
        $this->assertSame('Jim', Lead::first()->name);
    }

    public function test_google_sheet_import_creates_leads_successfully(): void
    {
        $rows = $this->importService->importRawRows([
            ['name' => 'Sheet Lead', 'email' => 'sheet@example.com', 'phone' => '5551239999', 'intent' => 'buyer'],
        ], 'google_sheets');

        $this->assertSame(1, $rows['created']);
        $this->assertDatabaseHas('leads', ['email' => 'sheet@example.com']);
    }

    public function test_package_type_starter_does_not_cause_sql_error(): void
    {
        $csv = "name,email,phone,package_type\nStarterTest,starter@example.com,5550009999,starter";
        $file = UploadedFile::fake()->createWithContent('leads.csv', $csv);

        $rows = $this->importService->previewFile($file);
        $result = $this->importService->importPreparedRows($rows);

        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['failed']);
    }

    public function test_import_result_includes_failed_rows_info(): void
    {
        // Create a lead to be the duplicate
        Lead::create([
            'name' => 'Good',
            'email' => 'g@example.com',
            'phone' => '1111111111',
            'lead_number' => Lead::generateLeadNumber(),
            'intent' => 'buyer',
            'status' => 'new',
            'source' => 'manual',
        ]);

        $csv = "name,email,phone\nGood,g@example.com,1111111111\nGood2,g2@example.com,2222222222";
        $file = UploadedFile::fake()->createWithContent('leads.csv', $csv);

        $rows = $this->importService->previewFile($file);
        $result = $this->importService->importPreparedRows($rows);

        $this->assertSame(1, $result['created']);
        $this->assertSame(1, $result['skipped']);
        $this->assertArrayHasKey('failed_rows', $result);
    }
}
