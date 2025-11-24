import { router } from '@inertiajs/react';
import RetailerLayout from '@/Layouts/RetailerLayout';

export default function Offers({ offers }) {
    return (
        <RetailerLayout>
            <div>
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">Offers & Discounts</h1>
                    <button
                        onClick={() => router.visit('/retailer/offers/create')}
                        className="flex items-center px-4 py-2 rounded-lg text-white font-medium"
                        style={{ backgroundColor: '#E91E8C' }}
                    >
                        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Offer
                    </button>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                    <div className="bg-white rounded-xl shadow-sm p-4">
                        <p className="text-sm text-gray-500">Total Offers</p>
                        <p className="text-2xl font-bold text-gray-900">{offers?.length || 0}</p>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4">
                        <p className="text-sm text-gray-500">Active</p>
                        <p className="text-2xl font-bold text-green-600">
                            {offers?.filter(o => o.status === 'active').length || 0}
                        </p>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4">
                        <p className="text-sm text-gray-500">Total Claims</p>
                        <p className="text-2xl font-bold text-gray-900">0</p>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4">
                        <p className="text-sm text-gray-500">Total Views</p>
                        <p className="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>

                {/* Offers List */}
                {offers && offers.length > 0 ? (
                    <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Offer</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Claims</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {offers.map((offer) => (
                                    <tr key={offer.id}>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="font-medium text-gray-900">{offer.name}</div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {offer.type}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {offer.claims || 0}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`px-2 py-1 text-xs rounded-full ${
                                                offer.status === 'active'
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-gray-100 text-gray-800'
                                            }`}>
                                                {offer.status}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm">
                                            <div className="flex items-center space-x-3">
                                                <button
                                                    onClick={() => router.visit(`/retailer/offers/${offer.id}/edit`)}
                                                    className="text-pink-600 hover:text-pink-700 font-medium"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    onClick={() => {
                                                        if (confirm('Are you sure you want to delete this offer?')) {
                                                            router.delete(`/retailer/offers/${offer.id}`);
                                                        }
                                                    }}
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
                ) : (
                    <div className="bg-white rounded-xl shadow-sm p-12 text-center">
                        <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <h3 className="text-lg font-medium text-gray-900 mb-2">No Offers Yet</h3>
                        <p className="text-gray-500 mb-4">Create your first offer to attract customers</p>
                        <button
                            onClick={() => router.visit('/retailer/offers/create')}
                            className="px-6 py-2 rounded-lg text-white font-medium"
                            style={{ backgroundColor: '#E91E8C' }}
                        >
                            Create Your First Offer
                        </button>
                    </div>
                )}
            </div>
        </RetailerLayout>
    );
}
