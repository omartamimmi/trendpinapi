import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout';

// Icons for channels
const EmailIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
    </svg>
);

const SmsIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
    </svg>
);

const WhatsAppIcon = () => (
    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
);

const PushIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
    </svg>
);

const ChevronDownIcon = ({ isOpen }: { isOpen: boolean }) => (
    <svg
        className={`w-5 h-5 transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`}
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
    >
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
    </svg>
);

// Recipient Icons
const AdminIcon = () => (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
    </svg>
);

const RetailerIcon = () => (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
    </svg>
);

const CustomerIcon = () => (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
    </svg>
);

// Types
type RecipientType = 'admin' | 'retailer' | 'customer';
type ChannelType = 'email' | 'sms' | 'whatsapp' | 'push';

interface ChannelTemplate {
    email: { subject: string; body: string };
    sms: { body: string };
    whatsapp: { body: string };
    push: { title: string; body: string };
}

interface NotificationEvent {
    id: string;
    name: string;
    description: string;
    category: string;
    recipients: RecipientType[];
    channels: {
        email: boolean;
        sms: boolean;
        whatsapp: boolean;
        push: boolean;
    };
    isEnabled: boolean;
}

interface NotificationTemplate {
    id: string;
    eventId: string;
    name: string;
    description: string;
    category: string;
    templates: {
        admin: ChannelTemplate;
        retailer: ChannelTemplate;
        customer: ChannelTemplate;
    };
    placeholders: string[];
}

// Default notification events
const defaultEvents: NotificationEvent[] = [
    // Customer Events
    {
        id: 'new_customer',
        name: 'New Customer',
        description: 'When a new customer registers on the platform',
        category: 'Customer',
        recipients: ['admin', 'customer'],
        channels: { email: true, sms: false, whatsapp: false, push: true },
        isEnabled: true,
    },
    {
        id: 'nearby_shop',
        name: 'Nearby Shop',
        description: 'When a customer is near a shop with active offers',
        category: 'Customer',
        recipients: ['customer'],
        channels: { email: false, sms: false, whatsapp: false, push: true },
        isEnabled: true,
    },
    // Retailer Events
    {
        id: 'new_retailer',
        name: 'New Retailer',
        description: 'When a new retailer signs up for the platform',
        category: 'Retailer',
        recipients: ['admin', 'retailer'],
        channels: { email: true, sms: true, whatsapp: false, push: false },
        isEnabled: true,
    },
    {
        id: 'retailer_approved',
        name: 'Retailer Approved',
        description: 'When a retailer application is approved',
        category: 'Retailer',
        recipients: ['retailer'],
        channels: { email: true, sms: true, whatsapp: true, push: true },
        isEnabled: true,
    },
    {
        id: 'retailer_rejected',
        name: 'Retailer Rejected',
        description: 'When a retailer application is rejected',
        category: 'Retailer',
        recipients: ['retailer'],
        channels: { email: true, sms: false, whatsapp: false, push: false },
        isEnabled: true,
    },
    {
        id: 'retailer_changes_requested',
        name: 'Request Changes',
        description: 'When changes are requested on a retailer application',
        category: 'Retailer',
        recipients: ['retailer'],
        channels: { email: true, sms: false, whatsapp: true, push: true },
        isEnabled: true,
    },
    // Subscription Events
    {
        id: 'subscription_success',
        name: 'Success Subscription',
        description: 'When a subscription is successfully activated',
        category: 'Subscription',
        recipients: ['admin', 'retailer'],
        channels: { email: true, sms: true, whatsapp: true, push: true },
        isEnabled: true,
    },
    {
        id: 'subscription_cancelled',
        name: 'Cancel Subscription',
        description: 'When a subscription is cancelled',
        category: 'Subscription',
        recipients: ['admin', 'retailer'],
        channels: { email: true, sms: false, whatsapp: false, push: false },
        isEnabled: true,
    },
    {
        id: 'subscription_expiring',
        name: 'Subscription Expiring',
        description: 'Reminder before subscription expires',
        category: 'Subscription',
        recipients: ['retailer'],
        channels: { email: true, sms: true, whatsapp: true, push: true },
        isEnabled: true,
    },
    // Branch Events
    {
        id: 'branch_published',
        name: 'Published Branch',
        description: 'When a branch is published and goes live',
        category: 'Branch',
        recipients: ['admin', 'retailer'],
        channels: { email: true, sms: false, whatsapp: false, push: true },
        isEnabled: true,
    },
];

// Helper to create empty channel template
const createEmptyChannelTemplate = (): ChannelTemplate => ({
    email: { subject: '', body: '' },
    sms: { body: '' },
    whatsapp: { body: '' },
    push: { title: '', body: '' },
});

