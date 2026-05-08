<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;

class BackfillLeadNormalizedIdentity extends Command
{
    protected $signature = 'omnireferral:backfill-lead-normalized {--chunk=500 : Rows per batch}';

    protected $description = 'Backfill leads.email_normalized and leads.phone_normalized for scalable duplicate detection.';

    public function handle(): int
    {
        $chunk = (int) $this->option('chunk');
        $chunk = $chunk > 0 ? $chunk : 500;

        $query = Lead::query()
            ->where(function ($q) {
                $q->whereNull('email_normalized')
                    ->orWhereNull('phone_normalized');
            })
            ->orderBy('id');

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('No leads require backfill.');
            return self::SUCCESS;
        }

        $this->info("Backfilling {$total} leads in chunks of {$chunk}...");

        $updated = 0;
        $query->chunkById($chunk, function ($rows) use (&$updated) {
            foreach ($rows as $lead) {
                $lead->email_normalized = Lead::normalizeEmail($lead->email);
                $lead->phone_normalized = Lead::normalizePhone($lead->phone);
                $lead->saveQuietly();
                $updated++;
            }
        });

        $this->info("Backfill complete. Updated {$updated} rows.");

        return self::SUCCESS;
    }
}

