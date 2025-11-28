<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Business\app\Models\Brand;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;

class MigrateShopsToBrands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retailers:migrate-shops
                            {--dry-run : Run without making changes}
                            {--force : Skip confirmation}
                            {--with-deleted : Include soft-deleted shops}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate shops to brands table and assign shop owners as retailers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scanning shops table...');

        // Get shops query
        $shopsQuery = DB::table('shops');

        if (!$this->option('with-deleted')) {
            $shopsQuery->whereNull('deleted_at');
        }

        $shops = $shopsQuery->get();

        if ($shops->isEmpty()) {
            $this->warn('No shops found to migrate.');
            return 0;
        }

        // Get unique user IDs
        $userIds = $shops->pluck('create_user')->filter()->unique()->values();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $this->info("Found {$shops->count()} shops from {$users->count()} users.");

        if ($this->option('dry-run')) {
            $this->info('DRY RUN - No changes will be made.');
            $this->newLine();

            // Show summary table
            $this->table(
                ['User ID', 'Name', 'Email', 'Shops Count', 'Has Retailer Role'],
                $users->map(function ($user) use ($shops) {
                    $shopCount = $shops->where('create_user', $user->id)->count();
                    return [
                        $user->id,
                        $user->name,
                        $user->email,
                        $shopCount,
                        $user->hasRole('retailer') ? 'Yes' : 'No',
                    ];
                })
            );

            return 0;
        }

        if (!$this->option('force') && !$this->confirm("Migrate {$shops->count()} shops and assign {$users->count()} users as retailers?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Starting migration...');
        $this->newLine();

        $migratedShops = 0;
        $migratedUsers = 0;
        $errors = [];

        // Migrate shops to brands
        $this->info('Migrating shops to brands...');
        $bar = $this->output->createProgressBar($shops->count());
        $bar->start();

        foreach ($shops as $shop) {
            try {
                // Check if brand already exists (by source_id or slug)
                $existingBrand = Brand::where('source_id', $shop->id)
                    ->orWhere('slug', $shop->slug)
                    ->first();

                if ($existingBrand) {
                    $bar->advance();
                    continue;
                }

                // Create brand from shop data
                Brand::create([
                    'name' => $shop->title,
                    'title' => $shop->title,
                    'title_ar' => $shop->title_ar,
                    'slug' => $shop->slug,
                    'description' => $shop->description,
                    'description_ar' => $shop->description_ar,
                    'logo' => $shop->featured_image,
                    'image_id' => $shop->image_id,
                    'gallery' => $shop->gallery,
                    'video' => $shop->video,
                    'featured_mobile' => $shop->featured_image,
                    'status' => $shop->status,
                    'publish_date' => $shop->publish_date,
                    'days' => $shop->days,
                    'open_status' => $shop->open_status,
                    'featured' => $shop->featured,
                    'location_id' => $shop->location_id,
                    'location' => null, // Will be set from location_id if needed
                    'phone_number' => $shop->phone_number,
                    'lat' => $shop->lat,
                    'lng' => $shop->lng,
                    'is_main_branch' => $shop->is_main_branch ?? 1,
                    'main_branch_id' => $shop->main_branch_id,
                    'type' => $shop->type,
                    'website_link' => $shop->website_link,
                    'insta_link' => $shop->insta_link,
                    'facebook_link' => $shop->facebook_link,
                    'source_id' => $shop->id, // Reference to original shop
                    'create_user' => $shop->create_user,
                    'update_user' => $shop->update_user,
                ]);

                $migratedShops++;
            } catch (\Exception $e) {
                $errors[] = "Shop {$shop->id}: {$e->getMessage()}";
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Assign users as retailers and create onboarding records
        $this->info('Assigning users as retailers...');
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            try {
                // Assign retailer role if not already assigned
                if (!$user->hasRole('retailer')) {
                    $user->assignRole('retailer');
                }

                // Create onboarding record
                $onboarding = RetailerOnboarding::firstOrNew(['user_id' => $user->id]);

                if (!$onboarding->exists) {
                    $onboarding->fill([
                        'current_step' => 'retailer_details',
                        'status' => 'in_progress',
                        'phone_verified' => !empty($user->phone),
                        'cliq_verified' => false,
                        'requires_completion' => true,
                    ]);
                    $onboarding->save();
                } elseif ($onboarding->status !== 'completed') {
                    $onboarding->update(['requires_completion' => true]);
                }

                $migratedUsers++;
            } catch (\Exception $e) {
                $errors[] = "User {$user->id}: {$e->getMessage()}";
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Migration complete!');
        $this->info("Shops migrated to brands: {$migratedShops}");
        $this->info("Users assigned as retailers: {$migratedUsers}");

        if (!empty($errors)) {
            $this->newLine();
            $this->warn('Errors encountered:');
            foreach (array_slice($errors, 0, 10) as $error) {
                $this->error("  - {$error}");
            }
            if (count($errors) > 10) {
                $this->warn('  ... and ' . (count($errors) - 10) . ' more errors');
            }
        }

        return 0;
    }
}
