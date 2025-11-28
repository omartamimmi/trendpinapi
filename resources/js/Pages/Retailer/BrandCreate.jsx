import { router, useForm } from '@inertiajs/react';
import RetailerLayout from '@/Layouts/RetailerLayout';

export default function BrandCreate({ groups }) {
    const form = useForm({
        name: '',
        title: '',
        title_ar: '',
        description: '',
        description_ar: '',
        group_id: '',
        phone_number: '',
        location: '',
        lat: '',
        lng: '',
        website_link: '',
        insta_link: '',
        facebook_link: '',
        branches: [{ name: '' }],
    });

    const addBranch = () => {
        form.setData('branches', [...form.data.branches, { name: '' }]);
    };

    const removeBranch = (index) => {
        const branches = form.data.branches.filter((_, i) => i !== index);
        form.setData('branches', branches.length ? branches : [{ name: '' }]);
    };

    const updateBranch = (index, value) => {
        const branches = [...form.data.branches];
        branches[index] = { name: value };
        form.setData('branches', branches);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        form.post('/retailer/brands');
    };

    return (
        <RetailerLayout>
            <div>
                {/* Header */}
                <div className="flex items-center space-x-4 mb-6">
                    <button
                        onClick={() => router.visit('/retailer/brands')}
                        className="p-2 hover:bg-gray-100 rounded-lg"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <h1 className="text-2xl font-bold text-gray-900">Add New Brand</h1>
                </div>

                <form onSubmit={handleSubmit}>
                    <div className="space-y-6">
                        {/* Basic Info */}
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Brand Name *</label>
                                    <input
                                        type="text"
                                        value={form.data.name}
                                        onChange={(e) => form.setData('name', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Display Title *</label>
                                    <input
                                        type="text"
                                        value={form.data.title}
                                        onChange={(e) => form.setData('title', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Title (Arabic)</label>
                                    <input
                                        type="text"
                                        value={form.data.title_ar}
                                        onChange={(e) => form.setData('title_ar', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                        dir="rtl"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Group</label>
                                    <select
                                        value={form.data.group_id}
                                        onChange={(e) => form.setData('group_id', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                    >
                                        <option value="">Select Group</option>
                                        {groups.map((group) => (
                                            <option key={group.id} value={group.id}>{group.name}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="md:col-span-2">
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea
                                        value={form.data.description}
                                        onChange={(e) => form.setData('description', e.target.value)}
                                        rows={3}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Contact & Location */}
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Contact & Location</h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                    <input
                                        type="text"
                                        value={form.data.phone_number}
                                        onChange={(e) => form.setData('phone_number', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                    <input
                                        type="text"
                                        value={form.data.location}
                                        onChange={(e) => form.setData('location', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Branches */}
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <div className="flex items-center justify-between mb-4">
                                <h2 className="text-lg font-semibold text-gray-900">Branches</h2>
                                <button
                                    type="button"
                                    onClick={addBranch}
                                    className="text-sm text-pink-600 hover:text-pink-700 font-medium"
                                >
                                    + Add Branch
                                </button>
                            </div>
                            <div className="space-y-3">
                                {form.data.branches.map((branch, index) => (
                                    <div key={index} className="flex items-center space-x-3">
                                        <input
                                            type="text"
                                            value={branch.name}
                                            onChange={(e) => updateBranch(index, e.target.value)}
                                            placeholder="Branch name"
                                            className="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                        />
                                        {form.data.branches.length > 1 && (
                                            <button
                                                type="button"
                                                onClick={() => removeBranch(index)}
                                                className="p-2 text-red-500 hover:bg-red-50 rounded-lg"
                                            >
                                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Submit */}
                        <div className="flex justify-end space-x-3">
                            <button
                                type="button"
                                onClick={() => router.visit('/retailer/brands')}
                                className="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={form.processing}
                                className="px-6 py-2 rounded-lg text-white font-medium disabled:opacity-50"
                                style={{ backgroundColor: '#E91E8C' }}
                            >
                                {form.processing ? 'Creating...' : 'Create Brand'}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </RetailerLayout>
    );
}