// Default templates with role-specific content
const defaultTemplates: NotificationTemplate[] = [
    {
        id: 'tpl_new_customer',
        eventId: 'new_customer',
        name: 'New Customer',
        description: 'Welcome message for new customers',
        category: 'Customer',
        templates: {
            admin: {
                email: {
                    subject: 'New Customer Registration - {{customer_name}}',
                    body: 'A new customer has registered on {{app_name}}.\n\nCustomer Details:\n- Name: {{customer_name}}\n- Email: {{customer_email}}\n- Registered: {{registration_date}}\n\nThis is an automated notification.',
                },
                sms: { body: 'New customer registered: {{customer_name}} ({{customer_email}})' },
                whatsapp: { body: 'New customer registered: {{customer_name}} ({{customer_email}})' },
                push: { title: 'New Customer', body: '{{customer_name}} just registered on the platform.' },
            },
            retailer: createEmptyChannelTemplate(),
            customer: {
                email: {
                    subject: 'Welcome to {{app_name}}!',
                    body: 'Hi {{customer_name}},\n\nWelcome to {{app_name}}! We\'re excited to have you on board.\n\nStart exploring amazing offers near you today!\n\nBest regards,\nThe {{app_name}} Team',
                },
                sms: { body: 'Welcome to {{app_name}}, {{customer_name}}! Start exploring offers near you.' },
                whatsapp: { body: 'Welcome to {{app_name}}, {{customer_name}}! Start exploring offers near you.' },
                push: { title: 'Welcome to {{app_name}}!', body: 'Start exploring amazing offers near you today!' },
            },
        },
        placeholders: ['customer_name', 'customer_email', 'app_name', 'registration_date'],
    },
    {
        id: 'tpl_nearby_shop',
        eventId: 'nearby_shop',
        name: 'Nearby Shop',
        description: 'Notification when customer is near a shop',
        category: 'Customer',
        templates: {
            admin: createEmptyChannelTemplate(),
            retailer: createEmptyChannelTemplate(),
            customer: {
                email: {
                    subject: '{{shop_name}} is nearby!',
                    body: 'Hi {{customer_name}},\n\n{{shop_name}} is just {{distance}} away from you!\n\nThey have {{offer_count}} active offers waiting for you.\n\nCheck them out now!',
                },
                sms: { body: '{{shop_name}} is {{distance}} away! Check out {{offer_count}} offers.' },
                whatsapp: { body: '{{shop_name}} is {{distance}} away! Check out {{offer_count}} offers.' },
                push: { title: '{{shop_name}} is nearby!', body: '{{offer_count}} offers waiting for you, just {{distance}} away.' },
            },
        },
        placeholders: ['customer_name', 'shop_name', 'distance', 'offer_count'],
    },
    {
        id: 'tpl_new_retailer',
        eventId: 'new_retailer',
        name: 'New Retailer',
        description: 'Welcome message for new retailers',
        category: 'Retailer',
        templates: {
            admin: {
                email: {
                    subject: 'New Retailer Application - {{retailer_name}}',
                    body: 'A new retailer has submitted an application.\n\nRetailer Details:\n- Name: {{retailer_name}}\n- Email: {{retailer_email}}\n- Business: {{business_name}}\n- Submitted: {{submission_date}}\n\nPlease review the application in the admin dashboard.',
                },
                sms: { body: 'New retailer application: {{retailer_name}} - {{business_name}}. Review needed.' },
                whatsapp: { body: 'New retailer application: {{retailer_name}} - {{business_name}}. Review needed.' },
                push: { title: 'New Retailer Application', body: '{{retailer_name}} submitted an application. Review required.' },
            },
            retailer: {
                email: {
                    subject: 'Welcome to {{app_name}} - Application Received',
                    body: 'Hi {{retailer_name}},\n\nThank you for applying to join {{app_name}} as a retailer.\n\nWe have received your application and our team will review it shortly.\n\nYou will be notified once your application is processed.\n\nBest regards,\nThe {{app_name}} Team',
                },
                sms: { body: 'Welcome to {{app_name}}! Your retailer application has been received and is under review.' },
                whatsapp: { body: 'Welcome to {{app_name}}! Your retailer application has been received and is under review.' },
                push: { title: 'Application Received', body: 'Your retailer application is under review.' },
            },
            customer: createEmptyChannelTemplate(),
        },
        placeholders: ['retailer_name', 'retailer_email', 'business_name', 'app_name', 'submission_date'],
    },
    {
        id: 'tpl_retailer_approved',
        eventId: 'retailer_approved',
        name: 'Retailer Approved',
        description: 'Notification when retailer is approved',
        category: 'Retailer',
        templates: {
            admin: createEmptyChannelTemplate(),
            retailer: {
                email: {
                    subject: 'Congratulations! Your {{app_name}} Retailer Account is Approved',
                    body: 'Hi {{retailer_name}},\n\nGreat news! Your retailer application has been approved.\n\nYou can now log in to your dashboard and start adding your branches and offers.\n\nWelcome aboard!\n\nBest regards,\nThe {{app_name}} Team',
                },
                sms: { body: 'Congratulations {{retailer_name}}! Your {{app_name}} retailer account is approved. Start adding your offers now!' },
                whatsapp: { body: 'Congratulations {{retailer_name}}! Your {{app_name}} retailer account is approved. Start adding your offers now!' },
                push: { title: 'Account Approved!', body: 'Your retailer account is now active. Start adding offers!' },
            },
            customer: createEmptyChannelTemplate(),
        },
        placeholders: ['retailer_name', 'app_name'],
    },
    {
        id: 'tpl_retailer_rejected',
        eventId: 'retailer_rejected',
        name: 'Retailer Rejected',
        description: 'Notification when retailer application is rejected',
        category: 'Retailer',
        templates: {
            admin: createEmptyChannelTemplate(),
            retailer: {
                email: {
                    subject: 'Update on Your {{app_name}} Retailer Application',
                    body: 'Hi {{retailer_name}},\n\nThank you for your interest in joining {{app_name}}.\n\nUnfortunately, we are unable to approve your application at this time.\n\nReason:\n{{admin_message}}\n\nIf you have any questions, please contact our support team.\n\nBest regards,\nThe {{app_name}} Team',
                },
                sms: { body: '{{app_name}}: Your application was not approved. Reason: {{admin_message}}' },
                whatsapp: { body: 'Hi {{retailer_name}},\n\nYour {{app_name}} retailer application was not approved.\n\nReason: {{admin_message}}\n\nContact support if you have questions.' },
                push: { title: 'Application Not Approved', body: 'Reason: {{admin_message}}' },
            },
            customer: createEmptyChannelTemplate(),
        },
        placeholders: ['retailer_name', 'app_name', 'admin_message'],
    },
    {
        id: 'tpl_retailer_changes_requested',
        eventId: 'retailer_changes_requested',
        name: 'Request Changes',
        description: 'Notification when changes are requested',
        category: 'Retailer',
        templates: {
            admin: createEmptyChannelTemplate(),
            retailer: {
                email: {
                    subject: 'Action Required: Changes Needed for Your {{app_name}} Application',
                    body: 'Hi {{retailer_name}},\n\nWe have reviewed your retailer application and need some changes before we can proceed.\n\nRequested Changes:\n{{admin_message}}\n\nPlease log in to your account and update your application.\n\nBest regards,\nThe {{app_name}} Team',
                },
                sms: { body: '{{app_name}}: Changes needed for your application: {{admin_message}}' },
                whatsapp: { body: 'Hi {{retailer_name}},\n\nChanges are needed for your {{app_name}} application:\n\n{{admin_message}}\n\nPlease update your application.' },
                push: { title: 'Changes Requested', body: '{{admin_message}}' },
            },
            customer: createEmptyChannelTemplate(),
        },
        placeholders: ['retailer_name', 'app_name', 'admin_message'],
    },
    {
        id: 'tpl_subscription_success',
        eventId: 'subscription_success',
        name: 'Success Subscription',
        description: 'Notification when subscription is activated',
        category: 'Subscription',
        templates: {
            admin: {
                email: {
                    subject: 'New Subscription Activated - {{retailer_name}}',
                    body: 'A new subscription has been activated.\n\nDetails:\n- Retailer: {{retailer_name}}\n- Plan: {{plan_name}}\n- Amount: {{amount}}\n- Valid Until: {{expiry_date}}\n\nThis is an automated notification.',
                },
                sms: { body: 'New subscription: {{retailer_name}} activated {{plan_name}} ({{amount}})' },
                whatsapp: { body: 'New subscription: {{retailer_name}} activated {{plan_name}} ({{amount}})' },
                push: { title: 'New Subscription', body: '{{retailer_name}} activated {{plan_name}}' },
            },
            retailer: {
                email: {
                    subject: 'Subscription Activated - {{plan_name}}',
                    body: 'Hi {{retailer_name}},\n\nYour subscription to {{plan_name}} has been successfully activated!\n\nSubscription Details:\n- Plan: {{plan_name}}\n- Amount: {{amount}}\n- Valid Until: {{expiry_date}}\n\nEnjoy all the premium features!\n\nBest regards,\nThe {{app_name}} Team',
                },
                sms: { body: '{{retailer_name}}, your {{plan_name}} subscription is now active! Valid until {{expiry_date}}.' },
                whatsapp: { body: '{{retailer_name}}, your {{plan_name}} subscription is now active! Valid until {{expiry_date}}.' },
                push: { title: 'Subscription Activated!', body: '{{plan_name}} is now active. Valid until {{expiry_date}}.' },
            },
            customer: createEmptyChannelTemplate(),
        },
        placeholders: ['retailer_name', 'plan_name', 'amount', 'expiry_date', 'app_name'],
    },
    {
        id: 'tpl_subscription_cancelled',
        eventId: 'subscription_cancelled',
        name: 'Cancel Subscription',
        description: 'Notification when subscription is cancelled',
        category: 'Subscription',
        templates: {
            admin: {
                email: {
                    subject: 'Subscription Cancelled - {{retailer_name}}',
                    body: 'A subscription has been cancelled.\n\nDetails:\n- Retailer: {{retailer_name}}\n- Plan: {{plan_name}}\n- Access Until: {{end_date}}\n\nThis is an automated notification.',
                },
                sms: { body: 'Subscription cancelled: {{retailer_name}} - {{plan_name}}' },
                whatsapp: { body: 'Subscription cancelled: {{retailer_name}} - {{plan_name}}' },
                push: { title: 'Subscription Cancelled', body: '{{retailer_name}} cancelled {{plan_name}}' },
            },
            retailer: {
                email: {
                    subject: 'Subscription Cancelled - {{plan_name}}',
                    body: 'Hi {{retailer_name}},\n\nYour subscription to {{plan_name}} has been cancelled.\n\nYou will continue to have access until {{end_date}}.\n\nWe\'d love to have you back! If you change your mind, you can resubscribe anytime.\n\nBest regards,\nThe {{app_name}} Team',
                },
                sms: { body: '{{retailer_name}}, your {{plan_name}} subscription has been cancelled. Access until {{end_date}}.' },
                whatsapp: { body: '{{retailer_name}}, your {{plan_name}} subscription has been cancelled. Access until {{end_date}}.' },
                push: { title: 'Subscription Cancelled', body: 'Your {{plan_name}} access ends on {{end_date}}.' },
            },
            customer: createEmptyChannelTemplate(),
        },
        placeholders: ['retailer_name', 'plan_name', 'end_date', 'app_name'],
    },
    {
        id: 'tpl_subscription_expiring',
        eventId: 'subscription_expiring',
        name: 'Subscription Expiring',
        description: 'Reminder before subscription expires',
        category: 'Subscription',
        templates: {
            admin: createEmptyChannelTemplate(),
            retailer: {
                email: {
                    subject: 'Your {{plan_name}} Subscription Expires in {{days_left}} Days',
                    body: 'Hi {{retailer_name}},\n\nYour {{plan_name}} subscription will expire on {{expiry_date}}.\n\nRenew now to continue enjoying all features without interruption.\n\nBest regards,\nThe {{app_name}} Team',
                },
                sms: { body: '{{retailer_name}}, your {{plan_name}} expires in {{days_left}} days. Renew now!' },
                whatsapp: { body: '{{retailer_name}}, your {{plan_name}} expires in {{days_left}} days. Renew now!' },
                push: { title: 'Subscription Expiring Soon', body: '{{plan_name}} expires in {{days_left}} days. Renew now!' },
            },
            customer: createEmptyChannelTemplate(),
        },
        placeholders: ['retailer_name', 'plan_name', 'expiry_date', 'days_left', 'app_name'],
    },
    {
        id: 'tpl_branch_published',
        eventId: 'branch_published',
        name: 'Published Branch',
        description: 'Notification when a branch goes live',
        category: 'Branch',
        templates: {
            admin: {
                email: {
                    subject: 'New Branch Published - {{branch_name}}',
                    body: 'A new branch has been published.\n\nDetails:\n- Retailer: {{retailer_name}}\n- Branch: {{branch_name}}\n- Address: {{branch_address}}\n\nThis is an automated notification.',
                },
                sms: { body: 'New branch published: {{branch_name}} by {{retailer_name}}' },
                whatsapp: { body: 'New branch published: {{branch_name}} by {{retailer_name}}' },
                push: { title: 'New Branch Published', body: '{{retailer_name}} published {{branch_name}}' },
            },
            retailer: {
                email: {
                    subject: 'Your Branch "{{branch_name}}" is Now Live!',
                    body: 'Hi {{retailer_name}},\n\nGreat news! Your branch "{{branch_name}}" is now published and visible to customers.\n\nBranch Details:\n- Name: {{branch_name}}\n- Address: {{branch_address}}\n\nStart creating offers to attract more customers!\n\nBest regards,\nThe {{app_name}} Team',
                },
                sms: { body: '{{retailer_name}}, your branch "{{branch_name}}" is now live! Start adding offers.' },
                whatsapp: { body: '{{retailer_name}}, your branch "{{branch_name}}" is now live! Start adding offers.' },
                push: { title: 'Branch Published!', body: '"{{branch_name}}" is now visible to customers.' },
            },
            customer: createEmptyChannelTemplate(),
        },
        placeholders: ['retailer_name', 'branch_name', 'branch_address', 'app_name'],
    },
];

