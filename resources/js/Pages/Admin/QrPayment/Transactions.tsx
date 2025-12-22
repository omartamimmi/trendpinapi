import { useState } from 'react';
import { router, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Transaction {
    id: number;
    reference: string;
    gateway_transaction_id: string;
    amount: number;
    original_amount: number;
    discount_amount: number;
    currency: string;
    status: string;
    gateway: string;
    payment_method: string;
    card_last_four: string | null;
    card_brand: string | null;
    created_at: string;
    completed_at: string | null;
    user?: {
        id: number;
        name: string;
        email: string;
        phone: string;
    };
    branch?: {
        id: number;
        name: string;
        brand?: {
            id: number;
            name: string;
        };
    };
    bank_offer?: {
        id: number;
        title: string;
        bank?: {
            id: number;
            name: string;
        };
    };
    tokenized_card?: {
        id: number;
        card_last_four: string;
        card_brand: string;
    };
}

interface Brand {
    id: number;
    name: string;
}

interface Branch {
    id: number;
    name: string;
    brand_id: number;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    data: Transaction[];
}

interface Filters {
    status?: string;
    gateway?: string;
    payment_method?: string;
    brand_id?: string;
    branch_id?: string;
    date_from?: string;
    date_to?: string;
    search?: string;
}

interface Props {
    transactions: Pagination;
    brands: Brand[];
    branches: Branch[];
    filters: Filters;
}

// Status badge component
const StatusBadge = ({ status }: { status: string }) => {
    const colors: Record<string, string> = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        failed: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
        refunded: 'bg-purple-100 text-purple-800',
    };

    return (
        <span className={`px-2 py-1 text-xs font-medium rounded-full ${colors[status] || 'bg-gray-100 text-gray-800'}`}>
            {status}
        </span>
    );
};

// Format currency
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-JO', {
        style: 'currency',
        currency: 'JOD',
    }).format(amount);
};

// Payment method icon
const getPaymentMethodIcon = (method: string) => {
    switch (method) {
        case 'card':
            return (
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            );
        case 'apple_pay':
            return <span className="text-xs font-bold">AP</span>;
        case 'google_pay':
            return <span className="text-xs font-bold">GP</span>;
        case 'cliq':
            return <span className="text-xs font-bold">CLQ</span>;
        default:
            return null;
    }
};

