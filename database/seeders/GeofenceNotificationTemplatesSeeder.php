<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeofenceNotificationTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Geofence Offer - Percentage Discount',
                'tag' => 'geofence_offer',
                'title_template' => '{{brand_name}} - Special Offer!',
                'body_template' => '{{discount_value}}% off! {{offer_name}}. {{offer_description}}',
                'action_type' => 'open_offer',
                'action_data' => json_encode(['screen' => 'offer_detail']),
                'deep_link_template' => 'trendpin://offer/{{offer_id}}',
                'is_active' => true,
            ],
            [
                'name' => 'Geofence Offer - Fixed Discount',
                'tag' => 'geofence_offer_fixed',
                'title_template' => '{{brand_name}} - Save Now!',
                'body_template' => 'Save ${{discount_value}}! {{offer_name}}. {{offer_description}}',
                'action_type' => 'open_offer',
                'action_data' => json_encode(['screen' => 'offer_detail']),
                'deep_link_template' => 'trendpin://offer/{{offer_id}}',
                'is_active' => true,
            ],
            [
                'name' => 'Geofence Offer - BOGO',
                'tag' => 'geofence_offer_bogo',
                'title_template' => '{{brand_name}} - Buy One Get One!',
                'body_template' => '{{offer_name}} - Don\'t miss this BOGO deal! {{offer_description}}',
                'action_type' => 'open_offer',
                'action_data' => json_encode(['screen' => 'offer_detail']),
                'deep_link_template' => 'trendpin://offer/{{offer_id}}',
                'is_active' => true,
            ],
            [
                'name' => 'Geofence Welcome - Location Entry',
                'tag' => 'geofence_welcome',
                'title_template' => 'Welcome to {{location_name}}!',
                'body_template' => 'Check out exclusive offers from {{brands_count}} stores near you.',
                'action_type' => 'open_location',
                'action_data' => json_encode(['screen' => 'location_offers']),
                'deep_link_template' => 'trendpin://location/{{location_id}}',
                'is_active' => true,
            ],
            [
                'name' => 'Geofence Nearby - General',
                'tag' => 'geofence_nearby',
                'title_template' => 'You\'re near {{brand_name}}!',
                'body_template' => 'Discover what\'s waiting for you. Tap to see offers.',
                'action_type' => 'open_brand',
                'action_data' => json_encode(['screen' => 'brand_detail']),
                'deep_link_template' => 'trendpin://brand/{{brand_id}}',
                'is_active' => true,
            ],
            [
                'name' => 'Geofence Flash Sale',
                'tag' => 'geofence_flash_sale',
                'title_template' => '⚡ Flash Sale at {{brand_name}}!',
                'body_template' => 'Limited time: {{offer_name}} - {{discount_value}}% off! Ends soon.',
                'action_type' => 'open_offer',
                'action_data' => json_encode(['screen' => 'offer_detail', 'urgent' => true]),
                'deep_link_template' => 'trendpin://offer/{{offer_id}}',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('notification_templates')->updateOrInsert(
                ['tag' => $template['tag']],
                array_merge($template, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Geofence notification templates seeded successfully!');
    }
}
