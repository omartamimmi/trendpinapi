import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function BrandEdit({ brand, groups, retailer }) {
    const [formData, setFormData] = useState({
        name: brand.name || '',
        title: brand.title || '',
        title_ar: brand.title_ar || '',
        description: brand.description || '',
        description_ar: brand.description_ar || '',
        group_id: brand.group_id || '',
        phone_number: brand.phone_number || '',
        location: brand.location || '',
        lat: brand.lat || '',
        lng: brand.lng || '',
        website_link: brand.website_link || '',
        insta_link: brand.insta_link || '',
        facebook_link: brand.facebook_link || '',
        status: brand.status || 'draft',
    });

    const [branches, setBranches] = useState(
        brand.branches?.map(b => ({ id: b.id, name: b.name })) || []
    );

    const [newBranch, setNewBranch] = useState('');

    const handleSubmit = (e) => {
        e.preventDefault();
        router.put(`/admin/brands/${brand.id}`, {
            ...formData,
            branches: branches,
        });
    };

    const addBranch = () => {
        if (newBranch.trim()) {
            setBranches([...branches, { id: null, name: newBranch.trim() }]);
            setNewBranch('');
        }
    };

    const removeBranch = (index) => {
        setBranches(branches.filter((_, i) => i !== index));
    };

    return (
        <AdminLayout>
            <div>
                {/* Back Button */}
                <button
                    onClick={() => router.visit(`/admin/retailers/${retailer.id}`)}
                    className="flex items-center text-gray-600 hover:text-gray-900 mb-6"
                >
                    <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to {retailer.name}'s Profile
                </button>

                <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div className="p-6 border-b border-gray-200">
                        <h1 className="text-xl font-bold text-gray-900">Edit Brand: {brand.name || brand.title}</h1>
                    </div>

                    <form onSubmit={handleSubmit} className="p-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Basic Info */}
                            <div className="md:col-span-2">
                                <h3 className="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input
                                    type="text"
                                    value={formData.name}
                                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input
                                    type="text"
                                    value={formData.title}
                                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Title (Arabic)</label>
                                <input
                                    type="text"
                                    value={formData.title_ar}
                                    onChange={(e) => setFormData({ ...formData, title_ar: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    dir="rtl"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Group</label>
                                <select
                                    value={formData.group_id}
                                    onChange={(e) => setFormData({ ...formData, group_id: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                >
                                    <option value="">No Group</option>
                                    {groups?.map((group) => (
                                        <option key={group.id} value={group.id}>{group.name}</option>
                                    ))}
                                </select>
                            </div>

                            <div className="md:col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea
                                    value={formData.description}
                                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                    rows="3"
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>

                            <div className="md:col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">Description (Arabic)</label>
                                <textarea
                                    value={formData.description_ar}
                                    onChange={(e) => setFormData({ ...formData, description_ar: e.target.value })}
                                    rows="3"
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    dir="rtl"
                                />
                            </div>

                            {/* Contact & Location */}
                            <div className="md:col-span-2 mt-6">
                                <h3 className="text-lg font-semibold text-gray-900 mb-4">Contact & Location</h3>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input
                                    type="text"
                                    value={formData.phone_number}
                                    onChange={(e) => setFormData({ ...formData, phone_number: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <input
                                    type="text"
                                    value={formData.location}
                                    onChange={(e) => setFormData({ ...formData, location: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                                <input
                                    type="text"
                                    value={formData.lat}
                                    onChange={(e) => setFormData({ ...formData, lat: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                                <input
                                    type="text"
                                    value={formData.lng}
                                    onChange={(e) => setFormData({ ...formData, lng: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>

                            {/* Social Links */}
                            <div className="md:col-span-2 mt-6">
                                <h3 className="text-lg font-semibold text-gray-900 mb-4">Social Links</h3>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Website</label>
                                <input
                                    type="url"
                                    value={formData.website_link}
                                    onChange={(e) => setFormData({ ...formData, website_link: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                                <input
                                    type="url"
                                    value={formData.insta_link}
                                    onChange={(e) => setFormData({ ...formData, insta_link: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                                <input
                                    type="url"
                                    value={formData.facebook_link}
                                    onChange={(e) => setFormData({ ...formData, facebook_link: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select
                                    value={formData.status}
                                    onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="publish">Published</option>
                                </select>
                            </div>

                            {/* Branches */}
                            <div className="md:col-span-2 mt-6">
                                <h3 className="text-lg font-semibold text-gray-900 mb-4">Branches</h3>

                                <div className="space-y-3">
                                    {branches.map((branch, index) => (
                                        <div key={index} className="flex items-center gap-2">
                                            <input
                                                type="text"
                                                value={branch.name}
                                                onChange={(e) => {
                                                    const updated = [...branches];
                                                    updated[index].name = e.target.value;
                                                    setBranches(updated);
                                                }}
                                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                            />
                                            <button
                                                type="button"
                                                onClick={() => removeBranch(index)}
                                                className="p-2 text-red-600 hover:text-red-800"
                                            >
                                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    ))}

                                    <div className="flex items-center gap-2">
                                        <input
                                            type="text"
                                            value={newBranch}
                                            onChange={(e) => setNewBranch(e.target.value)}
                                            placeholder="Add new branch..."
                                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                            onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addBranch())}
                                        />
                                        <button
                                            type="button"
                                            onClick={addBranch}
                                            className="p-2 text-pink-600 hover:text-pink-800"
                                        >
                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Actions */}
                        <div className="mt-8 flex justify-end gap-4">
                            <button
                                type="button"
                                onClick={() => router.visit(`/admin/retailers/${retailer.id}`)}
                                className="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                className="px-6 py-2 rounded-lg text-white font-medium"
                                style={{ backgroundColor: '#E91E8C' }}
                            >
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