export default function QrPaymentTransactions({ transactions, brands, branches, filters }: Props) {
    const [localFilters, setLocalFilters] = useState<Filters>(filters);
    const [showFilters, setShowFilters] = useState(false);

    const applyFilters = () => {
        router.get('/admin/qr-payment/transactions', localFilters, {
            preserveState: true,
        });
    };

    const clearFilters = () => {
        setLocalFilters({});
        router.get('/admin/qr-payment/transactions', {}, {
            preserveState: true,
        });
    };

    const filteredBranches = localFilters.brand_id
        ? branches.filter(b => b.brand_id === parseInt(localFilters.brand_id!))
        : branches;

    return (
        <AdminLayout>
            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Transactions</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            {transactions.total} total transactions
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <div className="relative">
                            <input
                                type="text"
                                placeholder="Search reference, customer..."
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
                        <button
                            onClick={() => setShowFilters(!showFilters)}
                            className={`px-4 py-2 border rounded-lg text-sm flex items-center gap-2 ${
                                showFilters ? 'border-pink-500 text-pink-500' : 'border-gray-300 text-gray-700'
                            }`}
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filters
                        </button>
                    </div>
                </div>

                {/* Filters Panel */}
                {showFilters && (
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select
                                    value={localFilters.status || ''}
                                    onChange={(e) => setLocalFilters({ ...localFilters, status: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="completed">Completed</option>
                                    <option value="failed">Failed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="refunded">Refunded</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Gateway</label>
                                <select
                                    value={localFilters.gateway || ''}
                                    onChange={(e) => setLocalFilters({ ...localFilters, gateway: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                >
                                    <option value="">All Gateways</option>
                                    <option value="tap">Tap Payments</option>
                                    <option value="hyperpay">HyperPay</option>
                                    <option value="paytabs">PayTabs</option>
                                    <option value="cliq">CliQ</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                                <select
                                    value={localFilters.payment_method || ''}
                                    onChange={(e) => setLocalFilters({ ...localFilters, payment_method: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                >
                                    <option value="">All Methods</option>
                                    <option value="card">Card</option>
                                    <option value="apple_pay">Apple Pay</option>
                                    <option value="google_pay">Google Pay</option>
                                    <option value="cliq">CliQ</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                <select
                                    value={localFilters.brand_id || ''}
                                    onChange={(e) => setLocalFilters({ ...localFilters, brand_id: e.target.value, branch_id: '' })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                >
                                    <option value="">All Brands</option>
                                    {brands.map((brand) => (
                                        <option key={brand.id} value={brand.id}>{brand.name}</option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                <select
                                    value={localFilters.branch_id || ''}
                                    onChange={(e) => setLocalFilters({ ...localFilters, branch_id: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                >
                                    <option value="">All Branches</option>
                                    {filteredBranches.map((branch) => (
                                        <option key={branch.id} value={branch.id}>{branch.name}</option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                <input
                                    type="date"
                                    value={localFilters.date_from || ''}
                                    onChange={(e) => setLocalFilters({ ...localFilters, date_from: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                                <input
                                    type="date"
                                    value={localFilters.date_to || ''}
                                    onChange={(e) => setLocalFilters({ ...localFilters, date_to: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                />
                            </div>

                            <div className="flex items-end gap-2">
                                <button
                                    onClick={applyFilters}
                                    className="px-4 py-2 bg-pink-500 text-white text-sm rounded-lg hover:bg-pink-600"
                                >
                                    Apply Filters
                                </button>
                                <button
                                    onClick={clearFilters}
                                    className="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                )}

                {/* Transactions Table */}
                <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Reference
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Retailer / Branch
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Payment
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {transactions.data.length > 0 ? (
                                    transactions.data.map((tx) => (
                                        <tr key={tx.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <p className="text-sm font-medium text-gray-900">{tx.reference}</p>
                                                    {tx.gateway_transaction_id && (
                                                        <p className="text-xs text-gray-500 truncate max-w-[150px]">
                                                            {tx.gateway_transaction_id}
                                                        </p>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <p className="text-sm text-gray-900">{tx.user?.name || 'N/A'}</p>
                                                    <p className="text-xs text-gray-500">{tx.user?.email || ''}</p>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <p className="text-sm text-gray-900">{tx.branch?.brand?.name || 'N/A'}</p>
                                                    <p className="text-xs text-gray-500">{tx.branch?.name || ''}</p>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex items-center gap-2">
                                                    <span className="w-6 h-6 rounded bg-gray-100 flex items-center justify-center text-gray-600">
                                                        {getPaymentMethodIcon(tx.payment_method)}
                                                    </span>
                                                    <div>
                                                        <p className="text-sm text-gray-900 capitalize">
                                                            {tx.payment_method?.replace('_', ' ')}
                                                        </p>
                                                        {tx.card_last_four && (
                                                            <p className="text-xs text-gray-500">
                                                                **** {tx.card_last_four}
                                                            </p>
                                                        )}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <p className="text-sm font-medium text-gray-900">
                                                        {formatCurrency(tx.amount)}
                                                    </p>
                                                    {tx.discount_amount > 0 && (
                                                        <p className="text-xs text-green-600">
                                                            -{formatCurrency(tx.discount_amount)}
                                                        </p>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <StatusBadge status={tx.status} />
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div>
                                                    <p>{new Date(tx.created_at).toLocaleDateString()}</p>
                                                    <p className="text-xs">{new Date(tx.created_at).toLocaleTimeString()}</p>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right">
                                                <Link
                                                    href={`/admin/qr-payment/transactions/${tx.id}`}
                                                    className="text-pink-600 hover:text-pink-800 text-sm font-medium"
                                                >
                                                    View
                                                </Link>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={8} className="px-6 py-12 text-center">
                                            <svg
                                                className="w-12 h-12 text-gray-300 mx-auto mb-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            <p className="text-gray-500">No transactions found</p>
                                            <p className="text-sm text-gray-400 mt-1">
                                                Try adjusting your filters or search criteria
                                            </p>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {transactions.last_page > 1 && (
                        <div className="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                            <p className="text-sm text-gray-500">
                                Showing {(transactions.current_page - 1) * transactions.per_page + 1} to{' '}
                                {Math.min(transactions.current_page * transactions.per_page, transactions.total)} of{' '}
                                {transactions.total} results
                            </p>
                            <div className="flex gap-2">
                                {transactions.current_page > 1 && (
                                    <Link
                                        href={`/admin/qr-payment/transactions?page=${transactions.current_page - 1}`}
                                        preserveState
                                        className="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50"
                                    >
                                        Previous
                                    </Link>
                                )}
                                {transactions.current_page < transactions.last_page && (
                                    <Link
                                        href={`/admin/qr-payment/transactions?page=${transactions.current_page + 1}`}
                                        preserveState
                                        className="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50"
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