// Toggle Switch Component
const ToggleSwitch = ({
    enabled,
    onChange,
    size = 'md'
}: {
    enabled: boolean;
    onChange: () => void;
    size?: 'sm' | 'md';
}) => {
    const sizeClasses = {
        sm: { track: 'w-8 h-4', thumb: 'w-3 h-3', translate: 'translate-x-4' },
        md: { track: 'w-11 h-6', thumb: 'w-5 h-5', translate: 'translate-x-5' },
    };
    const s = sizeClasses[size];

    return (
        <button
            type="button"
            onClick={onChange}
            className={`relative inline-flex ${s.track} flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 ${
                enabled ? 'bg-pink-500' : 'bg-gray-200'
            }`}
        >
            <span
                className={`pointer-events-none inline-block ${s.thumb} transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${
                    enabled ? s.translate : 'translate-x-0'
                }`}
            />
        </button>
    );
};

// Channel Toggle Cell Component
const ChannelToggle = ({
    enabled,
    eventEnabled,
    onChange,
    icon: Icon
}: {
    enabled: boolean;
    eventEnabled: boolean;
    onChange: () => void;
    icon: React.ComponentType;
}) => (
    <div className="flex items-center justify-center">
        <button
            onClick={onChange}
            disabled={!eventEnabled}
            className={`p-2 rounded-lg transition-all duration-200 ${
                !eventEnabled
                    ? 'bg-gray-100 text-gray-300 cursor-not-allowed'
                    : enabled
                        ? 'bg-pink-100 text-pink-600 hover:bg-pink-200'
                        : 'bg-gray-100 text-gray-400 hover:bg-gray-200'
            }`}
        >
            <Icon />
        </button>
    </div>
);

