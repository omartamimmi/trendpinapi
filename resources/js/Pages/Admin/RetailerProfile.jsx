import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function RetailerProfile({ retailer, brands, subscriptions }) {
    const [activeTab, setActiveTab] = useState('brands');

    const tabs = [
        { id: 'brands', name: 'Brands & Offers' },
        { id: 'subscriptions', name: 'Subscriptions' },
        { id: 'settings', name: 'Settings' },
    ];

    // Group brands by group
    const groupedBrands = brands?.reduce((acc, brand) => {
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
                {/* Back Button */}
                <button
                    onClick={() => router.visit('/admin/retailers')}
                    className="flex items-center text-gray-600 hover:text-gray-900 mb-6"
                >
                    <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Retailers
                </button>

                {/* Profile Header */}
                <div className="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    {/* Cover */}
                    <div className="h-32 bg-gradient-to-br from-gray-600 to-gray-700 relative">
                        <div className="absolute inset-0 opacity-30">
                            <svg className="w-full h-full" viewBox="0 0 200 100" fill="none">
                                <path d="M0 50 Q 25 30, 50 50 T 100 50 T 150 50 T 200 50" stroke="white" strokeWidth="1" fill="none" opacity="0.5"/>
                                <path d="M0 60 Q 25 40, 50 60 T 100 60 T 150 60 T 200 60" stroke="white" strokeWidth="1" fill="none" opacity="0.5"/>
                            </svg>
                        </div>
                    </div>

                    {/* Profile Info */}
                    <div className="p-6">
                        <div className="flex items-start justify-between">
                            <div className="flex items-center">
                                <div className="w-16 h-16 rounded-full bg-pink-500 flex items-center justify-center text-white text-2xl font-bold -mt-12 border-4 border-white">
                                    {retailer.name?.charAt(0).toUpperCase()}
                                </div>
                                <div className="ml-4">
                                    <h1 className="text-xl font-bold text-gray-900">{retailer.name}</h1>
                                    <p className="text-sm text-gray-500">Seller ID: #{retailer.id}</p>
                                </div>
                            </div>
                            <button
                                onClick={() => router.visit(`/admin/retailers/${retailer.id}/brands`)}
                                className="px-4 py-2 rounded-lg text-white font-medium"
                                style={{ backgroundColor: '#E91E8C' }}
                            >
                                Manage Brands
                            </button>
                        </div>

                        {/* Contact Info */}
                        <div className="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div className="flex items-center text-sm text-gray-600">
                                <svg className="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {retailer.email}
                            </div>
                            {retailer.phone && (
                                <div className="flex items-center text-sm text-gray-600">
                                    <svg className="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    {retailer.phone}
                                </div>
                            )}
                            <div className="flex items-center text-sm text-gray-600">
                                <svg className="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Joined {new Date(retailer.created_at).toLocaleDateString()}
                            </div>
                        </div>

                        {/* Stats */}
                        <div className="mt-6 grid grid-cols-3 gap-4">
                            <div className="bg-gray-50 rounded-lg p-4 text-center">
                                <p className="text-2xl font-bold text-gray-900">{brands?.length || 0}</p>
                                <p className="text-sm text-gray-500">Brands</p>
                            </div>
                            <div className="bg-gray-50 rounded-lg p-4 text-center">
                                <p className="text-2xl font-bold text-gray-900">
                                    {brands?.reduce((acc, brand) => acc + (brand.branches?.length || 0), 0) || 0}
                                </p>
                                <p className="text-sm text-gray-500">Branches</p>
                            </div>
                            <div className="bg-gray-50 rounded-lg p-4 text-center">
                                <p className="text-2xl font-bold text-gray-900">{subscriptions?.length || 0}</p>
                                <p className="text-sm text-gray-500">Subscriptions</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Tabs */}
                <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div className="border-b border-gray-200">
                        <nav className="flex">
                            {tabs.map((tab) => (
                                <button
                                    key={tab.id}
                                    onClick={() => setActiveTab(tab.id)}
                                    className={`px-6 py-4 text-sm font-medium border-b-2 ${
                                        activeTab === tab.id
                                            ? 'border-pink-500 text-pink-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700'
                                    }`}
                                >
                                    {tab.name}
                                </button>
                            ))}
                        </nav>
                    </div>

                    <div className="p-6">
                        {/* Brands Tab */}
                        {activeTab === 'brands' && (
                            <div>
                                {brands?.length > 0 ? (
                                    <div className="space-y-6">
                                        {Object.entries(groupedBrands || {}).map(([groupName, groupBrands]) => (
                                            <div key={groupName}>
                                                <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                                    <svg className="w-5 h-5 mr-2 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                    </svg>
                                                    {groupName}
                                                </h3>
                                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    {groupBrands.map((brand) => (
                                                        <div key={brand.id} className="border border-gray-200 rounded-lg p-4 hover:border-pink-300 transition-colors">
                                                            <div className="flex items-start justify-between">
                                                                <div className="flex-1">
                                                                    <h4 className="font-semibold text-gray-900">{brand.name || brand.title}</h4>
                                                                    <p className="text-sm text-gray-500 mt-1 line-clamp-2">{brand.description || 'No description'}</p>
                                                                </div>
                                                                <div className="flex items-center gap-2 ml-2">
                                                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                                                        brand.status === 'publish' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                                                    }`}>
                                                                        {brand.status || 'draft'}
                                                                    </span>
                                                                    <button
                                                                        onClick={() => router.visit(`/admin/brands/${brand.id}/edit`)}
                                                                        className="p-1 text-gray-400 hover:text-pink-600"
                                                                        title="Edit brand"
                                                                    >
                                                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            {/* Branches */}
                                                            {brand.branches?.length > 0 && (
                                                                <div className="mt-3 pt-3 border-t border-gray-100">
                                                                    <p className="text-xs font-medium text-gray-500 mb-2">Branches ({brand.branches.length})</p>
                                                                    <div className="flex flex-wrap gap-1">
                                                                        {brand.branches.map((branch) => (
                                                                            <span key={branch.id} className="px-2 py-1 bg-gray-100 rounded text-xs text-gray-600">
                                                                                {branch.name}
                                                                            </span>
                                                                        ))}
                                                                    </div>
                                                                </div>
                                                            )}

                                                            {/* Location */}
                                                            {brand.location && (
                                                                <div className="mt-3 flex items-center text-xs text-gray-500">
                                                                    <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                    </svg>
                                                                    {brand.location}
                                                                </div>
                                                            )}
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-12">
                                        <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <h3 className="text-lg font-medium text-gray-900 mb-2">No Brands Yet</h3>
                                        <p className="text-gray-500 mb-4">This retailer hasn't created any brands</p>
                                        <button
                                            onClick={() => router.visit(`/admin/retailers/${retailer.id}/brands`)}
                                            className="px-6 py-2 rounded-full text-white font-medium"
                                            style={{ backgroundColor: '#E91E8C' }}
                                        >
                                            Add Brand
                                        </button>
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Subscriptions Tab */}
                        {activeTab === 'subscriptions' && (
                            <div>
                                {subscriptions?.length > 0 ? (
                                    <div className="space-y-4">
                                        {subscriptions.map((subscription) => (
                                            <div key={subscription.id} className="border border-gray-200 rounded-lg p-4">
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <h4 className="font-semibold text-gray-900">{subscription.plan?.name}</h4>
                                                        <p className="text-sm text-gray-500">
                                                            {subscription.plan?.offers_count} offers
                                                        </p>
                                                    </div>
                                                    <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                                                        subscription.status === 'active' ? 'bg-green-100 text-green-800' :
                                                        subscription.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                        'bg-gray-100 text-gray-800'
                                                    }`}>
                                                        {subscription.status}
                                                    </span>
                                                </div>
                                                <div className="mt-3 grid grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <span className="text-gray-500">Start Date:</span>
                                                        <span className="ml-2 text-gray-900">
                                                            {new Date(subscription.starts_at).toLocaleDateString()}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span className="text-gray-500">End Date:</span>
                                                        <span className="ml-2 text-gray-900">
                                                            {new Date(subscription.ends_at).toLocaleDateString()}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-12">
                                        <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <h3 className="text-lg font-medium text-gray-900 mb-2">No Subscriptions</h3>
                                        <p className="text-gray-500">This retailer hasn't subscribed to any plan</p>
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Settings Tab */}
                        {activeTab === 'settings' && (
                            <div className="space-y-6">
                                <div>
                                    <h3 className="text-lg font-semibold text-gray-900 mb-4">Account Settings</h3>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                            <input
                                                type="text"
                                                value={retailer.name}
                                                readOnly
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                            <input
                                                type="email"
                                                value={retailer.email}
                                                readOnly
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                            <input
                                                type="text"
                                                value={retailer.phone || 'Not provided'}
                                                readOnly
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                            <input
                                                type="text"
                                                value={retailer.retailer_onboarding?.status || 'Not started'}
                                                readOnly
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div className="pt-6 border-t border-gray-200">
                                    <h3 className="text-lg font-semibold text-red-600 mb-4">Danger Zone</h3>
                                    <button
                                        onClick={() => {
                                            if (confirm('Are you sure you want to delete this retailer? This action cannot be undone.')) {
                                                router.delete(`/admin/retailers/${retailer.id}`);
                                            }
                                        }}
                                        className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                                    >
                                        Delete Retailer
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
