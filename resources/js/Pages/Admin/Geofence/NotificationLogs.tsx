import { useState } from 'react';
import { router, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Notification {
    id: number;
    user_id: number;
    user_name: string;
    user_email: string;
    brand_id: number | null;
    brand_name: string | null;
    branch_id: number | null;
    branch_name: string | null;
    offer_id: number | null;
    offer_title: string | null;
    event_type: string;
    latitude: number;
    longitude: number;
    radar_event_id: string | null;
    created_at: string;
}

interface Brand {
    id: number;
    name: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    data: Notification[];
}

interface Props {
    notifications: Pagination;
    brands: Brand[];
    filters: {
        user_id: string | null;
        brand_id: string | null;
        date_from: string | null;
        date_to: string | null;
    };
}

// Icons
const SearchIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>
);

const CalendarIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
    </svg>
);

const MapPinIcon = () => (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
);

const DownloadIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
    </svg>
);

export default function NotificationLogs({ notifications, brands, filters }: Props) {
    const [userId, setUserId] = useState(filters.user_id || '');
    const [brandId, setBrandId] = useState(filters.brand_id || '');
    const [dateFrom, setDateFrom] = useState(filters.date_from || '');
    const [dateTo, setDateTo] = useState(filters.date_to || '');

    const handleFilter = () => {
        router.get('/admin/geofence/notifications', {
            user_id: userId || undefined,
            brand_id: brandId || undefined,
            date_from: dateFrom || undefined,
            date_to: dateTo || undefined,
        }, { preserveState: true });
    };

    const handleClearFilters = () => {
        setUserId('');
        setBrandId('');
        setDateFrom('');
        setDateTo('');
        router.get('/admin/geofence/notifications');
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleString();
    };

    const getEventTypeBadge = (eventType: string) => {
        const styles: Record<string, string> = {
            entry: 'bg-green-100 text-green-800',
            exit: 'bg-red-100 text-red-800',
            dwell: 'bg-blue-100 text-blue-800',
        };
        return styles[eventType] || 'bg-gray-100 text-gray-800';
    };

    return (
        <AdminLayout>
            <div className="max-w-7xl mx-auto">
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Notification Logs</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            View all geofence-triggered notifications
                        </p>
                    </div>
                </div>

                {/* Filters */}
                <div className="bg-white rounded-xl shadow-sm p-4 mb-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">User ID</label>
                            <input
                                type="text"
                                value={userId}
                                onChange={(e) => setUserId(e.target.value)}
                                placeholder="Enter user ID"
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent text-sm"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">Brand</label>
                            <select
                                value={brandId}
                                onChange={(e) => setBrandId(e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent text-sm"
                            >
                                <option value="">All Brands</option>
                                {brands.map((brand) => (
                                    <option key={brand.id} value={brand.id}>
                                        {brand.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">From Date</label>
                            <input
                                type="date"
                                value={dateFrom}
                                onChange={(e) => setDateFrom(e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent text-sm"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-500 mb-1">To Date</label>
                            <input
                                type="date"
                                value={dateTo}
                                onChange={(e) => setDateTo(e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent text-sm"
                            />
                        </div>
                        <div className="flex items-end gap-2">
                            <button
                                onClick={handleFilter}
                                className="flex-1 px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 text-sm"
                            >
                                Filter
                            </button>
                            <button
                                onClick={handleClearFilters}
                                className="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                {/* Stats Summary */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div className="bg-white rounded-xl shadow-sm p-4">
                        <p className="text-2xl font-bold text-gray-900">{notifications.total}</p>
                        <p className="text-sm text-gray-500">Total Notifications</p>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4">
                        <p className="text-2xl font-bold text-green-600">
                            {notifications.data.filter(n => n.event_type === 'entry').length}
                        </p>
                        <p className="text-sm text-gray-500">Entry Events (this page)</p>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4">
                        <p className="text-2xl font-bold text-blue-600">
                            {new Set(notifications.data.map(n => n.user_id)).size}
                        </p>
                        <p className="text-sm text-gray-500">Unique Users (this page)</p>
                    </div>
                </div>

                {/* Table */}
                <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Brand / Branch
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Offer
                                    </th>
                                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Event
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Location
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {notifications.data.map((notification) => (
                                    <tr key={notification.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="font-medium text-gray-900">
                                                {notification.user_name || `User #${notification.user_id}`}
                                            </div>
                                            <div className="text-xs text-gray-500">
                                                {notification.user_email}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="text-sm text-gray-900">
                                                {notification.brand_name || '-'}
                                            </div>
                                            {notification.branch_name && (
                                                <div className="text-xs text-gray-500">
                                                    {notification.branch_name}
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="text-sm text-gray-900 max-w-xs truncate">
                                                {notification.offer_title || '-'}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-center">
                                            <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getEventTypeBadge(notification.event_type)}`}>
                                                {notification.event_type}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center gap-1 text-sm text-gray-600">
                                                <MapPinIcon />
                                                <span>
                                                    {notification.latitude ? parseFloat(String(notification.latitude)).toFixed(4) : '-'}, {notification.longitude ? parseFloat(String(notification.longitude)).toFixed(4) : '-'}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="text-sm text-gray-900">
                                                {formatDate(notification.created_at)}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {notifications.data.length === 0 && (
                        <div className="px-6 py-12 text-center text-gray-500">
                            No notifications found
                        </div>
                    )}

                    {/* Pagination */}
                    {notifications.last_page > 1 && (
                        <div className="px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                            <p className="text-sm text-gray-500">
                                Showing {(notifications.current_page - 1) * notifications.per_page + 1} to{' '}
                                {Math.min(notifications.current_page * notifications.per_page, notifications.total)} of{' '}
                                {notifications.total} results
                            </p>
                            <div className="flex gap-2">
                                {notifications.current_page > 1 && (
                                    <Link
                                        href={`/admin/geofence/notifications?page=${notifications.current_page - 1}&user_id=${userId}&brand_id=${brandId}&date_from=${dateFrom}&date_to=${dateTo}`}
                                        className="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50"
                                    >
                                        Previous
                                    </Link>
                                )}
                                {notifications.current_page < notifications.last_page && (
                                    <Link
                                        href={`/admin/geofence/notifications?page=${notifications.current_page + 1}&user_id=${userId}&brand_id=${brandId}&date_from=${dateFrom}&date_to=${dateTo}`}
                                        className="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50"
                                    >
                                        Next
                                    </Link>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
