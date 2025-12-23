import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/Pagination';
import { useToast } from '@/Components/Toast';
import { useConfirm } from '@/Components/ConfirmDialog';

export default function CardTypes({ cardTypes, banks, stats = {}, filters: initialFilters }) {
    const toast = useToast();
    const confirm = useConfirm();
    const [filters, setFilters] = useState({
        search: initialFilters?.search || '',
        status: initialFilters?.status || '',
        bank_id: initialFilters?.bank_id || '',
        card_network: initialFilters?.card_network || '',
    });

    const handleFilterChange = (key, value) => {
        setFilters(prev => ({ ...prev, [key]: value }));
    };

    const applyFilters = () => {
        const activeFilters = Object.fromEntries(
            Object.entries(filters).filter(([_, v]) => v !== '')
        );
        router.get('/admin/bank-offer/card-types', activeFilters, { preserveState: true });
    };

    const clearFilters = () => {
        setFilters({ search: '', status: '', bank_id: '', card_network: '' });
        router.get('/admin/bank-offer/card-types', {}, { preserveState: true });
    };

    const handleDelete = (id) => {
        confirm({
            title: 'Delete Card Type',
            message: 'Are you sure you want to delete this card type?',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            type: 'danger',
            onConfirm: () => {
                router.delete(`/admin/bank-offer/card-types/${id}`, {
                    onSuccess: () => toast.success('Card type deleted successfully'),
                    onError: () => toast.error('Failed to delete card type'),
                });
            },
        });
    };

    const networkColors = {
        visa: 'bg-blue-100 text-blue-800',
        mastercard: 'bg-orange-100 text-orange-800',
        amex: 'bg-indigo-100 text-indigo-800',
        other: 'bg-gray-100 text-gray-800',
    };

    return (
        <AdminLayout>
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="md:flex md:items-center md:justify-between mb-6">
                        <div className="flex-1 min-w-0">
                            <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                                Card Types
                            </h2>
                            <p className="mt-1 text-sm text-gray-500">
                                Manage card types (Visa, Mastercard, etc.)
                            </p>
                        </div>
                        <div className="mt-4 flex md:mt-0 md:ml-4">
                            <Link
                                href="/admin/bank-offer/card-types/create"
                                className="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pink-600 hover:bg-pink-700"
                            >
                                Add Card Type
                            </Link>
                        </div>
                    </div>

                    {/* Stats Cards */}
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-5 text-white">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-blue-100 text-sm font-medium">Total Cards</p>
                                    <p className="text-3xl font-bold mt-1">{stats.total || 0}</p>
                                </div>
                                <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div className="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-5 text-white">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-green-100 text-sm font-medium">Active</p>
                                    <p className="text-3xl font-bold mt-1">{stats.active || 0}</p>
                                </div>
                                <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div className="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-5 text-white">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-indigo-100 text-sm font-medium">Visa Cards</p>
                                    <p className="text-3xl font-bold mt-1">{stats.visa || 0}</p>
                                </div>
                                <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <span className="text-xs font-bold">VISA</span>
                                </div>
                            </div>
                        </div>
                        <div className="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-5 text-white">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-orange-100 text-sm font-medium">Mastercard</p>
                                    <p className="text-3xl font-bold mt-1">{stats.mastercard || 0}</p>
                                </div>
                                <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <span className="text-xs font-bold">MC</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Filters */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
                        <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                            {/* Search */}
                            <div className="md:col-span-2">
                                <input
                                    type="text"
                                    placeholder="Search card types..."
                                    value={filters.search}
                                    onChange={(e) => handleFilterChange('search', e.target.value)}
                                    onKeyPress={(e) => e.key === 'Enter' && applyFilters()}
                                    className="w-full py-2.5 px-4 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20"
                                />
                            </div>

                            {/* Bank Filter */}
                            <div>
                                <select
                                    value={filters.bank_id}
                                    onChange={(e) => handleFilterChange('bank_id', e.target.value)}
                                    className="w-full py-2.5 px-4 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 cursor-pointer"
                                >
                                    <option value="">All Banks</option>
                                    {banks.map(bank => (
                                        <option key={bank.id} value={bank.id}>{bank.name}</option>
                                    ))}
                                </select>
                            </div>

                            {/* Network Filter */}
                            <div>
                                <select
                                    value={filters.card_network}
                                    onChange={(e) => handleFilterChange('card_network', e.target.value)}
                                    className="w-full py-2.5 px-4 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 cursor-pointer"
                                >
                                    <option value="">All Networks</option>
                                    <option value="visa">Visa</option>
                                    <option value="mastercard">Mastercard</option>
                                    <option value="amex">Amex</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            {/* Action Buttons */}
                            <div className="flex items-center gap-2">
                                <button onClick={clearFilters} className="flex-1 px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200">
                                    Clear
                                </button>
                                <button onClick={applyFilters} className="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-pink-600 rounded-xl hover:bg-pink-700">
                                    Apply
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Card Types Grid */}
                    {cardTypes.data && cardTypes.data.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {cardTypes.data.map((cardType) => (
                                <div key={cardType.id} className="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                                    <div className="flex items-start justify-between">
                                        <div className="flex items-center gap-4">
                                            {cardType.logo ? (
                                                <img src={cardType.logo.url} alt={cardType.name} className="h-12 w-16 rounded-lg object-contain bg-gray-50" />
                                            ) : (
                                                <div className={`h-12 w-16 rounded-lg flex items-center justify-center ${networkColors[cardType.card_network]} bg-opacity-20`}>
                                                    <span className="text-sm font-bold">{cardType.card_network.toUpperCase()}</span>
                                                </div>
                                            )}
                                            <div>
                                                <h3 className="font-semibold text-gray-900">{cardType.name}</h3>
                                                {cardType.name_ar && <p className="text-sm text-gray-500">{cardType.name_ar}</p>}
                                            </div>
                                        </div>
                                        <span className={`px-2.5 py-1 text-xs font-medium rounded-full ${
                                            cardType.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
                                        }`}>
                                            {cardType.status}
                                        </span>
                                    </div>

                                    <div className="mt-4 pt-4 border-t border-gray-100">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-3">
                                                <span className={`px-2.5 py-1 text-xs font-semibold rounded-lg ${networkColors[cardType.card_network]}`}>
                                                    {cardType.card_network.toUpperCase()}
                                                </span>
                                                <span className="text-sm text-gray-500">
                                                    {cardType.bank?.name || 'Generic Card'}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Link
                                                    href={`/admin/bank-offer/card-types/${cardType.id}/edit`}
                                                    className="p-2 text-gray-400 hover:text-pink-600 hover:bg-pink-50 rounded-lg transition-colors"
                                                >
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </Link>
                                                <button
                                                    onClick={() => handleDelete(cardType.id)}
                                                    className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                >
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                            <div className="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <h3 className="text-lg font-medium text-gray-900 mb-1">No Card Types Found</h3>
                            <p className="text-gray-500 mb-4">Get started by adding your first card type.</p>
                            <Link
                                href="/admin/bank-offer/card-types/create"
                                className="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-pink-600 rounded-xl hover:bg-pink-700"
                            >
                                Add Card Type
                            </Link>
                        </div>
                    )}

                    {/* Pagination */}
                    {cardTypes.data && cardTypes.data.length > 0 && (
                        <div className="mt-6">
                            <Pagination data={cardTypes} />
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
