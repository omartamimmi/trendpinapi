import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/Pagination';
import { useToast } from '@/Components/Toast';
import { useConfirm } from '@/Components/ConfirmDialog';

export default function Requests({ requests, offers, stats, filters: initialFilters }) {
    const toast = useToast();
    const confirm = useConfirm();
    const [filters, setFilters] = useState({
        status: initialFilters?.status || '',
        bank_offer_id: initialFilters?.bank_offer_id || '',
    });

    const handleFilterChange = (key, value) => {
        setFilters(prev => ({ ...prev, [key]: value }));
    };

    const applyFilters = () => {
        const activeFilters = Object.fromEntries(
            Object.entries(filters).filter(([_, v]) => v !== '')
        );
        router.get('/admin/bank-offer/requests', activeFilters, { preserveState: true });
    };

    const clearFilters = () => {
        setFilters({ status: '', bank_offer_id: '' });
        router.get('/admin/bank-offer/requests', {}, { preserveState: true });
    };

    const handleApprove = (id, brandName) => {
        confirm({
            title: 'Approve Participation',
            message: `Are you sure you want to approve ${brandName}'s participation in this offer?`,
            confirmText: 'Approve',
            type: 'success',
            onConfirm: () => {
                router.put(`/admin/bank-offer/requests/${id}/approve`, {}, {
                    onSuccess: () => toast.success('Participation approved'),
                    onError: () => toast.error('Failed to approve'),
                });
            },
        });
    };

    const handleReject = (id, brandName) => {
        confirm({
            title: 'Reject Participation',
            message: `Are you sure you want to reject ${brandName}'s participation request?`,
            confirmText: 'Reject',
            type: 'danger',
            onConfirm: () => {
                router.put(`/admin/bank-offer/requests/${id}/reject`, {}, {
                    onSuccess: () => toast.success('Participation rejected'),
                    onError: () => toast.error('Failed to reject'),
                });
            },
        });
    };

    const statusConfig = {
        pending: { bg: 'bg-yellow-100', text: 'text-yellow-700', dot: 'bg-yellow-400', label: 'Pending' },
        approved: { bg: 'bg-green-100', text: 'text-green-700', dot: 'bg-green-400', label: 'Approved' },
        rejected: { bg: 'bg-red-100', text: 'text-red-700', dot: 'bg-red-400', label: 'Rejected' },
    };

    return (
        <AdminLayout>
            <div>
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Participation Requests</h1>
                        <p className="text-sm text-gray-500 mt-1">Manage retailer requests to join bank offers</p>
                    </div>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <div className="flex items-center">
                            <div className="p-2 bg-gray-100 rounded-lg">
                                <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <p className="text-xs text-gray-500">Total Requests</p>
                                <p className="text-xl font-bold text-gray-900">{stats?.total || requests?.total || 0}</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <div className="flex items-center">
                            <div className="p-2 bg-yellow-100 rounded-lg">
                                <svg className="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <p className="text-xs text-gray-500">Pending</p>
                                <p className="text-xl font-bold text-yellow-600">{stats?.pending || 0}</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <div className="flex items-center">
                            <div className="p-2 bg-green-100 rounded-lg">
                                <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <p className="text-xs text-gray-500">Approved</p>
                                <p className="text-xl font-bold text-green-600">{stats?.approved || 0}</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <div className="flex items-center">
                            <div className="p-2 bg-red-100 rounded-lg">
                                <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <p className="text-xs text-gray-500">Rejected</p>
                                <p className="text-xl font-bold text-red-600">{stats?.rejected || 0}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Filters */}
                <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
                    <div className="flex items-center gap-2 mb-4">
                        <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <h3 className="text-sm font-semibold text-gray-700">Filters</h3>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div className="md:col-span-2">
                            <select
                                value={filters.bank_offer_id}
                                onChange={(e) => handleFilterChange('bank_offer_id', e.target.value)}
                                className="w-full py-2.5 px-4 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all cursor-pointer"
                            >
                                <option value="">All Offers</option>
                                {offers?.map(offer => (
                                    <option key={offer.id} value={offer.id}>{offer.title}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <select
                                value={filters.status}
                                onChange={(e) => handleFilterChange('status', e.target.value)}
                                className="w-full py-2.5 px-4 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all cursor-pointer"
                            >
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div className="flex items-center gap-2">
                            <button onClick={clearFilters} className="flex-1 px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200">
                                Clear
                            </button>
                            <button onClick={applyFilters} className="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-pink-500 to-pink-600 rounded-xl hover:from-pink-600 hover:to-pink-700">
                                Apply
                            </button>
                        </div>
                    </div>
                </div>

                {/* Requests List */}
                {requests.data && requests.data.length > 0 ? (
                    <div className="space-y-4">
                        {requests.data.map((request) => (
                            <div key={request.id} className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                                <div className="p-6">
                                    <div className="flex items-start justify-between">
                                        <div className="flex items-start">
                                            {request.brand?.logo ? (
                                                <img src={request.brand.logo.url} alt="" className="h-14 w-14 rounded-xl object-cover border border-gray-100" />
                                            ) : (
                                                <div className="h-14 w-14 rounded-xl bg-gradient-to-br from-pink-500 to-pink-600 flex items-center justify-center">
                                                    <span className="text-white text-xl font-bold">{request.brand?.name?.charAt(0)}</span>
                                                </div>
                                            )}
                                            <div className="ml-4">
                                                <h3 className="text-lg font-semibold text-gray-900">{request.brand?.name}</h3>
                                                <p className="text-sm text-gray-500 mt-1">
                                                    Requesting to join: <span className="font-medium text-gray-700">{request.bank_offer?.title}</span>
                                                </p>
                                                <div className="flex items-center gap-2 mt-2">
                                                    {request.bank_offer?.bank?.logo ? (
                                                        <img src={request.bank_offer.bank.logo.url} alt="" className="h-5 w-5 rounded" />
                                                    ) : null}
                                                    <span className="text-xs text-gray-500">{request.bank_offer?.bank?.name}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${statusConfig[request.status]?.bg} ${statusConfig[request.status]?.text}`}>
                                            <span className={`w-1.5 h-1.5 rounded-full mr-1.5 ${statusConfig[request.status]?.dot}`}></span>
                                            {statusConfig[request.status]?.label}
                                        </span>
                                    </div>

                                    <div className="mt-4 flex items-center gap-6">
                                        <div className="flex items-center text-sm">
                                            <svg className="w-4 h-4 text-gray-400 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span className="text-gray-500">
                                                {request.all_branches ? (
                                                    <span className="text-green-600 font-medium">All branches</span>
                                                ) : (
                                                    <span>{request.branch_ids?.length || 0} specific branches</span>
                                                )}
                                            </span>
                                        </div>
                                        <div className="flex items-center text-sm">
                                            <svg className="w-4 h-4 text-gray-400 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span className="text-gray-500">
                                                Requested {new Date(request.requested_at || request.created_at).toLocaleDateString()}
                                            </span>
                                        </div>
                                        {request.approved_at && (
                                            <div className="flex items-center text-sm">
                                                <svg className="w-4 h-4 text-green-400 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span className="text-gray-500">
                                                    Approved {new Date(request.approved_at).toLocaleDateString()}
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                <div className="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                                    <div className="text-xs text-gray-500">
                                        {request.approver && (
                                            <span>Handled by {request.approver.name}</span>
                                        )}
                                    </div>
                                    <div className="flex items-center gap-2">
                                        {request.status === 'pending' && (
                                            <>
                                                <button
                                                    onClick={() => handleApprove(request.id, request.brand?.name)}
                                                    className="px-4 py-1.5 text-sm font-medium text-white bg-green-500 hover:bg-green-600 rounded-lg transition-colors"
                                                >
                                                    Approve
                                                </button>
                                                <button
                                                    onClick={() => handleReject(request.id, request.brand?.name)}
                                                    className="px-4 py-1.5 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors"
                                                >
                                                    Reject
                                                </button>
                                            </>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}

                        {/* Pagination */}
                        <div className="bg-white rounded-xl shadow-sm border border-gray-100">
                            <Pagination data={requests} />
                        </div>
                    </div>
                ) : (
                    <div className="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-100">
                        <div className="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h3 className="text-lg font-medium text-gray-900 mb-2">No Participation Requests</h3>
                        <p className="text-gray-500">
                            {filters.status || filters.bank_offer_id
                                ? 'Try adjusting your filters'
                                : 'Retailer requests to join bank offers will appear here'}
                        </p>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
