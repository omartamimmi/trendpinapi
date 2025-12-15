import { useState } from 'react';
import { router, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/Pagination';
import { useToast } from '@/Components/Toast';
import { useConfirm } from '@/Components/ConfirmDialog';

export default function Offers({ offers, retailers, stats, filters }) {
    const toast = useToast();
    const confirm = useConfirm();
    const [search, setSearch] = useState(filters?.search || '');
    const [statusFilter, setStatusFilter] = useState(filters?.status || '');
    const [retailerFilter, setRetailerFilter] = useState(filters?.retailer_id || '');

    const handleDelete = (id) => {
        confirm({
            title: 'Delete Offer',
            message: 'Are you sure you want to delete this offer? This action cannot be undone.',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            type: 'danger',
            onConfirm: () => {
                router.delete(`/admin/offers/${id}`, {
                    onSuccess: () => toast.success('Offer deleted successfully'),
                    onError: () => toast.error('Failed to delete offer'),
                });
            },
        });
    };

    const handleFilter = () => {
        router.get('/admin/offers', {
            search: search || undefined,
            status: statusFilter || undefined,
            retailer_id: retailerFilter || undefined,
        }, { preserveState: true });
    };

    const handleKeyPress = (e) => {
        if (e.key === 'Enter') {
            handleFilter();
        }
    };

    const clearFilters = () => {
        setSearch('');
        setStatusFilter('');
        setRetailerFilter('');
        router.get('/admin/offers');
    };

    const getStatusBadge = (status) => {
        const styles = {
            active: 'bg-green-100 text-green-800',
            draft: 'bg-gray-100 text-gray-800',
            paused: 'bg-yellow-100 text-yellow-800',
            expired: 'bg-red-100 text-red-800',
        };
        return styles[status] || 'bg-gray-100 text-gray-800';
    };

    const getDiscountDisplay = (offer) => {
        if (offer.discount_type === 'percentage') {
            return `${offer.discount_value}%`;
        } else if (offer.discount_type === 'fixed') {
            return `${offer.discount_value} JOD`;
        } else if (offer.discount_type === 'bogo') {
            return 'Buy One Get One';
        }
        return '-';
    };

    return (
        <AdminLayout>
            <div>
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Offers & Discounts</h1>
                        <p className="text-sm text-gray-500 mt-1">Manage all retailer offers</p>
                    </div>
                    <Link
                        href="/admin/offers/create"
                        className="flex items-center px-4 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 transition-all"
                    >
                        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Offer
                    </Link>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <p className="text-sm text-gray-500">Total Offers</p>
                        <p className="text-2xl font-bold text-gray-900">{stats?.total || 0}</p>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <p className="text-sm text-gray-500">Active</p>
                        <p className="text-2xl font-bold text-green-600">{stats?.active || 0}</p>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <p className="text-sm text-gray-500">Total Claims</p>
                        <p className="text-2xl font-bold text-gray-900">{stats?.claims || 0}</p>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <p className="text-sm text-gray-500">Total Views</p>
                        <p className="text-2xl font-bold text-gray-900">{stats?.views || 0}</p>
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
                        {/* Search */}
                        <div className="lg:col-span-2">
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg className="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    onKeyPress={handleKeyPress}
                                    placeholder="Search offers..."
                                    className="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                />
                            </div>
                        </div>

                        {/* Status Filter */}
                        <div>
                            <select
                                value={statusFilter}
                                onChange={(e) => setStatusFilter(e.target.value)}
                                className="w-full py-2.5 px-4 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all cursor-pointer"
                            >
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="draft">Draft</option>
                                <option value="paused">Paused</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>

                        {/* Retailer Filter */}
                        <div>
                            <select
                                value={retailerFilter}
                                onChange={(e) => setRetailerFilter(e.target.value)}
                                className="w-full py-2.5 px-4 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all cursor-pointer"
                            >
                                <option value="">All Retailers</option>
                                {retailers?.map((retailer) => (
                                    <option key={retailer.id} value={retailer.id}>
                                        {retailer.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Action Buttons */}
                        <div className="flex items-center gap-2">
                            <button
                                onClick={clearFilters}
                                className="flex-1 px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 hover:text-gray-800 transition-all"
                            >
                                Clear
                            </button>
                            <button
                                onClick={handleFilter}
                                className="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-pink-500 to-pink-600 rounded-xl hover:from-pink-600 hover:to-pink-700 shadow-sm hover:shadow transition-all"
                            >
                                Apply
                            </button>
                        </div>
                    </div>
                </div>

                {/* Offers Table */}
                {(offers?.data || offers)?.length > 0 ? (
                    <div className="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Offer</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Retailer</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claims</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {(offers?.data || offers).map((offer) => (
                                        <tr key={offer.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="font-medium text-gray-900">{offer.name}</div>
                                                {offer.description && (
                                                    <div className="text-sm text-gray-500 truncate max-w-xs">{offer.description}</div>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900">{offer.user?.name || '-'}</div>
                                                <div className="text-xs text-gray-500">{offer.user?.email}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {offer.brand?.name || '-'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="text-sm font-medium text-pink-600">
                                                    {getDiscountDisplay(offer)}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {offer.claims_count || 0}
                                                {offer.max_claims && (
                                                    <span className="text-gray-400"> / {offer.max_claims}</span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`px-2 py-1 text-xs font-medium rounded-full ${getStatusBadge(offer.status)}`}>
                                                    {offer.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm">
                                                <div className="flex items-center space-x-3">
                                                    <Link
                                                        href={`/admin/offers/${offer.id}/edit`}
                                                        className="text-pink-600 hover:text-pink-700 font-medium"
                                                    >
                                                        Edit
                                                    </Link>
                                                    <button
                                                        onClick={() => handleDelete(offer.id)}
                                                        className="text-red-600 hover:text-red-700 font-medium"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {offers?.data && <Pagination data={offers} />}
                    </div>
                ) : (
                    <div className="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-100">
                        <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <h3 className="text-lg font-medium text-gray-900 mb-2">No Offers Found</h3>
                        <p className="text-gray-500 mb-4">
                            {filters?.search || filters?.status || filters?.retailer_id
                                ? 'Try adjusting your filters'
                                : 'Create the first offer to get started'}
                        </p>
                        <Link
                            href="/admin/offers/create"
                            className="inline-flex items-center px-6 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 transition-all"
                        >
                            Create Offer
                        </Link>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
