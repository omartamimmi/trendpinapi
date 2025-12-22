import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

interface QrSession {
    id: number;
    session_code: string;
    amount: number;
    final_amount: number | null;
    discount_amount: number | null;
    currency: string;
    status: string;
    created_at: string;
    scanned_at: string | null;
    completed_at: string | null;
    expires_at: string;
    retailer?: {
        id: number;
        name: string;
    };
    branch?: {
        id: number;
        name: string;
        brand?: {
            id: number;
            name: string;
        };
    };
    customer?: {
        id: number;
        name: string;
        email: string;
    };
    payment?: {
        id: number;
        reference: string;
    };
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    data: QrSession[];
}

interface Filters {
    status?: string;
    search?: string;
}

interface Props {
    sessions: Pagination;
    filters: Filters;
}

// Format currency
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-JO', {
        style: 'currency',
        currency: 'JOD',
    }).format(amount);
};

// Status badge component
const StatusBadge = ({ status }: { status: string }) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        scanned: 'bg-blue-100 text-blue-800',
        processing: 'bg-purple-100 text-purple-800',
        completed: 'bg-green-100 text-green-800',
        expired: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
    };

    return (
        <span className={`px-2 py-1 text-xs font-medium rounded-full ${colors[status] || 'bg-gray-100 text-gray-800'}`}>
            {status}
        </span>
    );
};

// Time ago helper
const timeAgo = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} min ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    return date.toLocaleDateString();
};

export default function QrPaymentSessions({ sessions, filters }: Props) {
    const [localFilters, setLocalFilters] = useState<Filters>(filters);

    const applyFilters = () => {
        router.get('/admin/qr-payment/sessions', localFilters, {
            preserveState: true,
        });
    };

    return (
        <AdminLayout>
            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">QR Sessions</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            {sessions.total} total QR payment sessions
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <div className="relative">
                            <input
                                type="text"
                                placeholder="Search session code, brand..."
                                value={localFilters.search || ''}
                                onChange={(e) => setLocalFilters({ ...localFilters, search: e.target.value })}
                                onKeyDown={(e) => e.key === 'Enter' && applyFilters()}
                                className="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm w-64 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            />
                            <svg
                                className="w-5 h-5 text-gray-400 absolute left-3 top-2.5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <select
                            value={localFilters.status || ''}
                            onChange={(e) => {
                                setLocalFilters({ ...localFilters, status: e.target.value });
                                router.get('/admin/qr-payment/sessions', { ...localFilters, status: e.target.value }, { preserveState: true });
                            }}
                            className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="scanned">Scanned</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="expired">Expired</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                {/* Sessions Table */}
                <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Session
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Retailer
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Created
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Expires
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {sessions.data.length > 0 ? (
                                    sessions.data.map((session) => {
                                        const isExpired = new Date(session.expires_at) < new Date() && session.status === 'pending';
                                        return (
                                            <tr key={session.id} className={`hover:bg-gray-50 ${isExpired ? 'opacity-60' : ''}`}>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex items-center gap-3">
                                                        <div className="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                                            <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <p className="text-sm font-medium text-gray-900">{session.session_code}</p>
                                                            {session.payment && (
                                                                <p className="text-xs text-gray-500">
                                                                    Payment: {session.payment.reference}
                                                                </p>
                                                            )}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div>
                                                        <p className="text-sm text-gray-900">{session.branch?.brand?.name || 'N/A'}</p>
                                                        <p className="text-xs text-gray-500">{session.branch?.name || ''}</p>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    {session.customer ? (
                                                        <div>
                                                            <p className="text-sm text-gray-900">{session.customer.name}</p>
                                                            <p className="text-xs text-gray-500">{session.customer.email}</p>
                                                        </div>
                                                    ) : (
                                                        <span className="text-sm text-gray-400">Not scanned</span>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div>
                                                        <p className="text-sm font-medium text-gray-900">
                                                            {formatCurrency(session.final_amount ?? session.amount)}
                                                        </p>
                                                        {session.discount_amount && session.discount_amount > 0 && (
                                                            <p className="text-xs text-green-600">
                                                                -{formatCurrency(session.discount_amount)}
                                                            </p>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <StatusBadge status={isExpired ? 'expired' : session.status} />
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div>
                                                        <p className="text-sm text-gray-900">{timeAgo(session.created_at)}</p>
                                                        <p className="text-xs text-gray-500">
                                                            {new Date(session.created_at).toLocaleTimeString()}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    {isExpired ? (
                                                        <span className="text-sm text-red-500">Expired</span>
                                                    ) : (
                                                        <span className="text-sm text-gray-500">
                                                            {new Date(session.expires_at).toLocaleTimeString()}
                                                        </span>
                                                    )}
                                                </td>
                                            </tr>
                                        );
                                    })
                                ) : (
                                    <tr>
                                        <td colSpan={7} className="px-6 py-12 text-center">
                                            <svg
                                                className="w-12 h-12 text-gray-300 mx-auto mb-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                            </svg>
                                            <p className="text-gray-500">No QR sessions found</p>
                                            <p className="text-sm text-gray-400 mt-1">
                                                QR sessions will appear here when retailers generate payment QR codes
                                            </p>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {sessions.last_page > 1 && (
                        <div className="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                            <p className="text-sm text-gray-500">
                                Showing {(sessions.current_page - 1) * sessions.per_page + 1} to{' '}
                                {Math.min(sessions.current_page * sessions.per_page, sessions.total)} of{' '}
                                {sessions.total} results
                            </p>
                            <div className="flex gap-2">
                                {sessions.current_page > 1 && (
                                    <button
                                        onClick={() => router.get('/admin/qr-payment/sessions', { ...localFilters, page: sessions.current_page - 1 }, { preserveState: true })}
                                        className="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50"
                                    >
                                        Previous
                                    </button>
                                )}
                                {sessions.current_page < sessions.last_page && (
                                    <button
                                        onClick={() => router.get('/admin/qr-payment/sessions', { ...localFilters, page: sessions.current_page + 1 }, { preserveState: true })}
                                        className="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50"
                                    >
                                        Next
                                    </button>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
