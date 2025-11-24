import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function OnboardingReview({ onboarding, retailer, brands, subscriptions }) {
    const [showModal, setShowModal] = useState(null);
    const [adminNotes, setAdminNotes] = useState('');
    const [processing, setProcessing] = useState(false);

    const handleApprove = () => {
        setProcessing(true);
        router.post(`/admin/onboarding-approvals/${onboarding.id}/approve`, {
            admin_notes: adminNotes,
        }, {
            onFinish: () => {
                setProcessing(false);
                setShowModal(null);
                setAdminNotes('');
            },
        });
    };

    const handleRequestChanges = () => {
        if (!adminNotes.trim()) {
            alert('Please provide details about what changes are required.');
            return;
        }
        setProcessing(true);
        router.post(`/admin/onboarding-approvals/${onboarding.id}/request-changes`, {
            admin_notes: adminNotes,
        }, {
            onFinish: () => {
                setProcessing(false);
                setShowModal(null);
                setAdminNotes('');
            },
        });
    };

    const handleReject = () => {
        if (!adminNotes.trim()) {
            alert('Please provide a reason for rejection.');
            return;
        }
        setProcessing(true);
        router.post(`/admin/onboarding-approvals/${onboarding.id}/reject`, {
            admin_notes: adminNotes,
        }, {
            onFinish: () => {
                setProcessing(false);
                setShowModal(null);
                setAdminNotes('');
            },
        });
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
            <span className={`px-3 py-1 text-sm rounded-full font-medium ${styles[status] || styles.pending}`}>
                {labels[status] || status}
            </span>
        );
    };

    // Group brands by group name
    const groupedBrands = brands.reduce((acc, brand) => {
        const groupName = brand.group?.name || 'Ungrouped';
        if (!acc[groupName]) {
            acc[groupName] = [];
        }
        acc[groupName].push(brand);
        return acc;
    }, {});

    return (
        <AdminLayout>
            <div>
                {/* Header */}
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center space-x-4">
                        <button
                            onClick={() => router.visit('/admin/onboarding-approvals')}
                            className="p-2 hover:bg-gray-100 rounded-lg"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">Onboarding Review</h1>
                            <p className="text-gray-500">Review and approve retailer onboarding</p>
                        </div>
                    </div>
                    {getStatusBadge(onboarding.approval_status)}
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Left Column - Retailer Info */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Retailer Details */}
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Retailer Information</h2>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm text-gray-500">Name</p>
                                    <p className="font-medium">{retailer.name}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-500">Email</p>
                                    <p className="font-medium">{retailer.email}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-500">Phone</p>
                                    <p className="font-medium">{retailer.phone || 'N/A'}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-500">Registered</p>
                                    <p className="font-medium">
                                        {new Date(retailer.created_at).toLocaleDateString('en-US', {
                                            year: 'numeric',
                                            month: 'short',
                                            day: 'numeric',
                                        })}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Onboarding Steps */}
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Completed Steps</h2>
                            <div className="space-y-3">
                                {(onboarding.completed_steps || []).map((step, index) => (
                                    <div key={index} className="flex items-center space-x-3">
                                        <div className="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center">
                                            <svg className="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span className="text-sm text-gray-700 capitalize">
                                            {step.replace(/_/g, ' ')}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Brands */}
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">
                                Brands ({brands.length})
                            </h2>
                            {Object.keys(groupedBrands).length > 0 ? (
                                <div className="space-y-4">
                                    {Object.entries(groupedBrands).map(([groupName, groupBrands]) => (
                                        <div key={groupName}>
                                            <h3 className="text-sm font-medium text-gray-500 mb-2">{groupName}</h3>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                {groupBrands.map((brand) => (
                                                    <div key={brand.id} className="border border-gray-200 rounded-lg p-3">
                                                        <p className="font-medium text-gray-900">{brand.title || brand.name}</p>
                                                        {brand.description && (
                                                            <p className="text-sm text-gray-500 mt-1 line-clamp-2">
                                                                {brand.description}
                                                            </p>
                                                        )}
                                                        {brand.branches && brand.branches.length > 0 && (
                                                            <p className="text-xs text-gray-400 mt-2">
                                                                {brand.branches.length} branch(es)
                                                            </p>
                                                        )}
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-gray-500 text-sm">No brands submitted</p>
                            )}
                        </div>

                        {/* Subscriptions */}
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Subscriptions</h2>
                            {subscriptions?.length > 0 ? (
                                <div className="space-y-3">
                                    {subscriptions.map((subscription) => (
                                        <div key={subscription.id} className="border border-gray-200 rounded-lg p-3">
                                            <div className="flex justify-between items-center">
                                                <div>
                                                    <p className="font-medium">{subscription.plan?.name}</p>
                                                    <p className="text-sm text-gray-500">
                                                        {subscription.plan?.price} JOD / {subscription.plan?.billing_period}
                                                    </p>
                                                </div>
                                                <span className={`px-2 py-1 text-xs rounded-full ${
                                                    subscription.status === 'active'
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-gray-100 text-gray-800'
                                                }`}>
                                                    {subscription.status}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-gray-500 text-sm">No subscriptions</p>
                            )}
                        </div>
                    </div>

                    {/* Right Column - Actions */}
                    <div className="space-y-6">
                        {/* Previous Admin Notes */}
                        {onboarding.admin_notes && (
                            <div className="bg-white rounded-xl shadow-sm p-6">
                                <h2 className="text-lg font-semibold text-gray-900 mb-4">Previous Notes</h2>
                                <p className="text-sm text-gray-600 whitespace-pre-wrap">{onboarding.admin_notes}</p>
                                {onboarding.approver && (
                                    <p className="text-xs text-gray-400 mt-2">
                                        By {onboarding.approver.name} on {new Date(onboarding.approved_at).toLocaleDateString()}
                                    </p>
                                )}
                            </div>
                        )}

                        {/* Action Buttons */}
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
                            <div className="space-y-3">
                                <button
                                    onClick={() => setShowModal('approve')}
                                    className="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium flex items-center justify-center"
                                >
                                    <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Approve
                                </button>
                                <button
                                    onClick={() => setShowModal('changes')}
                                    className="w-full px-4 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium flex items-center justify-center"
                                >
                                    <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Request Changes
                                </button>
                                <button
                                    onClick={() => setShowModal('reject')}
                                    className="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium flex items-center justify-center"
                                >
                                    <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Reject
                                </button>
                            </div>
                        </div>

                        {/* View Profile Link */}
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <button
                                onClick={() => router.visit(`/admin/retailers/${retailer.id}`)}
                                className="w-full px-4 py-3 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-medium"
                            >
                                View Full Profile
                            </button>
                        </div>
                    </div>
                </div>

                {/* Modal */}
                {showModal && (
                    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div className="bg-white rounded-xl p-6 w-full max-w-md mx-4">
                            <h3 className="text-lg font-semibold mb-4">
                                {showModal === 'approve' && 'Approve Onboarding'}
                                {showModal === 'changes' && 'Request Changes'}
                                {showModal === 'reject' && 'Reject Onboarding'}
                            </h3>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    {showModal === 'approve' ? 'Notes (optional)' : 'Notes (required)'}
                                </label>
                                <textarea
                                    value={adminNotes}
                                    onChange={(e) => setAdminNotes(e.target.value)}
                                    rows={4}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    placeholder={
                                        showModal === 'approve'
                                            ? 'Add any notes for the retailer...'
                                            : showModal === 'changes'
                                            ? 'Describe what changes are required...'
                                            : 'Provide reason for rejection...'
                                    }
                                />
                            </div>
                            <div className="flex space-x-3">
                                <button
                                    onClick={() => {
                                        setShowModal(null);
                                        setAdminNotes('');
                                    }}
                                    className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                                    disabled={processing}
                                >
                                    Cancel
                                </button>
                                <button
                                    onClick={() => {
                                        if (showModal === 'approve') handleApprove();
                                        if (showModal === 'changes') handleRequestChanges();
                                        if (showModal === 'reject') handleReject();
                                    }}
                                    disabled={processing}
                                    className={`flex-1 px-4 py-2 text-white rounded-lg font-medium ${
                                        showModal === 'approve' ? 'bg-green-600 hover:bg-green-700' :
                                        showModal === 'changes' ? 'bg-orange-600 hover:bg-orange-700' :
                                        'bg-red-600 hover:bg-red-700'
                                    } ${processing ? 'opacity-50 cursor-not-allowed' : ''}`}
                                >
                                    {processing ? 'Processing...' : 'Confirm'}
                                </button>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