// Recipient Chip Component
const RecipientChip = ({
    type,
    selected,
    disabled,
    onClick
}: {
    type: RecipientType;
    selected: boolean;
    disabled: boolean;
    onClick: () => void;
}) => {
    const config = {
        admin: { label: 'Admin', icon: AdminIcon, color: 'purple' },
        retailer: { label: 'Retailer', icon: RetailerIcon, color: 'blue' },
        customer: { label: 'Customer', icon: CustomerIcon, color: 'green' },
    };

    const { label, icon: Icon, color } = config[type];

    const colorClasses = {
        purple: selected ? 'bg-purple-100 text-purple-700 border-purple-300' : 'bg-gray-50 text-gray-400 border-gray-200',
        blue: selected ? 'bg-blue-100 text-blue-700 border-blue-300' : 'bg-gray-50 text-gray-400 border-gray-200',
        green: selected ? 'bg-green-100 text-green-700 border-green-300' : 'bg-gray-50 text-gray-400 border-gray-200',
    };

    return (
        <button
            onClick={onClick}
            disabled={disabled}
            className={`inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-full border transition-all duration-200 ${
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:shadow-sm'
            } ${colorClasses[color]}`}
        >
            <Icon />
            {label}
        </button>
    );
};

export default function NotificationSettings() {
    const [events, setEvents] = useState<NotificationEvent[]>(defaultEvents);
    const [templates, setTemplates] = useState<NotificationTemplate[]>(defaultTemplates);
    const [expandedTemplates, setExpandedTemplates] = useState<string[]>([]);
    const [activeTab, setActiveTab] = useState<'events' | 'templates'>('events');
    const [hasUnsavedChanges, setHasUnsavedChanges] = useState(false);
    const [selectedTemplateRole, setSelectedTemplateRole] = useState<Record<string, RecipientType>>({});
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    // Load settings from API on mount
    useEffect(() => {
        const loadSettings = async () => {
            try {
                const response = await axios.get('/api/v1/admin/notification-settings');
                if (response.data.success && response.data.data) {
                    const { events: loadedEvents, templates: loadedTemplates } = response.data.data;

                    if (loadedEvents && loadedEvents.length > 0) {
                        setEvents(loadedEvents);
                    }
                    if (loadedTemplates && loadedTemplates.length > 0) {
                        setTemplates(loadedTemplates);
                    }
                }
            } catch (error) {
                console.error('Failed to load notification settings:', error);
                // Keep defaults if API fails
            } finally {
                setLoading(false);
            }
        };

        loadSettings();
    }, []);

    // Group events by category
    const eventsByCategory = events.reduce((acc, event) => {
        if (!acc[event.category]) acc[event.category] = [];
        acc[event.category].push(event);
        return acc;
    }, {} as Record<string, NotificationEvent[]>);

    // Group templates by category
    const templatesByCategory = templates.reduce((acc, template) => {
        if (!acc[template.category]) acc[template.category] = [];
        acc[template.category].push(template);
        return acc;
    }, {} as Record<string, NotificationTemplate[]>);

    const toggleEvent = (eventId: string) => {
        setEvents(prev => prev.map(e =>
            e.id === eventId ? { ...e, isEnabled: !e.isEnabled } : e
        ));
        setHasUnsavedChanges(true);
    };

    const toggleChannel = (eventId: string, channel: ChannelType) => {
        setEvents(prev => prev.map(e =>
            e.id === eventId
                ? { ...e, channels: { ...e.channels, [channel]: !e.channels[channel] } }
                : e
        ));
        setHasUnsavedChanges(true);
    };

    const toggleRecipient = (eventId: string, recipient: RecipientType) => {
        setEvents(prev => prev.map(e => {
            if (e.id !== eventId) return e;
            const newRecipients = e.recipients.includes(recipient)
                ? e.recipients.filter(r => r !== recipient)
                : [...e.recipients, recipient];
            return { ...e, recipients: newRecipients };
        }));
        setHasUnsavedChanges(true);
    };

    const toggleTemplateExpand = (templateId: string) => {
        setExpandedTemplates(prev =>
            prev.includes(templateId)
                ? prev.filter(id => id !== templateId)
                : [...prev, templateId]
        );
    };

    const getSelectedRole = (templateId: string, event: NotificationEvent | undefined): RecipientType => {
        if (selectedTemplateRole[templateId]) {
            return selectedTemplateRole[templateId];
        }
        // Default to first available recipient
        if (event && event.recipients.length > 0) {
            return event.recipients[0];
        }
        return 'customer';
    };

    const setTemplateRole = (templateId: string, role: RecipientType) => {
        setSelectedTemplateRole(prev => ({ ...prev, [templateId]: role }));
    };

    const updateTemplate = (
        templateId: string,
        role: RecipientType,
        channel: ChannelType,
        field: string,
        value: string
    ) => {
        setTemplates(prev => prev.map(t => {
            if (t.id !== templateId) return t;
            return {
                ...t,
                templates: {
                    ...t.templates,
                    [role]: {
                        ...t.templates[role],
                        [channel]: {
                            ...t.templates[role][channel],
                            [field]: value,
                        },
                    },
                },
            };
        }));
        setHasUnsavedChanges(true);
    };

    const handleSave = async () => {
        setSaving(true);
        try {
            const response = await axios.post('/api/v1/admin/notification-settings', {
                events,
                templates,
            });

            if (response.data.success) {
                setHasUnsavedChanges(false);
                alert('Settings saved successfully!');
            } else {
                alert(response.data.message || 'Failed to save settings');
            }
        } catch (error: any) {
            console.error('Failed to save settings:', error);
            alert(error.response?.data?.message || 'Failed to save settings. Please try again.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <AdminLayout>
            <div className="max-w-7xl mx-auto">
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Notification Settings</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            Configure notification events, recipients, channels, and message templates
                        </p>
                    </div>
                    <div className="flex gap-3">
                        {hasUnsavedChanges && (
                            <span className="flex items-center text-sm text-amber-600">
                                <svg className="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                                </svg>
                                Unsaved changes
                            </span>
                        )}
                        <button
                            onClick={handleSave}
                            disabled={!hasUnsavedChanges || saving}
                            className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                                hasUnsavedChanges && !saving
                                    ? 'bg-pink-500 text-white hover:bg-pink-600'
                                    : 'bg-gray-200 text-gray-400 cursor-not-allowed'
                            }`}
                        >
                            {saving ? 'Saving...' : 'Save Changes'}
                        </button>
                    </div>
                </div>

                {/* Tabs */}
                <div className="bg-white rounded-lg shadow mb-6">
                    <div className="border-b border-gray-200">
                        <nav className="flex -mb-px">
                            <button
                                onClick={() => setActiveTab('events')}
                                className={`px-6 py-4 text-sm font-medium border-b-2 transition-colors ${
                                    activeTab === 'events'
                                        ? 'border-pink-500 text-pink-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                Events & Channels
                            </button>
                            <button
                                onClick={() => setActiveTab('templates')}
                                className={`px-6 py-4 text-sm font-medium border-b-2 transition-colors ${
                                    activeTab === 'templates'
                                        ? 'border-pink-500 text-pink-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                Message Templates
                            </button>
                        </nav>
                    </div>
                </div>

                {/* Events & Channels Tab */}
                {activeTab === 'events' && (
                    <div className="space-y-6">
                        {Object.entries(eventsByCategory).map(([category, categoryEvents]) => (
                            <div key={category} className="bg-white rounded-lg shadow overflow-hidden">
                                <div className="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                    <h3 className="text-lg font-semibold text-gray-900">{category} Events</h3>
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">
                                                    Event
                                                </th>
                                                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                                    Enabled
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Send To
                                                </th>
                                                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                                                    <div className="flex flex-col items-center gap-1">
                                                        <EmailIcon />
                                                        <span className="text-[10px]">Email</span>
                                                    </div>
                                                </th>
                                                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                                                    <div className="flex flex-col items-center gap-1">
                                                        <SmsIcon />
                                                        <span className="text-[10px]">SMS</span>
                                                    </div>
                                                </th>
                                                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                                                    <div className="flex flex-col items-center gap-1">
                                                        <WhatsAppIcon />
                                                        <span className="text-[10px]">WhatsApp</span>
                                                    </div>
                                                </th>
                                                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                                                    <div className="flex flex-col items-center gap-1">
                                                        <PushIcon />
                                                        <span className="text-[10px]">Push</span>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {categoryEvents.map((event) => (
                                                <tr key={event.id} className={`transition-colors ${!event.isEnabled ? 'bg-gray-50' : 'hover:bg-gray-50'}`}>
                                                    <td className="px-6 py-4">
                                                        <div className={`font-medium ${event.isEnabled ? 'text-gray-900' : 'text-gray-400'}`}>
                                                            {event.name}
                                                        </div>
                                                        <div className={`text-sm ${event.isEnabled ? 'text-gray-500' : 'text-gray-400'}`}>
                                                            {event.description}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <div className="flex justify-center">
                                                            <ToggleSwitch
                                                                enabled={event.isEnabled}
                                                                onChange={() => toggleEvent(event.id)}
                                                            />
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <div className="flex flex-wrap gap-2">
                                                            <RecipientChip
                                                                type="admin"
                                                                selected={event.recipients.includes('admin')}
                                                                disabled={!event.isEnabled}
                                                                onClick={() => toggleRecipient(event.id, 'admin')}
                                                            />
                                                            <RecipientChip
                                                                type="retailer"
                                                                selected={event.recipients.includes('retailer')}
                                                                disabled={!event.isEnabled}
                                                                onClick={() => toggleRecipient(event.id, 'retailer')}
                                                            />
                                                            <RecipientChip
                                                                type="customer"
                                                                selected={event.recipients.includes('customer')}
                                                                disabled={!event.isEnabled}
                                                                onClick={() => toggleRecipient(event.id, 'customer')}
                                                            />
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <ChannelToggle
                                                            enabled={event.channels.email}
                                                            eventEnabled={event.isEnabled}
                                                            onChange={() => toggleChannel(event.id, 'email')}
                                                            icon={EmailIcon}
                                                        />
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <ChannelToggle
                                                            enabled={event.channels.sms}
                                                            eventEnabled={event.isEnabled}
                                                            onChange={() => toggleChannel(event.id, 'sms')}
                                                            icon={SmsIcon}
                                                        />
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <ChannelToggle
                                                            enabled={event.channels.whatsapp}
                                                            eventEnabled={event.isEnabled}
                                                            onChange={() => toggleChannel(event.id, 'whatsapp')}
                                                            icon={WhatsAppIcon}
                                                        />
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <ChannelToggle
                                                            enabled={event.channels.push}
                                                            eventEnabled={event.isEnabled}
                                                            onChange={() => toggleChannel(event.id, 'push')}
                                                            icon={PushIcon}
                                                        />
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {/* Templates Tab */}
                {activeTab === 'templates' && (
                    <div className="space-y-6">
                        {Object.entries(templatesByCategory).map(([category, categoryTemplates]) => (
                            <div key={category}>
                                <h3 className="text-lg font-semibold text-gray-900 mb-3">{category} Templates</h3>
                                <div className="space-y-3">
                                    {categoryTemplates.map((template) => {
                                        const isExpanded = expandedTemplates.includes(template.id);
                                        const event = events.find(e => e.id === template.eventId);
                                        const selectedRole = getSelectedRole(template.id, event);
                                        const roleTemplate = template.templates[selectedRole];

                                        return (
                                            <div
                                                key={template.id}
                                                className={`bg-white rounded-lg shadow overflow-hidden transition-all duration-200 ${
                                                    !event?.isEnabled ? 'opacity-60' : ''
                                                }`}
                                            >
                                                {/* Accordion Header */}
                                                <button
                                                    onClick={() => toggleTemplateExpand(template.id)}
                                                    className="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors"
                                                >
                                                    <div className="flex items-center gap-4">
                                                        <div className="flex-shrink-0">
                                                            <div className={`w-10 h-10 rounded-lg flex items-center justify-center ${
                                                                event?.isEnabled ? 'bg-pink-100 text-pink-600' : 'bg-gray-100 text-gray-400'
                                                            }`}>
                                                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                        <div className="text-left">
                                                            <div className="font-medium text-gray-900">{template.name}</div>
                                                            <div className="text-sm text-gray-500">{template.description}</div>
                                                        </div>
                                                    </div>
                                                    <div className="flex items-center gap-4">
                                                        {/* Recipient indicators */}
                                                        <div className="flex gap-1">
                                                            {event?.recipients.includes('admin') && (
                                                                <span className="p-1.5 rounded bg-purple-100 text-purple-600" title="Admin">
                                                                    <AdminIcon />
                                                                </span>
                                                            )}
                                                            {event?.recipients.includes('retailer') && (
                                                                <span className="p-1.5 rounded bg-blue-100 text-blue-600" title="Retailer">
                                                                    <RetailerIcon />
                                                                </span>
                                                            )}
                                                            {event?.recipients.includes('customer') && (
                                                                <span className="p-1.5 rounded bg-green-100 text-green-600" title="Customer">
                                                                    <CustomerIcon />
                                                                </span>
                                                            )}
                                                        </div>
                                                        {/* Channel indicators */}
                                                        <div className="flex gap-1 border-l border-gray-200 pl-4">
                                                            {event?.channels.email && (
                                                                <span className="p-1.5 rounded bg-gray-100 text-gray-600">
                                                                    <EmailIcon />
                                                                </span>
                                                            )}
                                                            {event?.channels.sms && (
                                                                <span className="p-1.5 rounded bg-gray-100 text-gray-600">
                                                                    <SmsIcon />
                                                                </span>
                                                            )}
                                                            {event?.channels.whatsapp && (
                                                                <span className="p-1.5 rounded bg-gray-100 text-gray-600">
                                                                    <WhatsAppIcon />
                                                                </span>
                                                            )}
                                                            {event?.channels.push && (
                                                                <span className="p-1.5 rounded bg-gray-100 text-gray-600">
                                                                    <PushIcon />
                                                                </span>
                                                            )}
                                                        </div>
                                                        <ChevronDownIcon isOpen={isExpanded} />
                                                    </div>
                                                </button>

                                                {/* Accordion Content */}
                                                {isExpanded && (
                                                    <div className="px-6 pb-6 border-t border-gray-100">
                                                        {/* Role Selector Tabs */}
                                                        {event && event.recipients.length > 0 && (
                                                            <div className="mt-4 mb-4">
                                                                <div className="flex items-center gap-2 mb-2">
                                                                    <span className="text-sm font-medium text-gray-700">Template for:</span>
                                                                </div>
                                                                <div className="flex gap-2 border-b border-gray-200">
                                                                    {event.recipients.includes('admin') && (
                                                                        <button
                                                                            onClick={() => setTemplateRole(template.id, 'admin')}
                                                                            className={`px-4 py-2 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                                                                selectedRole === 'admin'
                                                                                    ? 'border-purple-500 text-purple-600'
                                                                                    : 'border-transparent text-gray-500 hover:text-gray-700'
                                                                            }`}
                                                                        >
                                                                            <AdminIcon />
                                                                            Admin
                                                                        </button>
                                                                    )}
                                                                    {event.recipients.includes('retailer') && (
                                                                        <button
                                                                            onClick={() => setTemplateRole(template.id, 'retailer')}
                                                                            className={`px-4 py-2 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                                                                selectedRole === 'retailer'
                                                                                    ? 'border-blue-500 text-blue-600'
                                                                                    : 'border-transparent text-gray-500 hover:text-gray-700'
                                                                            }`}
                                                                        >
                                                                            <RetailerIcon />
                                                                            Retailer
                                                                        </button>
                                                                    )}
                                                                    {event.recipients.includes('customer') && (
                                                                        <button
                                                                            onClick={() => setTemplateRole(template.id, 'customer')}
                                                                            className={`px-4 py-2 text-sm font-medium border-b-2 transition-colors flex items-center gap-2 ${
                                                                                selectedRole === 'customer'
                                                                                    ? 'border-green-500 text-green-600'
                                                                                    : 'border-transparent text-gray-500 hover:text-gray-700'
                                                                            }`}
                                                                        >
                                                                            <CustomerIcon />
                                                                            Customer
                                                                        </button>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        )}

                                                        {/* Placeholders Info */}
                                                        <div className="mb-6 p-4 bg-gray-50 rounded-lg">
                                                            <div className="text-sm font-medium text-gray-700 mb-2">Available Placeholders:</div>
                                                            <div className="flex flex-wrap gap-2">
                                                                {template.placeholders.map((placeholder) => (
                                                                    <code
                                                                        key={placeholder}
                                                                        className="px-2 py-1 bg-white border border-gray-200 rounded text-sm text-pink-600"
                                                                    >
                                                                        {`{{${placeholder}}}`}
                                                                    </code>
                                                                ))}
                                                            </div>
                                                        </div>

                                                        {/* Channel Templates */}
                                                        <div className="grid gap-4">
                                                            {/* Email Template */}
                                                            {event?.channels.email && (
                                                                <div className="border border-gray-200 rounded-lg overflow-hidden">
                                                                    <div className="px-4 py-3 bg-blue-50 border-b border-gray-200 flex items-center gap-2">
                                                                        <EmailIcon />
                                                                        <span className="font-medium text-gray-900">Email Template</span>
                                                                        <span className={`ml-auto text-xs px-2 py-0.5 rounded-full ${
                                                                            selectedRole === 'admin' ? 'bg-purple-100 text-purple-700' :
                                                                            selectedRole === 'retailer' ? 'bg-blue-100 text-blue-700' :
                                                                            'bg-green-100 text-green-700'
                                                                        }`}>
                                                                            {selectedRole.charAt(0).toUpperCase() + selectedRole.slice(1)}
                                                                        </span>
                                                                    </div>
                                                                    <div className="p-4 space-y-4">
                                                                        <div>
                                                                            <label className="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                                                            <input
                                                                                type="text"
                                                                                value={roleTemplate.email.subject}
                                                                                onChange={(e) => updateTemplate(template.id, selectedRole, 'email', 'subject', e.target.value)}
                                                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                                                            />
                                                                        </div>
                                                                        <div>
                                                                            <label className="block text-sm font-medium text-gray-700 mb-1">Body</label>
                                                                            <textarea
                                                                                value={roleTemplate.email.body}
                                                                                onChange={(e) => updateTemplate(template.id, selectedRole, 'email', 'body', e.target.value)}
                                                                                rows={5}
                                                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            )}

                                                            {/* SMS Template */}
                                                            {event?.channels.sms && (
                                                                <div className="border border-gray-200 rounded-lg overflow-hidden">
                                                                    <div className="px-4 py-3 bg-green-50 border-b border-gray-200 flex items-center gap-2">
                                                                        <SmsIcon />
                                                                        <span className="font-medium text-gray-900">SMS Template</span>
                                                                        <span className={`ml-2 text-xs px-2 py-0.5 rounded-full ${
                                                                            selectedRole === 'admin' ? 'bg-purple-100 text-purple-700' :
                                                                            selectedRole === 'retailer' ? 'bg-blue-100 text-blue-700' :
                                                                            'bg-green-100 text-green-700'
                                                                        }`}>
                                                                            {selectedRole.charAt(0).toUpperCase() + selectedRole.slice(1)}
                                                                        </span>
                                                                        <span className="ml-auto text-xs text-gray-500">
                                                                            {roleTemplate.sms.body.length}/160 characters
                                                                        </span>
                                                                    </div>
                                                                    <div className="p-4">
                                                                        <textarea
                                                                            value={roleTemplate.sms.body}
                                                                            onChange={(e) => updateTemplate(template.id, selectedRole, 'sms', 'body', e.target.value)}
                                                                            rows={3}
                                                                            maxLength={160}
                                                                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                                                        />
                                                                    </div>
                                                                </div>
                                                            )}

                                                            {/* WhatsApp Template */}
                                                            {event?.channels.whatsapp && (
                                                                <div className="border border-gray-200 rounded-lg overflow-hidden">
                                                                    <div className="px-4 py-3 bg-emerald-50 border-b border-gray-200 flex items-center gap-2">
                                                                        <WhatsAppIcon />
                                                                        <span className="font-medium text-gray-900">WhatsApp Template</span>
                                                                        <span className={`ml-auto text-xs px-2 py-0.5 rounded-full ${
                                                                            selectedRole === 'admin' ? 'bg-purple-100 text-purple-700' :
                                                                            selectedRole === 'retailer' ? 'bg-blue-100 text-blue-700' :
                                                                            'bg-green-100 text-green-700'
                                                                        }`}>
                                                                            {selectedRole.charAt(0).toUpperCase() + selectedRole.slice(1)}
                                                                        </span>
                                                                    </div>
                                                                    <div className="p-4">
                                                                        <textarea
                                                                            value={roleTemplate.whatsapp.body}
                                                                            onChange={(e) => updateTemplate(template.id, selectedRole, 'whatsapp', 'body', e.target.value)}
                                                                            rows={3}
                                                                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                                                        />
                                                                    </div>
                                                                </div>
                                                            )}

                                                            {/* Push Template */}
                                                            {event?.channels.push && (
                                                                <div className="border border-gray-200 rounded-lg overflow-hidden">
                                                                    <div className="px-4 py-3 bg-purple-50 border-b border-gray-200 flex items-center gap-2">
                                                                        <PushIcon />
                                                                        <span className="font-medium text-gray-900">Push Notification Template</span>
                                                                        <span className={`ml-auto text-xs px-2 py-0.5 rounded-full ${
                                                                            selectedRole === 'admin' ? 'bg-purple-100 text-purple-700' :
                                                                            selectedRole === 'retailer' ? 'bg-blue-100 text-blue-700' :
                                                                            'bg-green-100 text-green-700'
                                                                        }`}>
                                                                            {selectedRole.charAt(0).toUpperCase() + selectedRole.slice(1)}
                                                                        </span>
                                                                    </div>
                                                                    <div className="p-4 space-y-4">
                                                                        <div>
                                                                            <label className="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                                                            <input
                                                                                type="text"
                                                                                value={roleTemplate.push.title}
                                                                                onChange={(e) => updateTemplate(template.id, selectedRole, 'push', 'title', e.target.value)}
                                                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                                                            />
                                                                        </div>
                                                                        <div>
                                                                            <label className="block text-sm font-medium text-gray-700 mb-1">Body</label>
                                                                            <textarea
                                                                                value={roleTemplate.push.body}
                                                                                onChange={(e) => updateTemplate(template.id, selectedRole, 'push', 'body', e.target.value)}
                                                                                rows={2}
                                                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
