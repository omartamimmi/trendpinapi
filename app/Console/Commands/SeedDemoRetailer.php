<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Modules\Business\app\Models\Brand;
use Modules\Business\app\Models\Branch;
use Modules\Business\app\Models\Group;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;
use Modules\RetailerOnboarding\app\Models\RetailerSubscription;

class SeedDemoRetailer extends Command
{
    protected $signature = 'retailers:seed-demo';
    protected $description = 'Create a demo retailer with groups, brands, and branches';

    public function handle()
    {
        $this->info('Creating demo retailer...');

        // Create demo user
        $user = User::firstOrCreate(
            ['email' => 'demo@trendpin.com'],
            [
                'name' => 'Demo Retailer',
                'password' => Hash::make('demo123'),
                'phone' => '+962791234567',
            ]
        );

        // Assign retailer role
        if (!$user->hasRole('retailer')) {
            $user->assignRole('retailer');
        }

        // Create completed onboarding
        $onboarding = RetailerOnboarding::firstOrCreate(
            ['user_id' => $user->id],
            [
                'current_step' => 'completed',
                'status' => 'completed',
                'approval_status' => 'approved',
                'phone_verified' => true,
                'cliq_verified' => true,
                'requires_completion' => false,
                'completed_steps' => ['retailer_details', 'payment_details', 'brand_information', 'subscription', 'payment'],
            ]
        );

        // Create subscription if plan exists
        $plan = SubscriptionPlan::where('type', 'retailer')->first();
        if ($plan) {
            RetailerSubscription::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'subscription_plan_id' => $plan->id,
                    'starts_at' => now(),
                    'ends_at' => now()->addYear(),
                    'status' => 'active',
                ]
            );
        }

        $this->info("Demo user created: {$user->email}");

        // Create Groups
        $this->info('Creating groups...');

        $foodGroup = Group::firstOrCreate(
            ['name' => 'Food & Beverages'],
            ['business_id' => null]
        );

        $fashionGroup = Group::firstOrCreate(
            ['name' => 'Fashion & Apparel'],
            ['business_id' => null]
        );

        $electronicsGroup = Group::firstOrCreate(
            ['name' => 'Electronics'],
            ['business_id' => null]
        );

        // Create Brands with Branches
        $this->info('Creating brands and branches...');

        // Food & Beverages brands
        $brands = [
            [
                'group' => $foodGroup,
                'name' => 'Coffee House',
                'title' => 'Coffee House',
                'title_ar' => 'بيت القهوة',
                'description' => 'Premium coffee and pastries',
                'description_ar' => 'قهوة وحلويات فاخرة',
                'phone_number' => '+962791111111',
                'status' => 'publish',
                'branches' => ['Abdoun Branch', 'Sweifieh Branch', 'Downtown Branch'],
            ],
            [
                'group' => $foodGroup,
                'name' => 'Fresh Bites',
                'title' => 'Fresh Bites',
                'title_ar' => 'فريش بايتس',
                'description' => 'Healthy food options',
                'description_ar' => 'خيارات طعام صحية',
                'phone_number' => '+962792222222',
                'status' => 'publish',
                'branches' => ['Khalda Branch', 'Mecca Mall Branch'],
            ],
            [
                'group' => $fashionGroup,
                'name' => 'Urban Style',
                'title' => 'Urban Style',
                'title_ar' => 'أوربان ستايل',
                'description' => 'Contemporary fashion for all',
                'description_ar' => 'أزياء عصرية للجميع',
                'phone_number' => '+962793333333',
                'status' => 'publish',
                'branches' => ['City Mall', 'Taj Mall', 'Galleria Mall'],
            ],
            [
                'group' => $fashionGroup,
                'name' => 'Kids Corner',
                'title' => 'Kids Corner',
                'title_ar' => 'ركن الأطفال',
                'description' => 'Children clothing and accessories',
                'description_ar' => 'ملابس وإكسسوارات أطفال',
                'phone_number' => '+962794444444',
                'status' => 'publish',
                'branches' => ['Abdali Mall'],
            ],
            [
                'group' => $electronicsGroup,
                'name' => 'Tech Zone',
                'title' => 'Tech Zone',
                'title_ar' => 'تك زون',
                'description' => 'Latest electronics and gadgets',
                'description_ar' => 'أحدث الإلكترونيات والأجهزة',
                'phone_number' => '+962795555555',
                'status' => 'publish',
                'branches' => ['Mecca Mall', 'City Mall', 'Taj Mall', 'Abdali Mall'],
            ],
            [
                'group' => $electronicsGroup,
                'name' => 'Mobile World',
                'title' => 'Mobile World',
                'title_ar' => 'عالم الموبايل',
                'description' => 'Mobile phones and accessories',
                'description_ar' => 'هواتف وإكسسوارات',
                'phone_number' => '+962796666666',
                'status' => 'publish',
                'branches' => ['Downtown', 'Sweifieh'],
            ],
        ];

        foreach ($brands as $brandData) {
            $brand = Brand::firstOrCreate(
                [
                    'name' => $brandData['name'],
                    'create_user' => $user->id,
                ],
                [
                    'title' => $brandData['title'],
                    'title_ar' => $brandData['title_ar'],
                    'description' => $brandData['description'],
                    'description_ar' => $brandData['description_ar'],
                    'phone_number' => $brandData['phone_number'],
                    'status' => $brandData['status'],
                    'group_id' => $brandData['group']->id,
                    'is_main_branch' => 1,
                    'type' => 'in_person',
                ]
            );

            // Create branches
            foreach ($brandData['branches'] as $branchName) {
                Branch::firstOrCreate(
                    [
                        'brand_id' => $brand->id,
                        'name' => $branchName,
                    ]
                );
            }

            $this->info("  Created: {$brand->name} with " . count($brandData['branches']) . " branches");
        }

        $this->newLine();
        $this->info('Demo retailer created successfully!');
        $this->info('Email: demo@trendpin.com');
        $this->info('Password: demo123');
        $this->info('Brands: 6');
        $this->info('Groups: 3');

        return 0;
    }
}
