<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RepairAgentProfileIntegrityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:agent-profile-integrity {--dry-run : Do not write changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repairs realtor_profiles integrity issues (orphans, duplicate user_id, duplicate slugs, nullable text normalization)';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('RepairAgentProfileIntegrityCommand started'.($dryRun ? ' (dry-run)' : '').'...');

        // Include a PHP repair script to keep this command small and easy to audit.
        // The script uses the DRY_RUN env flag.
        putenv('DRY_RUN='.($dryRun ? '1' : '0'));

        require_once base_path('database/repair/agent_profile_integrity_repair.php');

        $this->info('RepairAgentProfileIntegrityCommand finished.');

        return self::SUCCESS;
    }
}

