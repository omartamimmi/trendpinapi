<?php

namespace Modules\Notification\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Notification\app\Models\NotificationSetting;

class NotificationSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'event_id' => 'phone_verification',
                'name' => 'Phone Verification OTP',
                'description' => 'Verification code sent for phone number verification',
                'category' => 'Authentication',
                'is_enabled' => true,
                'recipients' => ['admin', 'retailer', 'customer'],
                'channels' => ['email' => false, 'sms' => true, 'whatsapp' => true, 'push' => false],
                'templates' => [
                    'admin' => [
                        'email' => ['subject' => 'Verification Code - {{app_name}}', 'body' => "Your verification code is: {{otp_code}}\n\nThis code expires in {{expiry_minutes}} minutes.", 'title' => 'Verification Code'],
                        'sms' => ['body' => 'Your {{app_name}} verification code is: {{otp_code}}. Expires in {{expiry_minutes}} minutes.'],
                    ],
                    'retailer' => [
                        'email' => ['subject' => 'Verification Code - {{app_name}}', 'body' => "Your verification code is: {{otp_code}}\n\nThis code expires in {{expiry_minutes}} minutes.\n\nDo not share this code with anyone.", 'title' => 'Verification Code'],
                        'sms' => ['body' => 'Your {{app_name}} verification code is: {{otp_code}}. Expires in {{expiry_minutes}} minutes.'],
                    ],
                    'customer' => [
                        'email' => ['subject' => 'Verification Code - {{app_name}}', 'body' => "Your {{app_name}} verification code is: {{otp_code}}\n\nExpires in {{expiry_minutes}} minutes.", 'title' => 'Verification Code'],
                        'sms' => ['body' => 'Your {{app_name}} code: {{otp_code}}. Expires in {{expiry_minutes}} min.'],
                    ],
                ],
                'placeholders' => ['otp_code', 'expiry_minutes', 'app_name'],
            ],
            [
                'event_id' => 'new_customer',
                'name' => 'New Customer Registration',
                'description' => 'Welcome email sent when a new customer registers',
                'category' => 'Customer',
                'is_enabled' => true,
                'recipients' => ['admin', 'customer'],
                'channels' => ['email' => true, 'sms' => false, 'whatsapp' => false, 'push' => false],
                'templates' => [
                    'admin' => [
                        'email' => ['subject' => 'New Customer Registration - {{customer_name}}', 'body' => "A new customer has registered.\n\nCustomer: {{customer_name}}\nEmail: {{customer_email}}\nDate: {{registration_date}}", 'title' => 'New Customer'],
                    ],
                    'customer' => [
                        'email' => ['subject' => 'Welcome to {{app_name}}!', 'body' => "Hi {{customer_name}},\n\nWelcome to {{app_name}}! We're excited to have you.\n\nStart exploring our amazing offers from local retailers near you.\n\nBest regards,\nThe {{app_name}} Team", 'title' => 'Welcome!'],
                    ],
                ],
                'placeholders' => ['customer_name', 'customer_email', 'app_name', 'registration_date'],
            ],
            [
                'event_id' => 'new_retailer',
                'name' => 'New Retailer Application',
                'description' => 'Notification when a new retailer applies',
                'category' => 'Retailer',
                'is_enabled' => true,
                'recipients' => ['admin', 'retailer'],
                'channels' => ['email' => true, 'sms' => false, 'whatsapp' => false, 'push' => true],
                'templates' => [
                    'admin' => [
                        'email' => ['subject' => 'New Retailer Application - {{retailer_name}}', 'body' => "A new retailer has applied.\n\nRetailer: {{retailer_name}}\nBusiness: {{business_name}}\nEmail: {{retailer_email}}\nDate: {{submission_date}}", 'title' => 'New Application'],
                    ],
                    'retailer' => [
                        'email' => ['subject' => 'Application Received - {{app_name}}', 'body' => "Hi {{retailer_name}},\n\nThank you for applying to {{app_name}}!\n\nWe have received your application and will review it shortly. You will receive an email once your application has been processed.\n\nBest regards,\nThe {{app_name}} Team", 'title' => 'Application Received'],
                    ],
                ],
                'placeholders' => ['retailer_name', 'retailer_email', 'business_name', 'app_name', 'submission_date'],
            ],
            [
                'event_id' => 'retailer_approved',
                'name' => 'Retailer Approved',
                'description' => 'Notification when a retailer application is approved',
                'category' => 'Retailer',
                'is_enabled' => true,
                'recipients' => ['retailer'],
                'channels' => ['email' => true, 'sms' => true, 'whatsapp' => false, 'push' => true],
                'templates' => [
                    'retailer' => [
                        'email' => ['subject' => 'Congratulations! Your Account is Approved - {{app_name}}', 'body' => "Hi {{retailer_name}},\n\nGreat news! Your retailer application has been approved.\n\nYou can now log in and start adding your offers to reach customers in your area.\n\nWelcome to {{app_name}}!\n\nBest regards,\nThe {{app_name}} Team", 'title' => 'Account Approved!'],
                        'sms' => ['body' => 'Congratulations {{retailer_name}}! Your {{app_name}} retailer account is approved. Log in to start adding offers!'],
                    ],
                ],
                'placeholders' => ['retailer_name', 'app_name'],
            ],
            [
                'event_id' => 'retailer_rejected',
                'name' => 'Retailer Rejected',
                'description' => 'Notification when a retailer application is rejected',
                'category' => 'Retailer',
                'is_enabled' => true,
                'recipients' => ['retailer'],
                'channels' => ['email' => true, 'sms' => false, 'whatsapp' => false, 'push' => false],
                'templates' => [
                    'retailer' => [
                        'email' => ['subject' => 'Application Update - {{app_name}}', 'body' => "Hi {{retailer_name}},\n\nWe regret to inform you that we were unable to approve your retailer application at this time.\n\nReason: {{admin_message}}\n\nIf you have any questions, please contact our support team.\n\nBest regards,\nThe {{app_name}} Team", 'title' => 'Application Update'],
                    ],
                ],
                'placeholders' => ['retailer_name', 'app_name', 'admin_message'],
            ],
            [
                'event_id' => 'retailer_changes_requested',
                'name' => 'Retailer Changes Requested',
                'description' => 'Notification when changes are requested for a retailer application',
                'category' => 'Retailer',
                'is_enabled' => true,
                'recipients' => ['retailer'],
                'channels' => ['email' => true, 'sms' => false, 'whatsapp' => false, 'push' => true],
                'templates' => [
                    'retailer' => [
                        'email' => ['subject' => 'Action Required: Changes Needed - {{app_name}}', 'body' => "Hi {{retailer_name}},\n\nYour retailer application requires some changes before we can proceed:\n\n{{admin_message}}\n\nPlease log in to your account and make the necessary updates.\n\nBest regards,\nThe {{app_name}} Team", 'title' => 'Changes Requested'],
                    ],
                ],
                'placeholders' => ['retailer_name', 'app_name', 'admin_message'],
            ],
            [
                'event_id' => 'subscription_success',
                'name' => 'Subscription Success',
                'description' => 'Notification when a subscription is successfully activated',
                'category' => 'Subscription',
                'is_enabled' => true,
                'recipients' => ['admin', 'retailer'],
                'channels' => ['email' => true, 'sms' => false, 'whatsapp' => false, 'push' => true],
                'templates' => [
                    'admin' => [
                        'email' => ['subject' => 'New Subscription - {{retailer_name}}', 'body' => "A new subscription has been activated.\n\nRetailer: {{retailer_name}}\nPlan: {{plan_name}}\nAmount: {{amount}}\nExpiry: {{expiry_date}}", 'title' => 'New Subscription'],
                    ],
                    'retailer' => [
                        'email' => ['subject' => 'Subscription Activated - {{plan_name}}', 'body' => "Hi {{retailer_name}},\n\nYour {{plan_name}} subscription is now active!\n\nYour subscription is valid until {{expiry_date}}.\n\nThank you for choosing {{app_name}}!\n\nBest regards,\nThe {{app_name}} Team", 'title' => 'Subscription Active!'],
                    ],
                ],
                'placeholders' => ['retailer_name', 'plan_name', 'amount', 'expiry_date', 'app_name'],
            ],
            [
                'event_id' => 'subscription_cancelled',
                'name' => 'Subscription Cancelled',
                'description' => 'Notification when a subscription is cancelled',
                'category' => 'Subscription',
                'is_enabled' => true,
                'recipients' => ['admin', 'retailer'],
                'channels' => ['email' => true, 'sms' => false, 'whatsapp' => false, 'push' => false],
                'templates' => [
                    'admin' => [
                        'email' => ['subject' => 'Subscription Cancelled - {{retailer_name}}', 'body' => "A subscription has been cancelled.\n\nRetailer: {{retailer_name}}\nPlan: {{plan_name}}\nEnd Date: {{end_date}}", 'title' => 'Subscription Cancelled'],
                    ],
                    'retailer' => [
                        'email' => ['subject' => 'Subscription Cancelled - {{app_name}}', 'body' => "Hi {{retailer_name}},\n\nYour {{plan_name}} subscription has been cancelled.\n\nYou will continue to have access until {{end_date}}.\n\nWe hope to see you again soon!\n\nBest regards,\nThe {{app_name}} Team", 'title' => 'Subscription Cancelled'],
                    ],
                ],
                'placeholders' => ['retailer_name', 'plan_name', 'end_date', 'app_name'],
            ],
            [
                'event_id' => 'subscription_expiring',
                'name' => 'Subscription Expiring',
                'description' => 'Reminder when subscription is about to expire',
                'category' => 'Subscription',
                'is_enabled' => true,
                'recipients' => ['retailer'],
                'channels' => ['email' => true, 'sms' => true, 'whatsapp' => false, 'push' => true],
                'templates' => [
                    'retailer' => [
                        'email' => ['subject' => 'Subscription Expiring in {{days_left}} Days - {{app_name}}', 'body' => "Hi {{retailer_name}},\n\nYour {{plan_name}} subscription will expire on {{expiry_date}} ({{days_left}} days remaining).\n\nRenew now to keep your offers visible to customers!\n\nBest regards,\nThe {{app_name}} Team", 'title' => 'Expiring Soon'],
                        'sms' => ['body' => '{{retailer_name}}, your {{plan_name}} expires in {{days_left}} days. Renew now at {{app_name}}!'],
                    ],
                ],
                'placeholders' => ['retailer_name', 'plan_name', 'expiry_date', 'days_left', 'app_name'],
            ],
            [
                'event_id' => 'branch_published',
                'name' => 'Branch Published',
                'description' => 'Notification when a branch is published',
                'category' => 'Branch',
                'is_enabled' => true,
                'recipients' => ['admin', 'retailer'],
                'channels' => ['email' => true, 'sms' => false, 'whatsapp' => false, 'push' => true],
                'templates' => [
                    'admin' => [
                        'email' => ['subject' => 'Branch Published - {{branch_name}}', 'body' => "A new branch has been published.\n\nRetailer: {{retailer_name}}\nBranch: {{branch_name}}\nAddress: {{branch_address}}", 'title' => 'Branch Published'],
                    ],
                    'retailer' => [
                        'email' => ['subject' => 'Your Branch is Now Live! - {{app_name}}', 'body' => "Hi {{retailer_name}},\n\nYour branch \"{{branch_name}}\" is now live and visible to customers!\n\nCustomers in your area will now be able to see your offers.\n\nBest regards,\nThe {{app_name}} Team", 'title' => 'Branch Live!'],
                    ],
                ],
                'placeholders' => ['retailer_name', 'branch_name', 'branch_address', 'app_name'],
            ],
            [
                'event_id' => 'nearby_shop',
                'name' => 'Nearby Shop',
                'description' => 'Notification when a customer is near a shop with offers',
                'category' => 'Customer',
                'is_enabled' => true,
                'recipients' => ['customer'],
                'channels' => ['email' => false, 'sms' => false, 'whatsapp' => false, 'push' => true],
                'templates' => [
                    'customer' => [
                        'push' => ['title' => '{{shop_name}} Nearby!', 'body' => 'You\'re {{distance}} away from {{shop_name}} with {{offer_count}} offers!'],
                    ],
                ],
                'placeholders' => ['customer_name', 'shop_name', 'distance', 'offer_count'],
            ],
        ];

        foreach ($settings as $setting) {
            NotificationSetting::updateOrCreate(
                ['event_id' => $setting['event_id']],
                $setting
            );
        }
    }
}
