import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/Pagination';

export default function Retailers({ retailers }) {
    const [search, setSearch] = useState('');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/admin/retailers', { search }, { preserveState: true });
    };

    return (
        <AdminLayout>
            <div>
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">Retailers Profiles</h1>
                    <button
                        onClick={() => router.visit('/admin/retailers/create')}
                        className="flex items-center px-4 py-2 rounded-lg text-white font-medium"
                        style={{ backgroundColor: '#E91E8C' }}
                    >
                        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Retailer
                    </button>
                </div>

                {/* Search */}
                <div className="mb-6">
                    <form onSubmit={handleSearch} className="flex gap-2">
                        <div className="relative flex-1">
                            <svg className="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                type="text"
                                placeholder="Search retailers by name or email..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="pl-10 pr-4 py-2 border border-gray-200 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>
                        <button
                            type="submit"
                            className="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                        >
                            Search
                        </button>
                    </form>
                </div>

                {/* Retailers Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    {retailers.data && retailers.data.length > 0 ? (
                        retailers.data.map((retailer) => (
                            <div key={retailer.id} className="bg-white rounded-xl shadow-sm overflow-hidden">
                                {/* Cover Image */}
                                <div className="h-32 bg-gradient-to-br from-gray-600 to-gray-700 relative">
                                    <div className="absolute inset-0 opacity-30">
                                        <svg className="w-full h-full" viewBox="0 0 200 100" fill="none">
                                            <path d="M0 50 Q 25 30, 50 50 T 100 50 T 150 50 T 200 50" stroke="white" strokeWidth="1" fill="none" opacity="0.5"/>
                                            <path d="M0 60 Q 25 40, 50 60 T 100 60 T 150 60 T 200 60" stroke="white" strokeWidth="1" fill="none" opacity="0.5"/>
                                            <path d="M0 70 Q 25 50, 50 70 T 100 70 T 150 70 T 200 70" stroke="white" strokeWidth="1" fill="none" opacity="0.5"/>
                                        </svg>
                                    </div>
                                </div>

                                {/* Content */}
                                <div className="p-4 text-center">
                                    <h3 className="font-semibold text-gray-900 mb-1">{retailer.name}</h3>
                                    <p className="text-sm text-gray-400 mb-1">Seller ID: #{retailer.id}</p>
                                    <p className="text-sm text-gray-500 mb-4">{retailer.email}</p>
                                    <button
                                        onClick={() => router.visit(`/admin/retailers/${retailer.id}`)}
                                        className="px-6 py-2 rounded-full text-sm font-medium border-2"
                                        style={{ borderColor: '#E91E8C', color: '#E91E8C' }}
                                    >
                                        View Profile
                                    </button>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="col-span-4 text-center py-12">
                            <div className="bg-white rounded-xl shadow-sm p-8">
                                <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <h3 className="text-lg font-medium text-gray-900 mb-2">No Retailers Yet</h3>
                                <p className="text-gray-500 mb-4">Get started by adding your first retailer</p>
                                <button
                                    onClick={() => router.visit('/admin/retailers/create')}
                                    className="px-6 py-2 rounded-full text-white font-medium"
                                    style={{ backgroundColor: '#E91E8C' }}
                                >
                                    Add Retailer
                                </button>
                            </div>
                        </div>
                    )}
                </div>

                {/* Pagination */}
                <div className="bg-white shadow rounded-lg">
                    <Pagination data={retailers} />
                </div>
            </div>
        </AdminLayout>
    );
}
