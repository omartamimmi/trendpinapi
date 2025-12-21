import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useToast } from '@/Components/Toast';
import { useConfirm } from '@/Components/ConfirmDialog';

export default function OfferDetail({ offer }) {
    const toast = useToast();
    const confirm = useConfirm();

    const statusColors = {
        draft: 'bg-gray-100 text-gray-800',
        pending: 'bg-yellow-100 text-yellow-800',
        active: 'bg-green-100 text-green-800',
        paused: 'bg-blue-100 text-blue-800',
        expired: 'bg-red-100 text-red-800',
        rejected: 'bg-red-100 text-red-800',
    };

    const handleStatusChange = (status) => {
        router.put(`/admin/bank-offer/offers/${offer.id}/status`, { status }, {
            onSuccess: () => toast.success('Status updated'),
            onError: () => toast.error('Failed to update status'),
        });
    };

    const handleApprove = () => {
        confirm({
            title: 'Approve Offer',
            message: 'Are you sure you want to approve this offer?',
            confirmText: 'Approve',
            type: 'success',
            onConfirm: () => {
                router.put(`/admin/bank-offer/offers/${offer.id}/approve`, {}, {
                    onSuccess: () => toast.success('Offer approved'),
                });
            },
        });
    };

    const handleReject = () => {
        confirm({
            title: 'Reject Offer',
            message: 'Are you sure you want to reject this offer?',
            confirmText: 'Reject',
            type: 'danger',
            onConfirm: () => {
                router.put(`/admin/bank-offer/offers/${offer.id}/reject`, {}, {
                    onSuccess: () => toast.success('Offer rejected'),
                });
            },
        });
    };

    return (
        <AdminLayout>
            <div className="py-6">
                <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="md:flex md:items-center md:justify-between mb-6">
                        <div className="flex-1 min-w-0">
                            <Link href="/admin/bank-offer/offers" className="text-sm text-pink-600 hover:text-pink-900 mb-2 inline-block">
                                &larr; Back to Offers
                            </Link>
                            <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                                {offer.title}
                            </h2>
                            {offer.title_ar && <p className="text-lg text-gray-500">{offer.title_ar}</p>}
                        </div>
                        <div className="mt-4 flex gap-2 md:mt-0 md:ml-4">
                            {offer.status === 'pending' && (
                                <>
                                    <button onClick={handleApprove} className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                        Approve
                                    </button>
                                    <button onClick={handleReject} className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                                        Reject
                                    </button>
                                </>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Main Info */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Offer Details */}
                            <div className="bg-white shadow sm:rounded-lg p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Offer Details</h3>
                                <dl className="grid grid-cols-2 gap-4">
                                    <div>
                                        <dt className="text-sm text-gray-500">Bank</dt>
                                        <dd className="text-sm font-medium text-gray-900 flex items-center mt-1">
                                            {offer.bank?.logo && <img src={offer.bank.logo.url} alt="" className="h-6 w-6 rounded mr-2" />}
                                            {offer.bank?.name}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm text-gray-500">Card Type</dt>
                                        <dd className="text-sm font-medium text-gray-900 mt-1">{offer.card_type?.name || 'All Cards'}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm text-gray-500">Offer Type</dt>
                                        <dd className="text-sm font-medium text-gray-900 mt-1 capitalize">{offer.offer_type}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm text-gray-500">Offer Value</dt>
                                        <dd className="text-sm font-medium text-gray-900 mt-1">
                                            {offer.offer_type === 'percentage' ? `${offer.offer_value}%` : `JOD ${offer.offer_value}`}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm text-gray-500">Min Purchase</dt>
                                        <dd className="text-sm font-medium text-gray-900 mt-1">
                                            {offer.min_purchase_amount ? `JOD ${offer.min_purchase_amount}` : 'No minimum'}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm text-gray-500">Max Discount</dt>
                                        <dd className="text-sm font-medium text-gray-900 mt-1">
                                            {offer.max_discount_amount ? `JOD ${offer.max_discount_amount}` : 'No cap'}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm text-gray-500">Start Date</dt>
                                        <dd className="text-sm font-medium text-gray-900 mt-1">{new Date(offer.start_date).toLocaleDateString()}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm text-gray-500">End Date</dt>
                                        <dd className="text-sm font-medium text-gray-900 mt-1">{new Date(offer.end_date).toLocaleDateString()}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm text-gray-500">Redemption Type</dt>
                                        <dd className="text-sm font-medium text-gray-900 mt-1 capitalize">{offer.redemption_type?.replace('_', ' ')}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm text-gray-500">Claims</dt>
                                        <dd className="text-sm font-medium text-gray-900 mt-1">
                                            {offer.total_claims} / {offer.max_claims || 'Unlimited'}
                                        </dd>
                                    </div>
                                </dl>

                                {offer.description && (
                                    <div className="mt-6">
                                        <dt className="text-sm text-gray-500">Description</dt>
                                        <dd className="text-sm text-gray-900 mt-1">{offer.description}</dd>
                                    </div>
                                )}

                                {offer.terms && (
                                    <div className="mt-4">
                                        <dt className="text-sm text-gray-500">Terms & Conditions</dt>
                                        <dd className="text-sm text-gray-900 mt-1">{offer.terms}</dd>
                                    </div>
                                )}
                            </div>

                            {/* Participating Retailers */}
                            <div className="bg-white shadow sm:rounded-lg p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Participating Retailers</h3>
                                {offer.participating_brands && offer.participating_brands.length > 0 ? (
                                    <ul className="divide-y divide-gray-200">
                                        {offer.participating_brands.map((pb) => (
                                            <li key={pb.id} className="py-3 flex items-center justify-between">
                                                <div className="flex items-center">
                                                    {pb.brand?.logo && <img src={pb.brand.logo.url} alt="" className="h-8 w-8 rounded mr-3" />}
                                                    <div>
                                                        <div className="text-sm font-medium text-gray-900">{pb.brand?.name}</div>
                                                        <div className="text-xs text-gray-500">
                                                            {pb.all_branches ? 'All branches' : `${pb.branch_ids?.length || 0} branches`}
                                                        </div>
                                                    </div>
                                                </div>
                                                <span className={`px-2 text-xs font-semibold rounded-full ${
                                                    pb.status === 'approved' ? 'bg-green-100 text-green-800' :
                                                    pb.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                    'bg-red-100 text-red-800'
                                                }`}>
                                                    {pb.status}
                                                </span>
                                            </li>
                                        ))}
                                    </ul>
                                ) : (
                                    <p className="text-sm text-gray-500">No retailers have joined this offer yet.</p>
                                )}
                            </div>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Status Card */}
                            <div className="bg-white shadow sm:rounded-lg p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Status</h3>
                                <span className={`px-3 py-1 text-sm font-semibold rounded-full ${statusColors[offer.status]}`}>
                                    {offer.status.toUpperCase()}
                                </span>

                                {offer.status !== 'pending' && (
                                    <div className="mt-4">
                                        <label className="text-sm text-gray-500">Change Status</label>
                                        <select
                                            value={offer.status}
                                            onChange={(e) => handleStatusChange(e.target.value)}
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                        >
                                            <option value="active">Active</option>
                                            <option value="paused">Paused</option>
                                            <option value="expired">Expired</option>
                                        </select>
                                    </div>
                                )}
                            </div>

                            {/* Meta Info */}
                            <div className="bg-white shadow sm:rounded-lg p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Info</h3>
                                <dl className="space-y-3">
                                    <div>
                                        <dt className="text-xs text-gray-500">Created By</dt>
                                        <dd className="text-sm text-gray-900">{offer.creator?.name || 'Unknown'}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-xs text-gray-500">Created At</dt>
                                        <dd className="text-sm text-gray-900">{new Date(offer.created_at).toLocaleString()}</dd>
                                    </div>
                                    {offer.approved_by && (
                                        <>
                                            <div>
                                                <dt className="text-xs text-gray-500">Approved By</dt>
                                                <dd className="text-sm text-gray-900">{offer.approver?.name}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-xs text-gray-500">Approved At</dt>
                                                <dd className="text-sm text-gray-900">{new Date(offer.approved_at).toLocaleString()}</dd>
                                            </div>
                                        </>
                                    )}
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
