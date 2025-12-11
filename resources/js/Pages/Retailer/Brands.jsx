import { router } from '@inertiajs/react';
import RetailerLayout from '@/Layouts/RetailerLayout';
import Pagination from '@/Components/Pagination';

export default function Brands({ brands, groups }) {
    // Group brands by group name
    const groupedBrands = (brands.data || brands).reduce((acc, brand) => {
        const groupName = brand.group?.name || 'Ungrouped';
        if (!acc[groupName]) {
            acc[groupName] = [];
        }
        acc[groupName].push(brand);
        return acc;
    }, {});

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this brand?')) {
            router.delete(`/retailer/brands/${id}`);
        }
    };

    return (
        <RetailerLayout>
            <div>
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">My Brands</h1>
                    <button
                        onClick={() => router.visit('/retailer/brands/create')}
                        className="flex items-center px-4 py-2 rounded-lg text-white font-medium"
                        style={{ backgroundColor: '#E91E8C' }}
                    >
                        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Brand
                    </button>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div className="bg-white rounded-xl shadow-sm p-4">
                        <p className="text-sm text-gray-500">Total Brands</p>
                        <p className="text-2xl font-bold text-gray-900">{brands.total || (brands.data || brands).length}</p>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4">
                        <p className="text-sm text-gray-500">Total Branches</p>
                        <p className="text-2xl font-bold text-gray-900">
                            {(brands.data || brands).reduce((acc, brand) => acc + (brand.branches?.length || 0), 0)}
                        </p>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4">
                        <p className="text-sm text-gray-500">Groups</p>
                        <p className="text-2xl font-bold text-gray-900">{Object.keys(groupedBrands).length}</p>
                    </div>
                </div>

                {/* Brands List */}
                {(brands.data || brands).length > 0 ? (
                    <div className="space-y-6">
                        {Object.entries(groupedBrands).map(([groupName, groupBrands]) => (
                            <div key={groupName} className="bg-white rounded-xl shadow-sm overflow-hidden">
                                <div className="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                    <h2 className="font-semibold text-gray-900">{groupName}</h2>
                                    <p className="text-sm text-gray-500">{groupBrands.length} brand(s)</p>
                                </div>
                                <div className="divide-y divide-gray-100">
                                    {groupBrands.map((brand) => (
                                        <div key={brand.id} className="p-6 flex items-center justify-between">
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-3">
                                                    <h3 className="font-semibold text-gray-900">{brand.title || brand.name}</h3>
                                                    <span className={`px-2 py-1 text-xs rounded-full ${
                                                        brand.status === 'publish'
                                                            ? 'bg-green-100 text-green-800'
                                                            : 'bg-gray-100 text-gray-800'
                                                    }`}>
                                                        {brand.status}
                                                    </span>
                                                </div>
                                                {brand.description && (
                                                    <p className="text-sm text-gray-500 mt-1 line-clamp-2">{brand.description}</p>
                                                )}
                                                <div className="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                                    {brand.phone_number && (
                                                        <span className="flex items-center">
                                                            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                            </svg>
                                                            {brand.phone_number}
                                                        </span>
                                                    )}
                                                    {brand.branches && brand.branches.length > 0 && (
                                                        <span className="flex items-center">
                                                            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            </svg>
                                                            {brand.branches.length} branch(es)
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <button
                                                    onClick={() => router.visit(`/retailer/brands/${brand.id}/edit`)}
                                                    className="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
                                                >
                                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button
                                                    onClick={() => handleDelete(brand.id)}
                                                    className="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-gray-100"
                                                >
                                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="bg-white rounded-xl shadow-sm p-12 text-center">
                        <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <h3 className="text-lg font-medium text-gray-900 mb-2">No Brands Yet</h3>
                        <p className="text-gray-500 mb-4">Get started by adding your first brand</p>
                        <button
                            onClick={() => router.visit('/retailer/brands/create')}
                            className="px-6 py-2 rounded-lg text-white font-medium"
                            style={{ backgroundColor: '#E91E8C' }}
                        >
                            Add Your First Brand
                        </button>
                    </div>
                )}

                {/* Pagination */}
                {brands.data && <div className="mt-6">
                    <Pagination data={brands} />
                </div>}
            </div>
        </RetailerLayout>
    );
}
