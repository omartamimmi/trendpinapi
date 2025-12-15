import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import axios from 'axios';

interface Config {
    radar_secret_key: string;
    radar_publishable_key: string;
    radar_webhook_secret: string;
    max_per_day: number;
    max_per_week: number;
    min_interval_minutes: number;
    brand_cooldown_hours: number;
    location_cooldown_hours: number;
    offer_cooldown_hours: number;
    quiet_hours_enabled: boolean;
    quiet_hours_start: string;
    quiet_hours_end: string;
}

interface Props {
    config: Config;
}

// Icons
const KeyIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
    </svg>
);

const ClockIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
);

const BellIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
    </svg>
);

const MoonIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>
);

const SyncIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
    </svg>
);

// Toggle Switch Component
const ToggleSwitch = ({
    enabled,
    onChange,
}: {
    enabled: boolean;
    onChange: () => void;
}) => (
    <button
        type="button"
        onClick={onChange}
        className={`relative inline-flex w-11 h-6 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 ${
            enabled ? 'bg-pink-500' : 'bg-gray-200'
        }`}
    >
        <span
            className={`pointer-events-none inline-block w-5 h-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${
                enabled ? 'translate-x-5' : 'translate-x-0'
            }`}
        />
    </button>
);

export default function GeofenceSettings({ config }: Props) {
    const [formData, setFormData] = useState<Config>(config);
    const [saving, setSaving] = useState(false);
    const [syncing, setSyncing] = useState(false);
    const [hasChanges, setHasChanges] = useState(false);
    const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

    const handleChange = (field: keyof Config, value: string | number | boolean) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        setHasChanges(true);
    };

    const handleSave = async () => {
        setSaving(true);
        setMessage(null);

        try {
            const response = await axios.post('/admin/geofence/settings', formData);
            if (response.data.success) {
                setMessage({ type: 'success', text: 'Settings saved successfully!' });
                setHasChanges(false);
            }
        } catch (error: any) {
            setMessage({
                type: 'error',
                text: error.response?.data?.message || 'Failed to save settings'
            });
        } finally {
            setSaving(false);
        }
    };

    const handleSync = async () => {
        setSyncing(true);
        setMessage(null);

        try {
            const response = await axios.post('/admin/geofence/sync');
            if (response.data.success) {
                const results = response.data.results;
                setMessage({
                    type: 'success',
                    text: `Sync complete: ${results.created} created, ${results.updated} updated, ${results.failed} failed`
                });
            }
        } catch (error: any) {
            setMessage({
                type: 'error',
                text: error.response?.data?.message || 'Failed to sync geofences'
            });
        } finally {
            setSyncing(false);
        }
    };

    return (
        <AdminLayout>
            <div className="max-w-4xl mx-auto">
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Geofence Settings</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            Configure Radar.io integration and notification throttling
                        </p>
                    </div>
                    <div className="flex gap-3">
                        <button
                            onClick={handleSync}
                            disabled={syncing}
                            className="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors disabled:opacity-50"
                        >
                            <SyncIcon />
                            {syncing ? 'Syncing...' : 'Sync to Radar'}
                        </button>
                        <button
                            onClick={handleSave}
                            disabled={!hasChanges || saving}
                            className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                                hasChanges && !saving
                                    ? 'bg-pink-500 text-white hover:bg-pink-600'
                                    : 'bg-gray-200 text-gray-400 cursor-not-allowed'
                            }`}
                        >
                            {saving ? 'Saving...' : 'Save Changes'}
                        </button>
                    </div>
                </div>

                {/* Message */}
                {message && (
                    <div className={`mb-6 p-4 rounded-lg ${
                        message.type === 'success'
                            ? 'bg-green-50 text-green-800 border border-green-200'
                            : 'bg-red-50 text-red-800 border border-red-200'
                    }`}>
                        {message.text}
                    </div>
                )}

                <div className="space-y-6">
                    {/* Radar.io API Keys */}
                    <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div className="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center gap-2">
                            <KeyIcon />
                            <h2 className="font-semibold text-gray-900">Radar.io API Keys</h2>
                        </div>
                        <div className="p-6 space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Secret Key (Server-side)
                                </label>
                                <input
                                    type="password"
                                    value={formData.radar_secret_key}
                                    onChange={(e) => handleChange('radar_secret_key', e.target.value)}
                                    placeholder="prj_live_sk_..."
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                />
                                <p className="mt-1 text-xs text-gray-500">
                                    Used for server-side API calls to Radar.io
                                </p>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Publishable Key (Client-side)
                                </label>
                                <input
                                    type="text"
                                    value={formData.radar_publishable_key}
                                    onChange={(e) => handleChange('radar_publishable_key', e.target.value)}
                                    placeholder="prj_live_pk_..."
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                />
                                <p className="mt-1 text-xs text-gray-500">
                                    Used in mobile apps for tracking
                                </p>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Webhook Secret
                                </label>
                                <input
                                    type="password"
                                    value={formData.radar_webhook_secret}
                                    onChange={(e) => handleChange('radar_webhook_secret', e.target.value)}
                                    placeholder="whsec_..."
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                />
                                <p className="mt-1 text-xs text-gray-500">
                                    Used to verify webhook signatures from Radar.io
                                </p>
                            </div>

                            <div className="mt-4 p-4 bg-blue-50 rounded-lg">
                                <p className="text-sm text-blue-800">
                                    <strong>Webhook URL:</strong>{' '}
                                    <code className="bg-blue-100 px-2 py-1 rounded">
                                        {window.location.origin}/webhooks/radar
                                    </code>
                                </p>
                                <p className="mt-2 text-xs text-blue-600">
                                    Add this URL to your Radar.io webhook settings
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Notification Limits */}
                    <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div className="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center gap-2">
                            <BellIcon />
                            <h2 className="font-semibold text-gray-900">Notification Limits</h2>
                        </div>
                        <div className="p-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Max Notifications Per Day
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="50"
                                        value={formData.max_per_day}
                                        onChange={(e) => handleChange('max_per_day', parseInt(e.target.value))}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    />
                                    <p className="mt-1 text-xs text-gray-500">
                                        Maximum notifications per user per day
                                    </p>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Max Notifications Per Week
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="200"
                                        value={formData.max_per_week}
                                        onChange={(e) => handleChange('max_per_week', parseInt(e.target.value))}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    />
                                    <p className="mt-1 text-xs text-gray-500">
                                        Maximum notifications per user per week
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Cooldown Settings */}
                    <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div className="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center gap-2">
                            <ClockIcon />
                            <h2 className="font-semibold text-gray-900">Cooldown Settings</h2>
                        </div>
                        <div className="p-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Minimum Interval (minutes)
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="1440"
                                        value={formData.min_interval_minutes}
                                        onChange={(e) => handleChange('min_interval_minutes', parseInt(e.target.value))}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    />
                                    <p className="mt-1 text-xs text-gray-500">
                                        Minimum time between any two notifications
                                    </p>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Brand Cooldown (hours)
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="168"
                                        value={formData.brand_cooldown_hours}
                                        onChange={(e) => handleChange('brand_cooldown_hours', parseInt(e.target.value))}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    />
                                    <p className="mt-1 text-xs text-gray-500">
                                        Time before notifying about the same brand again
                                    </p>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Location Cooldown (hours)
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="168"
                                        value={formData.location_cooldown_hours}
                                        onChange={(e) => handleChange('location_cooldown_hours', parseInt(e.target.value))}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    />
                                    <p className="mt-1 text-xs text-gray-500">
                                        Time before notifying about the same location again
                                    </p>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Offer Cooldown (hours)
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="168"
                                        value={formData.offer_cooldown_hours}
                                        onChange={(e) => handleChange('offer_cooldown_hours', parseInt(e.target.value))}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    />
                                    <p className="mt-1 text-xs text-gray-500">
                                        Time before notifying about the same offer again
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Quiet Hours */}
                    <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div className="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center gap-2">
                            <MoonIcon />
                            <h2 className="font-semibold text-gray-900">Quiet Hours</h2>
                        </div>
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-6">
                                <div>
                                    <p className="font-medium text-gray-900">Enable Quiet Hours</p>
                                    <p className="text-sm text-gray-500">
                                        Pause notifications during specified hours
                                    </p>
                                </div>
                                <ToggleSwitch
                                    enabled={formData.quiet_hours_enabled}
                                    onChange={() => handleChange('quiet_hours_enabled', !formData.quiet_hours_enabled)}
                                />
                            </div>

                            {formData.quiet_hours_enabled && (
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Start Time
                                        </label>
                                        <input
                                            type="time"
                                            value={formData.quiet_hours_start}
                                            onChange={(e) => handleChange('quiet_hours_start', e.target.value)}
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            End Time
                                        </label>
                                        <input
                                            type="time"
                                            value={formData.quiet_hours_end}
                                            onChange={(e) => handleChange('quiet_hours_end', e.target.value)}
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                        />
                                    </div>
                                </div>
                            )}

                            <p className="mt-4 text-sm text-gray-500">
                                Quiet hours span overnight (e.g., 22:00 to 08:00 means notifications
                                are paused from 10 PM to 8 AM)
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
