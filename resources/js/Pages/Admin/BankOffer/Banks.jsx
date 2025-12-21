import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/Pagination';
import { useToast } from '@/Components/Toast';
import { useConfirm } from '@/Components/ConfirmDialog';

export default function Banks({ banks, stats, filters: initialFilters }) {
    const toast = useToast();
    const confirm = useConfirm();
    const [filters, setFilters] = useState({
        search: initialFilters?.search || '',
        status: initialFilters?.status || '',
    });

    const handleFilterChange = (key, value) => {
        setFilters(prev => ({ ...prev, [key]: value }));
    };

    const applyFilters = () => {
        const activeFilters = Object.fromEntries(
            Object.entries(filters).filter(([_, v]) => v !== '')
        );
        router.get('/admin/bank-offer/banks', activeFilters, { preserveState: true });
    };

    const clearFilters = () => {
        setFilters({ search: '', status: '' });
        router.get('/admin/bank-offer/banks', {}, { preserveState: true });
    };

    const handleKeyPress = (e) => {
        if (e.key === 'Enter') {
            applyFilters();
        }
    };

    const handleDelete = (id, name) => {
        confirm({
            title: 'Delete Bank',
            message: `Are you sure you want to delete "${name}"? This will also affect all associated offers and card types.`,
            confirmText: 'Delete',
            cancelText: 'Cancel',
            type: 'danger',
            onConfirm: () => {
                router.delete(`/admin/bank-offer/banks/${id}`, {
                    onSuccess: () => toast.success('Bank deleted successfully'),
                    onError: () => toast.error('Failed to delete bank'),
                });
            },
        });
    };

    return (
        <AdminLayout>
            <div>
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Banks</h1>
                        <p className="text-sm text-gray-500 mt-1">Manage banks for card offers</p>
                    </div>
                    <Link
                        href="/admin/bank-offer/banks/create"
                        className="flex items-center px-4 py-2 rounded-lg text-white font-medium bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 transition-all shadow-sm"
                    >
                        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Bank
                    </Link>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <div className="flex items-center">
                            <div className="p-2 bg-blue-100 rounded-lg">
                                <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <p className="text-sm text-gray-500">Total Banks</p>
                                <p className="text-2xl font-bold text-gray-900">{stats?.total || banks?.total || 0}</p>
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
                                <p className="text-sm text-gray-500">Active</p>
                                <p className="text-2xl font-bold text-green-600">{stats?.active || 0}</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <div className="flex items-center">
                            <div className="p-2 bg-purple-100 rounded-lg">
                                <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <p className="text-sm text-gray-500">Card Types</p>
                                <p className="text-2xl font-bold text-purple-600">{stats?.cardTypes || 0}</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                        <div className="flex items-center">
                            <div className="p-2 bg-pink-100 rounded-lg">
                                <svg className="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <p className="text-sm text-gray-500">Total Offers</p>
                                <p className="text-2xl font-bold text-pink-600">{stats?.offers || 0}</p>
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
                        {/* Search */}
                        <div className="md:col-span-2">
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg className="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    placeholder="Search banks..."
                                    value={filters.search}
                                    onChange={(e) => handleFilterChange('search', e.target.value)}
                                    onKeyPress={handleKeyPress}
                                    className="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                />
                            </div>
                        </div>

                        {/* Status Filter */}
                        <div>
                            <select
                                value={filters.status}
                                onChange={(e) => handleFilterChange('status', e.target.value)}
                                className="w-full py-2.5 px-4 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all cursor-pointer"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
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
                                onClick={applyFilters}
                                className="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-pink-500 to-pink-600 rounded-xl hover:from-pink-600 hover:to-pink-700 shadow-sm hover:shadow transition-all"
                            >
                                Apply
                            </button>
                        </div>
                    </div>
                </div>

                {/* Banks Grid */}
                {banks.data && banks.data.length > 0 ? (
                    <>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                            {banks.data.map((bank) => (
                                <div key={bank.id} className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                                    <div className="p-6">
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-center">
                                                {bank.logo ? (
                                                    <img
                                                        src={bank.logo.url}
                                                        alt={bank.name}
                                                        className="h-14 w-14 rounded-xl object-cover border border-gray-100"
                                                    />
                                                ) : (
                                                    <div className="h-14 w-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                                        <span className="text-white text-xl font-bold">
                                                            {bank.name.charAt(0)}
                                                        </span>
                                                    </div>
                                                )}
                                                <div className="ml-4">
                                                    <h3 className="text-lg font-semibold text-gray-900">{bank.name}</h3>
                                                    {bank.name_ar && (
                                                        <p className="text-sm text-gray-500">{bank.name_ar}</p>
                                                    )}
                                                </div>
                                            </div>
                                            <span className={`px-2.5 py-1 text-xs font-semibold rounded-full ${
                                                bank.status === 'active'
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-gray-100 text-gray-600'
                                            }`}>
                                                {bank.status}
                                            </span>
                                        </div>

                                        {bank.description && (
                                            <p className="mt-3 text-sm text-gray-500 line-clamp-2">{bank.description}</p>
                                        )}

                                        <div className="mt-4 flex items-center gap-4 text-sm">
                                            <div className="flex items-center text-gray-500">
                                                <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                </svg>
                                                {bank.card_types_count || 0} Cards
                                            </div>
                                            <div className="flex items-center text-gray-500">
                                                <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                                {bank.offers_count || 0} Offers
                                            </div>
                                        </div>
                                    </div>

                                    <div className="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                                        <Link
                                            href={`/admin/bank-offer/banks/${bank.id}/edit`}
                                            className="px-3 py-1.5 text-sm font-medium text-pink-600 hover:text-pink-700 hover:bg-pink-50 rounded-lg transition-colors"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            onClick={() => handleDelete(bank.id, bank.name)}
                                            className="px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Pagination */}
                        <div className="bg-white rounded-xl shadow-sm border border-gray-100">
                            <Pagination data={banks} />
                        </div>
                    </>
                ) : (
                    <div className="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-100">
                        <div className="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg className="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                            </svg>
                        </div>
                        <h3 className="text-lg font-medium text-gray-900 mb-2">No Banks Found</h3>
                        <p className="text-gray-500 mb-6">
                            {filters.search || filters.status
                                ? 'Try adjusting your filters'
                                : 'Add your first bank to get started with card offers'}
                        </p>
                        <Link
                            href="/admin/bank-offer/banks/create"
                            className="inline-flex items-center px-6 py-2.5 rounded-lg text-white font-medium bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 transition-all shadow-sm"
                        >
                            <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Bank
                        </Link>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
