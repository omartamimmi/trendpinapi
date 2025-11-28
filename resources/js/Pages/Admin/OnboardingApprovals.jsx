import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function OnboardingApprovals({ onboardings, currentStatus, counts }) {
    const [search, setSearch] = useState('');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/admin/onboarding-approvals', { search, status: currentStatus }, { preserveState: true });
    };

    const handleStatusChange = (status) => {
        router.get('/admin/onboarding-approvals', { search, status }, { preserveState: true });
    };

    const getStatusBadge = (status) => {
        const styles = {
            pending: 'bg-gray-100 text-gray-800',
            pending_approval: 'bg-yellow-100 text-yellow-800',
            approved: 'bg-green-100 text-green-800',
            changes_requested: 'bg-orange-100 text-orange-800',
            rejected: 'bg-red-100 text-red-800',
        };
        const labels = {
            pending: 'Pending',
            pending_approval: 'Pending Approval',
            approved: 'Approved',
            changes_requested: 'Changes Requested',
            rejected: 'Rejected',
        };
        return (
            <span className={`px-2 py-1 text-xs rounded-full font-medium ${styles[status] || styles.pending}`}>
                {labels[status] || status}
            </span>
        );
    };

    const tabs = [
        { key: 'pending_approval', label: 'Pending Approval', count: counts.pending_approval },
        { key: 'approved', label: 'Approved', count: counts.approved },
        { key: 'changes_requested', label: 'Changes Requested', count: counts.changes_requested },
        { key: 'rejected', label: 'Rejected', count: counts.rejected },
    ];

    return (
        <AdminLayout>
            <div>
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">Onboarding Approvals</h1>
                </div>

                {/* Tabs */}
                <div className="flex space-x-1 mb-6 bg-gray-100 p-1 rounded-lg w-fit">
                    {tabs.map((tab) => (
                        <button
                            key={tab.key}
                            onClick={() => handleStatusChange(tab.key)}
                            className={`px-4 py-2 rounded-md text-sm font-medium transition-colors ${
                                currentStatus === tab.key
                                    ? 'bg-white text-gray-900 shadow-sm'
                                    : 'text-gray-600 hover:text-gray-900'
                            }`}
                        >
                            {tab.label}
                            {tab.count > 0 && (
                                <span className={`ml-2 px-2 py-0.5 text-xs rounded-full ${
                                    currentStatus === tab.key ? 'bg-pink-100 text-pink-600' : 'bg-gray-200 text-gray-600'
                                }`}>
                                    {tab.count}
                                </span>
                            )}
                        </button>
                    ))}
                </div>

                {/* Search */}
                <div className="mb-6">
                    <form onSubmit={handleSearch} className="relative">
                        <svg className="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            type="text"
                            placeholder="Search by name or email..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-10 pr-4 py-2 border border-gray-200 rounded-lg w-80 focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                    </form>
                </div>

                {/* Onboardings Table */}
                <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Retailer
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Submitted
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {onboardings?.length > 0 ? (
                                onboardings.map((onboarding) => (
                                    <tr key={onboarding.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center">
                                                <div className="w-10 h-10 rounded-full bg-gradient-to-br from-pink-400 to-purple-500 flex items-center justify-center text-white font-semibold">
                                                    {onboarding.user?.name?.charAt(0).toUpperCase() || '?'}
                                                </div>
                                                <div className="ml-3">
                                                    <p className="text-sm font-medium text-gray-900">
                                                        {onboarding.user?.name || 'N/A'}
                                                    </p>
                                                    <p className="text-sm text-gray-500">
                                                        ID: #{onboarding.user?.id}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {onboarding.user?.email || 'N/A'}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {new Date(onboarding.updated_at).toLocaleDateString('en-US', {
                                                year: 'numeric',
                                                month: 'short',
                                                day: 'numeric',
                                            })}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {getStatusBadge(onboarding.approval_status)}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm">
                                            <button
                                                onClick={() => router.visit(`/admin/onboarding-approvals/${onboarding.id}`)}
                                                className="text-pink-600 hover:text-pink-700 font-medium"
                                            >
                                                Review
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="5" className="px-6 py-12 text-center">
                                        <svg className="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h3 className="text-lg font-medium text-gray-900 mb-1">No onboardings found</h3>
                                        <p className="text-gray-500">No onboardings match the current filter</p>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
