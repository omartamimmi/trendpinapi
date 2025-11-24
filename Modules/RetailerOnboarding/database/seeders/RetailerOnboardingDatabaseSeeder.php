<?php

namespace Modules\RetailerOnboarding\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;

class RetailerOnboardingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create subscription plans
        SubscriptionPlan::create([
            'name' => 'Trendpin Blue',
            'color' => 'blue',
            'offers_count' => 35,
            'description' => 'Perfect for small retailers starting out. Get 35 offers per month.',
            'price' => 0.00,
            'billing_period' => 'monthly',
            'duration_months' => 12,
            'trial_days' => 90, // 3 months free
            'is_active' => true,
        ]);

        SubscriptionPlan::create([
            'name' => 'Trendpin Pink',
            'color' => 'pink',
            'offers_count' => 100,
            'description' => 'For growing businesses. Get 100 offers per month with premium features.',
            'price' => 250.00,
            'billing_period' => 'monthly',
            'duration_months' => 12,
            'trial_days' => 0,
            'is_active' => true,
        ]);

        // Annual plan option
        SubscriptionPlan::create([
            'name' => 'Trendpin Blue Annual',
            'color' => 'blue',
            'offers_count' => 35,
            'description' => 'Annual subscription with 35 offers per month.',
            'price' => 500.00,
            'billing_period' => 'yearly',
            'duration_months' => 12,
            'trial_days' => 0,
            'is_active' => true,
        ]);
    }
}
