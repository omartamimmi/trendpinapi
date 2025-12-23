import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/Pagination';
import { useToast } from '@/Components/Toast';
import { useConfirm } from '@/Components/ConfirmDialog';

export default function Offers({ offers, banks, stats, filters: initialFilters }) {
    const toast = useToast();
    const confirm = useConfirm();
    const [filters, setFilters] = useState({
        search: initialFilters?.search || '',
        status: initialFilters?.status || '',
        bank_id: initialFilters?.bank_id || '',
        offer_type: initialFilters?.offer_type || '',
    });

    const handleFilterChange = (key, value) => {
        setFilters(prev => ({ ...prev, [key]: value }));
    };

    const applyFilters = () => {
        const activeFilters = Object.fromEntries(
            Object.entries(filters).filter(([_, v]) => v !== '')
        );
        router.get('/admin/bank-offer/offers', activeFilters, { preserveState: true });
    };

    const clearFilters = () => {
        setFilters({ search: '', status: '', bank_id: '', offer_type: '' });
        router.get('/admin/bank-offer/offers', {}, { preserveState: true });
    };

    const handleApprove = (id, title) => {
        confirm({
            title: 'Approve Offer',
            message: `Are you sure you want to approve "${title}"? It will become active immediately.`,
            confirmText: 'Approve',
            type: 'success',
            onConfirm: () => {
                router.put(`/admin/bank-offer/offers/${id}/approve`, {}, {
                    onSuccess: () => toast.success('Offer approved successfully'),
                    onError: () => toast.error('Failed to approve offer'),
                });
            },
        });
    };

    const handleReject = (id, title) => {
        confirm({
            title: 'Reject Offer',
            message: `Are you sure you want to reject "${title}"?`,
            confirmText: 'Reject',
            type: 'danger',
            onConfirm: () => {
                router.put(`/admin/bank-offer/offers/${id}/reject`, {}, {
                    onSuccess: () => toast.success('Offer rejected'),
                    onError: () => toast.error('Failed to reject offer'),
                });
            },
        });
    };

    const statusConfig = {
        draft: { bg: 'bg-gray-100', text: 'text-gray-700', dot: 'bg-gray-400' },
        pending: { bg: 'bg-yellow-100', text: 'text-yellow-700', dot: 'bg-yellow-400' },
        active: { bg: 'bg-green-100', text: 'text-green-700', dot: 'bg-green-400' },
        paused: { bg: 'bg-blue-100', text: 'text-blue-700', dot: 'bg-blue-400' },
        expired: { bg: 'bg-red-100', text: 'text-red-700', dot: 'bg-red-400' },
        rejected: { bg: 'bg-red-100', text: 'text-red-700', dot: 'bg-red-400' },
    };

    const getOfferValue = (offer) => {
        if (offer.offer_type === 'percentage') return `${offer.offer_value}%`;
        if (offer.offer_type === 'cashback') return `${offer.offer_value}% Cashback`;
        return `JOD ${offer.offer_value}`;
    };

    return (
        <AdminLayout>
            <div>
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Bank Offers</h1>
                        <p className="text-sm text-gray-500 mt-1">Manage and approve bank card offers</p>
                    </div>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 sm:grid-cols-5 gap-4 mb-6">
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <div className="flex items-center">
                            <div className="p-2 bg-gray-100 rounded-lg">
                                <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <p className="text-xs text-gray-500">Total</p>
                                <p className="text-xl font-bold text-gray-900">{stats?.total || offers?.total || 0}</p>
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
                                <p className="text-xs text-gray-500">Active</p>
                                <p className="text-xl font-bold text-green-600">{stats?.active || 0}</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <div className="flex items-center">
                            <div className="p-2 bg-pink-100 rounded-lg">
                                <svg className="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <p className="text-xs text-gray-500">Retailers</p>
                                <p className="text-xl font-bold text-pink-600">{stats?.retailers || 0}</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <div className="flex items-center">
                            <div className="p-2 bg-purple-100 rounded-lg">
                                <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <p className="text-xs text-gray-500">Claims</p>
                                <p className="text-xl font-bold text-purple-600">{stats?.claims || 0}</p>
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

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div className="lg:col-span-2">
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg className="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    placeholder="Search offers..."
                                    value={filters.search}
                                    onChange={(e) => handleFilterChange('search', e.target.value)}
                                    onKeyPress={(e) => e.key === 'Enter' && applyFilters()}
                                    className="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                />
                            </div>
                        </div>
                        <div>
                            <select
                                value={filters.bank_id}
                                onChange={(e) => handleFilterChange('bank_id', e.target.value)}
                                className="w-full py-2.5 px-4 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all cursor-pointer"
                            >
                                <option value="">All Banks</option>
                                {banks?.map(bank => (
                                    <option key={bank.id} value={bank.id}>{bank.name}</option>
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
                                <option value="pending">Pending Approval</option>
                                <option value="active">Active</option>
                                <option value="paused">Paused</option>
                                <option value="expired">Expired</option>
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

                {/* Offers List */}
                {offers.data && offers.data.length > 0 ? (
                    <div className="space-y-4">
                        {offers.data.map((offer) => (
                            <div key={offer.id} className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                                <div className="p-6">
                                    <div className="flex items-start justify-between">
                                        <div className="flex items-start">
                                            {offer.bank?.logo ? (
                                                <img src={offer.bank.logo.url} alt="" className="h-12 w-12 rounded-lg object-cover" />
                                            ) : (
                                                <div className="h-12 w-12 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                                    <span className="text-white font-bold">{offer.bank?.name?.charAt(0)}</span>
                                                </div>
                                            )}
                                            <div className="ml-4">
                                                <h3 className="text-lg font-semibold text-gray-900">{offer.title}</h3>
                                                {offer.title_ar && <p className="text-sm text-gray-500">{offer.title_ar}</p>}
                                                <div className="flex items-center gap-3 mt-2">
                                                    <span className="text-sm text-gray-500">{offer.bank?.name}</span>
                                                    <span className="text-gray-300">•</span>
                                                    <span className="text-sm text-gray-500">{offer.card_type?.name || 'All Cards'}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${statusConfig[offer.status]?.bg} ${statusConfig[offer.status]?.text}`}>
                                                <span className={`w-1.5 h-1.5 rounded-full mr-1.5 ${statusConfig[offer.status]?.dot}`}></span>
                                                {offer.status}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="mt-4 grid grid-cols-2 md:grid-cols-5 gap-4">
                                        <div>
                                            <p className="text-xs text-gray-500">Discount</p>
                                            <p className="text-sm font-semibold text-pink-600">{getOfferValue(offer)}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-gray-500">Type</p>
                                            <p className="text-sm font-medium text-gray-900 capitalize">{offer.offer_type}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-gray-500">Valid Period</p>
                                            <p className="text-sm font-medium text-gray-900">
                                                {new Date(offer.start_date).toLocaleDateString()} - {new Date(offer.end_date).toLocaleDateString()}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-gray-500">Retailers</p>
                                            <p className="text-sm font-medium text-gray-900">{offer.participating_brands_count || 0}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-gray-500">Claims</p>
                                            <p className="text-sm font-medium text-gray-900">
                                                {offer.total_claims || 0}
                                                {offer.max_claims && <span className="text-gray-400"> / {offer.max_claims}</span>}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div className="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                                    <div className="text-xs text-gray-500">
                                        Created {new Date(offer.created_at).toLocaleDateString()}
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Link
                                            href={`/admin/bank-offer/offers/${offer.id}`}
                                            className="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                                        >
                                            View Details
                                        </Link>
                                        {offer.status === 'pending' && (
                                            <>
                                                <button
                                                    onClick={() => handleApprove(offer.id, offer.title)}
                                                    className="px-3 py-1.5 text-sm font-medium text-green-600 hover:text-green-700 hover:bg-green-50 rounded-lg transition-colors"
                                                >
                                                    Approve
                                                </button>
                                                <button
                                                    onClick={() => handleReject(offer.id, offer.title)}
                                                    className="px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
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
                            <Pagination data={offers} />
                        </div>
                    </div>
                ) : (
                    <div className="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-100">
                        <div className="w-16 h-16 mx-auto mb-4 bg-pink-100 rounded-full flex items-center justify-center">
                            <svg className="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 className="text-lg font-medium text-gray-900 mb-2">No Bank Offers Found</h3>
                        <p className="text-gray-500">
                            {filters.search || filters.status || filters.bank_id
                                ? 'Try adjusting your filters'
                                : 'Bank offers will appear here when banks create them'}
                        </p>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
