<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Modules\Business\app\Models\Brand;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;

class MigrateShopUsersToRetailers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retailers:migrate-shop-users
                            {--dry-run : Run without making changes}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate users with shops/brands to retailer role and create onboarding records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Finding users with shops/brands...');

        // Find all user IDs that have created brands
        $userIds = Brand::whereNotNull('create_user')
            ->distinct()
            ->pluck('create_user')
            ->toArray();

        if (empty($userIds)) {
            $this->warn('No users with shops/brands found.');
            return 0;
        }

        // Get users
        $users = User::whereIn('id', $userIds)->get();

        $this->info("Found {$users->count()} users with shops/brands.");

        if ($this->option('dry-run')) {
            $this->info('DRY RUN - No changes will be made.');
            $this->table(
                ['ID', 'Name', 'Email', 'Has Retailer Role', 'Has Onboarding'],
                $users->map(function ($user) {
                    return [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->hasRole('retailer') ? 'Yes' : 'No',
                        $user->retailerOnboarding ? 'Yes' : 'No',
                    ];
                })
            );
            return 0;
        }

        if (!$this->option('force') && !$this->confirm("Do you want to migrate {$users->count()} users to retailer role?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $migrated = 0;
        $skipped = 0;

        $this->withProgressBar($users, function ($user) use (&$migrated, &$skipped) {
            // Skip if already a retailer with completed onboarding
            if ($user->hasRole('retailer') && $user->retailerOnboarding && $user->retailerOnboarding->status === 'completed') {
                $skipped++;
                return;
            }

            // Assign retailer role if not already assigned
            if (!$user->hasRole('retailer')) {
                $user->assignRole('retailer');
            }

            // Create or update onboarding record
            $onboarding = RetailerOnboarding::firstOrNew(['user_id' => $user->id]);

            if (!$onboarding->exists) {
                // New onboarding - set to require completion
                $onboarding->fill([
                    'current_step' => 'retailer_details',
                    'status' => 'in_progress',
                    'phone_verified' => false,
                    'cliq_verified' => false,
                    'requires_completion' => true,
                ]);
                $onboarding->save();
            } elseif ($onboarding->status !== 'completed') {
                // Existing incomplete onboarding - ensure it requires completion
                $onboarding->update(['requires_completion' => true]);
            }

            $migrated++;
        });

        $this->newLine(2);
        $this->info("Migration complete!");
        $this->info("Migrated: {$migrated} users");
        $this->info("Skipped (already completed): {$skipped} users");

        return 0;
    }
}
