import { useState } from 'react';
import { router, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Stats {
    total_transactions: number;
    completed_transactions: number;
    total_amount: number;
    total_discounts: number;
    average_transaction: number;
    conversion_rate: number;
}

interface Transaction {
    id: number;
    reference: string;
    amount: number;
    discount_amount: number;
    status: string;
    gateway: string;
    payment_method: string;
    created_at: string;
    user?: {
        id: number;
        name: string;
        email: string;
    };
    branch?: {
        id: number;
        name: string;
        brand?: {
            id: number;
            name: string;
        };
    };
}

interface DailyStat {
    date: string;
    count: number;
    total: number;
}

interface GatewayStat {
    gateway: string;
    name: string;
    count: number;
    total: number;
}

interface MethodStat {
    method: string;
    count: number;
    total: number;
}

interface BrandStat {
    id: number;
    name: string;
    count: number;
    total: number;
}

interface BranchStat {
    id: number;
    name: string;
    brand_name: string;
    count: number;
    total: number;
}

interface Props {
    stats: Stats;
    recentTransactions: Transaction[];
    dailyStats: DailyStat[];
    byGateway: GatewayStat[];
    byMethod: MethodStat[];
    topBrands: BrandStat[];
    topBranches: BranchStat[];
    filters: {
        date_from: string;
        date_to: string;
    };
}

// Icons
const CurrencyIcon = () => (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
);

const ChartIcon = () => (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
);

const UsersIcon = () => (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
);

const PercentIcon = () => (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
    </svg>
);

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

// Simple bar chart component
const SimpleBarChart = ({ data, labelKey, valueKey }: { data: any[]; labelKey: string; valueKey: string }) => {
    const maxValue = Math.max(...data.map(d => d[valueKey]));

    return (
        <div className="space-y-3">
            {data.map((item, index) => (
                <div key={index} className="flex items-center gap-3">
                    <span className="w-20 text-sm text-gray-600 truncate">{item[labelKey]}</span>
                    <div className="flex-1 h-6 bg-gray-100 rounded-full overflow-hidden">
                        <div
                            className="h-full bg-pink-500 rounded-full transition-all duration-500"
                            style={{ width: `${(item[valueKey] / maxValue) * 100}%` }}
                        />
                    </div>
                    <span className="w-24 text-sm text-gray-900 text-right font-medium">
                        {formatCurrency(item[valueKey])}
                    </span>
                </div>
            ))}
        </div>
    );
};

export default function QrPaymentAnalytics({
    stats,
    recentTransactions,
    dailyStats,
    byGateway,
    byMethod,
    topBrands,
    topBranches,
    filters,
}: Props) {
    const [dateFrom, setDateFrom] = useState(filters.date_from);
    const [dateTo, setDateTo] = useState(filters.date_to);

    const applyFilters = () => {
        router.get('/admin/qr-payment/analytics', {
            date_from: dateFrom,
            date_to: dateTo,
        }, {
            preserveState: true,
        });
    };

    return (
        <AdminLayout>
            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Payment Analytics</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            Overview of QR payment system performance
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <input
                            type="date"
                            value={dateFrom}
                            onChange={(e) => setDateFrom(e.target.value)}
                            className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                        />
                        <span className="text-gray-400">to</span>
                        <input
                            type="date"
                            value={dateTo}
                            onChange={(e) => setDateTo(e.target.value)}
                            className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                        />
                        <button
                            onClick={applyFilters}
                            className="px-4 py-2 bg-pink-500 text-white text-sm rounded-lg hover:bg-pink-600"
                        >
                            Apply
                        </button>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm text-gray-500">Total Revenue</p>
                                <p className="text-2xl font-bold text-gray-900 mt-1">
                                    {formatCurrency(stats.total_amount)}
                                </p>
                            </div>
                            <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-green-600">
                                <CurrencyIcon />
                            </div>
                        </div>
                        <p className="text-xs text-gray-500 mt-2">
                            {stats.completed_transactions} completed transactions
                        </p>
                    </div>

                    <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm text-gray-500">Total Discounts</p>
                                <p className="text-2xl font-bold text-gray-900 mt-1">
                                    {formatCurrency(stats.total_discounts)}
                                </p>
                            </div>
                            <div className="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center text-pink-600">
                                <PercentIcon />
                            </div>
                        </div>
                        <p className="text-xs text-gray-500 mt-2">
                            Saved by customers via bank offers
                        </p>
                    </div>

                    <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm text-gray-500">Avg Transaction</p>
                                <p className="text-2xl font-bold text-gray-900 mt-1">
                                    {formatCurrency(stats.average_transaction)}
                                </p>
                            </div>
                            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                                <ChartIcon />
                            </div>
                        </div>
                        <p className="text-xs text-gray-500 mt-2">
                            Average payment amount
                        </p>
                    </div>

                    <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm text-gray-500">Conversion Rate</p>
                                <p className="text-2xl font-bold text-gray-900 mt-1">
                                    {stats.conversion_rate}%
                                </p>
                            </div>
                            <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">
                                <UsersIcon />
                            </div>
                        </div>
                        <p className="text-xs text-gray-500 mt-2">
                            QR scans to payments
                        </p>
                    </div>
                </div>

                {/* Charts Row */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* By Gateway */}
                    <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <h3 className="font-semibold text-gray-900 mb-4">Revenue by Gateway</h3>
                        {byGateway.length > 0 ? (
                            <SimpleBarChart data={byGateway} labelKey="name" valueKey="total" />
                        ) : (
                            <p className="text-sm text-gray-500 text-center py-8">No data available</p>
                        )}
                    </div>

                    {/* By Payment Method */}
                    <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <h3 className="font-semibold text-gray-900 mb-4">Revenue by Payment Method</h3>
                        {byMethod.length > 0 ? (
                            <SimpleBarChart data={byMethod} labelKey="method" valueKey="total" />
                        ) : (
                            <p className="text-sm text-gray-500 text-center py-8">No data available</p>
                        )}
                    </div>
                </div>

                {/* Top Brands & Branches */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Top Brands */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h3 className="font-semibold text-gray-900">Top Brands</h3>
                        </div>
                        <div className="p-6">
                            {topBrands.length > 0 ? (
                                <div className="space-y-4">
                                    {topBrands.map((brand, index) => (
                                        <div key={brand.id} className="flex items-center justify-between">
                                            <div className="flex items-center gap-3">
                                                <span className="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-600">
                                                    {index + 1}
                                                </span>
                                                <span className="font-medium text-gray-900">{brand.name}</span>
                                            </div>
                                            <div className="text-right">
                                                <p className="font-medium text-gray-900">{formatCurrency(brand.total)}</p>
                                                <p className="text-xs text-gray-500">{brand.count} transactions</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm text-gray-500 text-center py-8">No data available</p>
                            )}
                        </div>
                    </div>

                    {/* Top Branches */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h3 className="font-semibold text-gray-900">Top Branches</h3>
                        </div>
                        <div className="p-6">
                            {topBranches.length > 0 ? (
                                <div className="space-y-4">
                                    {topBranches.map((branch, index) => (
                                        <div key={branch.id} className="flex items-center justify-between">
                                            <div className="flex items-center gap-3">
                                                <span className="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-600">
                                                    {index + 1}
                                                </span>
                                                <div>
                                                    <span className="font-medium text-gray-900">{branch.name}</span>
                                                    <p className="text-xs text-gray-500">{branch.brand_name}</p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <p className="font-medium text-gray-900">{formatCurrency(branch.total)}</p>
                                                <p className="text-xs text-gray-500">{branch.count} transactions</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm text-gray-500 text-center py-8">No data available</p>
                            )}
                        </div>
                    </div>
                </div>

                {/* Recent Transactions */}
                <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 className="font-semibold text-gray-900">Recent Transactions</h3>
                        <Link
                            href="/admin/qr-payment/transactions"
                            className="text-sm text-pink-500 hover:text-pink-600"
                        >
                            View All
                        </Link>
                    </div>
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
                                        Branch
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
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {recentTransactions.length > 0 ? (
                                    recentTransactions.map((tx) => (
                                        <tr key={tx.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <Link
                                                    href={`/admin/qr-payment/transactions/${tx.id}`}
                                                    className="text-sm font-medium text-pink-600 hover:text-pink-800"
                                                >
                                                    {tx.reference}
                                                </Link>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <p className="text-sm text-gray-900">{tx.user?.name || 'N/A'}</p>
                                                    <p className="text-xs text-gray-500">{tx.user?.email || ''}</p>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <p className="text-sm text-gray-900">{tx.branch?.name || 'N/A'}</p>
                                                    <p className="text-xs text-gray-500">{tx.branch?.brand?.name || ''}</p>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <p className="text-sm font-medium text-gray-900">
                                                    {formatCurrency(tx.amount)}
                                                </p>
                                                {tx.discount_amount > 0 && (
                                                    <p className="text-xs text-green-600">
                                                        -{formatCurrency(tx.discount_amount)} discount
                                                    </p>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <StatusBadge status={tx.status} />
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {new Date(tx.created_at).toLocaleDateString()}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={6} className="px-6 py-12 text-center text-gray-500">
                                            No transactions found
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
