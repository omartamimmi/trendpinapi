import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Stats {
    total_geofences: number;
    active_geofences: number;
    notifications_today: number;
    notifications_this_week: number;
    notifications_this_month: number;
    unique_users_reached: number;
    is_quiet_hours: boolean;
    throttle_config: {
        max_per_day: number;
        max_per_week: number;
    };
}

interface Notification {
    id: number;
    user_name: string;
    brand_name: string;
    branch_name: string;
    offer_title: string;
    event_type: string;
    created_at: string;
}

interface Geofence {
    id: number;
    name: string;
    brand_name: string;
    branch_name: string;
    latitude: number;
    longitude: number;
    radius: number;
    is_active: boolean;
}

interface Props {
    stats: Stats;
    recentNotifications: { data: Notification[] };
    geofences: { data: Geofence[] };
}

// Icons
const MapPinIcon = () => (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
);

const BellIcon = () => (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
    </svg>
);

const UsersIcon = () => (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
);

const MoonIcon = () => (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>
);

const CogIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
);

export default function GeofenceDashboard({ stats, recentNotifications, geofences }: Props) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleString();
    };

    return (
        <AdminLayout>
            <div className="max-w-7xl mx-auto">
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Geofence Dashboard</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            Location-based notifications powered by Radar.io
                        </p>
                    </div>
                    <Link
                        href="/admin/geofence/settings"
                        className="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        <CogIcon />
                        Settings
                    </Link>
                </div>

                {/* Quiet Hours Alert */}
                {stats.is_quiet_hours && (
                    <div className="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg flex items-center gap-3">
                        <MoonIcon />
                        <div>
                            <p className="font-medium text-indigo-900">Quiet Hours Active</p>
                            <p className="text-sm text-indigo-700">
                                Notifications are paused during quiet hours to avoid disturbing users.
                            </p>
                        </div>
                    </div>
                )}

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div className="bg-white rounded-xl shadow-sm p-6">
                        <div className="flex items-center gap-4">
                            <div className="p-3 bg-blue-100 rounded-lg">
                                <MapPinIcon />
                            </div>
                            <div>
                                <p className="text-2xl font-bold text-gray-900">{stats.active_geofences}</p>
                                <p className="text-sm text-gray-500">Active Geofences</p>
                            </div>
                        </div>
                        <p className="mt-3 text-xs text-gray-400">
                            {stats.total_geofences} total geofences
                        </p>
                    </div>

                    <div className="bg-white rounded-xl shadow-sm p-6">
                        <div className="flex items-center gap-4">
                            <div className="p-3 bg-green-100 rounded-lg">
                                <BellIcon />
                            </div>
                            <div>
                                <p className="text-2xl font-bold text-gray-900">{stats.notifications_today}</p>
                                <p className="text-sm text-gray-500">Notifications Today</p>
                            </div>
                        </div>
                        <p className="mt-3 text-xs text-gray-400">
                            {stats.notifications_this_week} this week
                        </p>
                    </div>

                    <div className="bg-white rounded-xl shadow-sm p-6">
                        <div className="flex items-center gap-4">
                            <div className="p-3 bg-purple-100 rounded-lg">
                                <UsersIcon />
                            </div>
                            <div>
                                <p className="text-2xl font-bold text-gray-900">{stats.unique_users_reached}</p>
                                <p className="text-sm text-gray-500">Users Reached</p>
                            </div>
                        </div>
                        <p className="mt-3 text-xs text-gray-400">
                            This month
                        </p>
                    </div>

                    <div className="bg-white rounded-xl shadow-sm p-6">
                        <div className="flex items-center gap-4">
                            <div className="p-3 bg-pink-100 rounded-lg">
                                <BellIcon />
                            </div>
                            <div>
                                <p className="text-2xl font-bold text-gray-900">{stats.notifications_this_month}</p>
                                <p className="text-sm text-gray-500">Monthly Notifications</p>
                            </div>
                        </div>
                        <p className="mt-3 text-xs text-gray-400">
                            Max {stats.throttle_config.max_per_day}/day, {stats.throttle_config.max_per_week}/week
                        </p>
                    </div>
                </div>

                {/* Quick Actions */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                    <Link
                        href="/admin/geofence/locations"
                        className="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow flex items-center gap-4"
                    >
                        <div className="p-2 bg-indigo-100 rounded-lg">
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <p className="font-medium text-gray-900">Locations</p>
                            <p className="text-sm text-gray-500">Malls & shopping areas</p>
                        </div>
                    </Link>

                    <Link
                        href="/admin/geofence/geofences"
                        className="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow flex items-center gap-4"
                    >
                        <div className="p-2 bg-blue-100 rounded-lg">
                            <MapPinIcon />
                        </div>
                        <div>
                            <p className="font-medium text-gray-900">Geofences</p>
                            <p className="text-sm text-gray-500">View all geofence zones</p>
                        </div>
                    </Link>

                    <Link
                        href="/admin/geofence/notifications"
                        className="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow flex items-center gap-4"
                    >
                        <div className="p-2 bg-green-100 rounded-lg">
                            <BellIcon />
                        </div>
                        <div>
                            <p className="font-medium text-gray-900">Notification Logs</p>
                            <p className="text-sm text-gray-500">View sent notifications</p>
                        </div>
                    </Link>

                    <Link
                        href="/admin/geofence/settings"
                        className="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow flex items-center gap-4"
                    >
                        <div className="p-2 bg-purple-100 rounded-lg">
                            <CogIcon />
                        </div>
                        <div>
                            <p className="font-medium text-gray-900">Radar Settings</p>
                            <p className="text-sm text-gray-500">Configure API keys & throttling</p>
                        </div>
                    </Link>

                    <Link
                        href="/admin/geofence/test"
                        className="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow flex items-center gap-4"
                    >
                        <div className="p-2 bg-amber-100 rounded-lg">
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p className="font-medium text-gray-900">Test & Simulate</p>
                            <p className="text-sm text-gray-500">Manually test geofence events</p>
                        </div>
                    </Link>
                </div>

                {/* Recent Activity */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Recent Notifications */}
                    <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                            <h2 className="font-semibold text-gray-900">Recent Notifications</h2>
                            <Link
                                href="/admin/geofence/notifications"
                                className="text-sm text-pink-600 hover:text-pink-700"
                            >
                                View all
                            </Link>
                        </div>
                        <div className="divide-y divide-gray-100">
                            {recentNotifications.data.length > 0 ? (
                                recentNotifications.data.slice(0, 5).map((notification) => (
                                    <div key={notification.id} className="px-6 py-4">
                                        <div className="flex justify-between items-start">
                                            <div>
                                                <p className="font-medium text-gray-900">
                                                    {notification.user_name || 'Unknown User'}
                                                </p>
                                                <p className="text-sm text-gray-500">
                                                    {notification.brand_name} - {notification.offer_title || 'N/A'}
                                                </p>
                                            </div>
                                            <span className={`text-xs px-2 py-1 rounded-full ${
                                                notification.event_type === 'entry'
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-gray-100 text-gray-700'
                                            }`}>
                                                {notification.event_type}
                                            </span>
                                        </div>
                                        <p className="mt-1 text-xs text-gray-400">
                                            {formatDate(notification.created_at)}
                                        </p>
                                    </div>
                                ))
                            ) : (
                                <div className="px-6 py-8 text-center text-gray-500">
                                    No notifications yet
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Active Geofences */}
                    <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                            <h2 className="font-semibold text-gray-900">Active Geofences</h2>
                            <Link
                                href="/admin/geofence/geofences"
                                className="text-sm text-pink-600 hover:text-pink-700"
                            >
                                View all
                            </Link>
                        </div>
                        <div className="divide-y divide-gray-100">
                            {geofences.data.length > 0 ? (
                                geofences.data.slice(0, 5).map((geofence) => (
                                    <div key={geofence.id} className="px-6 py-4">
                                        <div className="flex justify-between items-start">
                                            <div>
                                                <p className="font-medium text-gray-900">{geofence.name}</p>
                                                <p className="text-sm text-gray-500">
                                                    {geofence.brand_name || 'No brand'}
                                                    {geofence.branch_name && ` - ${geofence.branch_name}`}
                                                </p>
                                            </div>
                                            <span className={`text-xs px-2 py-1 rounded-full ${
                                                geofence.is_active
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-gray-100 text-gray-700'
                                            }`}>
                                                {geofence.radius}m radius
                                            </span>
                                        </div>
                                        <p className="mt-1 text-xs text-gray-400">
                                            {parseFloat(String(geofence.latitude)).toFixed(6)}, {parseFloat(String(geofence.longitude)).toFixed(6)}
                                        </p>
                                    </div>
                                ))
                            ) : (
                                <div className="px-6 py-8 text-center text-gray-500">
                                    No geofences configured
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
