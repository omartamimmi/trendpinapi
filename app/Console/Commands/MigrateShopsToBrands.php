<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Business\app\Models\Brand;
use Modules\Business\app\Models\Branch;
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
    protected $description = 'Migrate shops to brands (grouped by main branch) and create branch records for each shop';

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

        // Group shops by their brand (main_branch_id or their own id if they are main)
        $brandGroups = [];
        foreach ($shops as $shop) {
            // Determine the brand key (main branch id)
            $brandKey = $shop->is_main_branch ? $shop->id : $shop->main_branch_id;

            if (!isset($brandGroups[$brandKey])) {
                $brandGroups[$brandKey] = [
                    'main_shop' => null,
                    'branches' => [],
                ];
            }

            if ($shop->is_main_branch) {
                $brandGroups[$brandKey]['main_shop'] = $shop;
            }

            $brandGroups[$brandKey]['branches'][] = $shop;
        }

        // Get unique user IDs
        $userIds = $shops->pluck('create_user')->filter()->unique()->values();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $totalBrands = count($brandGroups);
        $totalBranches = $shops->count();

        $this->info("Found {$totalBranches} shops grouped into {$totalBrands} brands");
        $this->info("From {$users->count()} users");

        if ($this->option('dry-run')) {
            $this->info('DRY RUN - No changes will be made.');
            $this->newLine();

            // Show first 10 brand groups as examples
            $this->info('Sample brand groups:');
            $count = 0;
            foreach ($brandGroups as $brandKey => $group) {
                if ($count >= 10) break;
                $mainShop = $group['main_shop'];
                $branchCount = count($group['branches']);
                $brandName = $mainShop ? $mainShop->title : 'Unknown (missing main shop)';
                $this->line("  • {$brandName}: {$branchCount} branch(es)");
                $count++;
            }

            $this->newLine();
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

        if (!$this->option('force') && !$this->confirm("Migrate {$totalBrands} brands with {$totalBranches} total branches from {$users->count()} users?")) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Starting migration...');
        $this->newLine();

        $migratedBrands = 0;
        $migratedBranches = 0;
        $migratedUsers = 0;
        $errors = [];
        $skippedNoMain = 0;

        // Map to store main_shop_id => brand_id
        $shopToBrandMap = [];

        // Step 1: Create brands from main shops
        $this->info('Creating brands from shop groups...');
        $bar = $this->output->createProgressBar($totalBrands);
        $bar->start();

        foreach ($brandGroups as $brandKey => $group) {
            try {
                $mainShop = $group['main_shop'];

                if (!$mainShop) {
                    $errors[] = "Brand group {$brandKey}: Missing main shop (has " . count($group['branches']) . " branch(es))";
                    $skippedNoMain++;
                    $bar->advance();
                    continue;
                }

                // Check if brand already exists
                $existingBrand = Brand::where('source_id', $mainShop->id)->first();

                if ($existingBrand) {
                    $shopToBrandMap[$mainShop->id] = $existingBrand->id;
                    $bar->advance();
                    continue;
                }

                // Create brand using main shop's information
                $brand = Brand::create([
                    'name' => $mainShop->title,
                    'title' => $mainShop->title,
                    'title_ar' => $mainShop->title_ar,
                    'slug' => $mainShop->slug,
                    'description' => $mainShop->description,
                    'description_ar' => $mainShop->description_ar,
                    'logo' => $mainShop->featured_image,
                    'image_id' => $mainShop->image_id,
                    'gallery' => $mainShop->gallery,
                    'video' => $mainShop->video,
                    'featured_mobile' => $mainShop->featured_image,
                    'status' => $mainShop->status,
                    'publish_date' => $mainShop->publish_date,
                    'days' => $mainShop->days,
                    'open_status' => $mainShop->open_status,
                    'featured' => $mainShop->featured,
                    'location_id' => $mainShop->location_id,
                    'location' => null,
                    'phone_number' => $mainShop->phone_number,
                    'lat' => $mainShop->lat,
                    'lng' => $mainShop->lng,
                    'type' => $mainShop->type,
                    'website_link' => $mainShop->website_link,
                    'insta_link' => $mainShop->insta_link,
                    'facebook_link' => $mainShop->facebook_link,
                    'source_id' => $mainShop->id,
                    'create_user' => $mainShop->create_user,
                    'update_user' => $mainShop->update_user,
                ]);

                $shopToBrandMap[$mainShop->id] = $brand->id;
                $migratedBrands++;
            } catch (\Exception $e) {
                $errors[] = "Brand group {$brandKey}: {$e->getMessage()}";
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Step 2: Create branch records for ALL shops (including main branches)
        $this->info('Creating branch records for all shops...');
        $bar = $this->output->createProgressBar($totalBranches);
        $bar->start();

        foreach ($brandGroups as $brandKey => $group) {
            $mainShop = $group['main_shop'];

            if (!$mainShop) {
                // Skip if no main shop
                foreach ($group['branches'] as $shop) {
                    $bar->advance();
                }
                continue;
            }

            $brandId = $shopToBrandMap[$mainShop->id] ?? null;

            if (!$brandId) {
                // Try to find brand by source_id
                $brand = Brand::where('source_id', $mainShop->id)->first();
                $brandId = $brand?->id;
            }

            if (!$brandId) {
                $errors[] = "Cannot find brand for main shop {$mainShop->id}";
                foreach ($group['branches'] as $shop) {
                    $bar->advance();
                }
                continue;
            }

            // Create branch for each shop in the group
            foreach ($group['branches'] as $shop) {
                try {
                    // Check if branch already exists
                    $existingBranch = Branch::where('brand_id', $brandId)
                        ->where('name', $shop->title)
                        ->first();

                    if ($existingBranch) {
                        $bar->advance();
                        continue;
                    }

                    // Create branch record
                    Branch::create([
                        'brand_id' => $brandId,
                        'name' => $shop->title,
                        'location' => $shop->location_id ? "Location ID: {$shop->location_id}" : null,
                        'phone' => $shop->phone_number,
                        'is_main' => $shop->is_main_branch ? 1 : 0,
                    ]);

                    $migratedBranches++;
                } catch (\Exception $e) {
                    $errors[] = "Branch for shop {$shop->id}: {$e->getMessage()}";
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Step 3: Assign users as retailers
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
        $this->info("Brands created: {$migratedBrands}");
        $this->info("Branches created: {$migratedBranches}");
        $this->info("Users assigned as retailers: {$migratedUsers}");

        if ($skippedNoMain > 0) {
            $this->warn("Brand groups skipped (missing main shop): {$skippedNoMain}");
        }

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
