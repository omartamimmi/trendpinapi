<?php

namespace Modules\Geofence\Console;

use Illuminate\Console\Command;
use Modules\Geofence\Services\Contracts\RadarServiceInterface;

class SyncGeofencesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'geofence:sync
                            {--tag= : Filter geofences by tag}
                            {--force : Force sync all geofences even if already synced}';

    /**
     * The console command description.
     */
    protected $description = 'Sync local geofences to Radar.io';

    public function __construct(
        private RadarServiceInterface $radarService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting geofence synchronization...');

        try {
            $results = $this->radarService->syncAllGeofences();

            $this->newLine();
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Created', $results['created']],
                    ['Updated', $results['updated']],
                    ['Failed', $results['failed']],
                ]
            );

            if ($results['failed'] > 0) {
                $this->warn("Some geofences failed to sync. Check logs for details.");
                return Command::FAILURE;
            }

            $this->info('Geofence synchronization completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Synchronization failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
