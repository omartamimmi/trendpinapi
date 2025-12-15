<?php

namespace Modules\Log\app\Console\Commands;

use Illuminate\Console\Command;
use Modules\Log\app\Services\Contracts\LogServiceInterface;

class CleanupLogsCommand extends Command
{
    protected $signature = 'logs:cleanup
                            {--days=30 : Number of days to keep logs}
                            {--dry-run : Show how many logs would be deleted without actually deleting}';

    protected $description = 'Clean up old activity logs from the database';

    public function __construct(
        protected LogServiceInterface $logService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $daysToKeep = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Cleaning up logs older than {$daysToKeep} days...");

        if ($dryRun) {
            $this->warn('DRY RUN - No logs will actually be deleted');

            // Get count without deleting
            $cutoffDate = now()->subDays($daysToKeep);
            $count = \Modules\Log\app\Models\ActivityLog::where('logged_at', '<', $cutoffDate)->count();

            $this->info("Would delete {$count} log entries");
            return Command::SUCCESS;
        }

        $startTime = microtime(true);
        $deleted = $this->logService->cleanup($daysToKeep);
        $duration = round(microtime(true) - $startTime, 2);

        $this->info("Successfully deleted {$deleted} log entries in {$duration} seconds");

        return Command::SUCCESS;
    }
}
