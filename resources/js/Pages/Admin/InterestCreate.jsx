import { useForm, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function InterestCreate({ categories }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        status: 'draft',
        category_ids: [],
    });

    const handleCategoryToggle = (categoryId) => {
        if (data.category_ids.includes(categoryId)) {
            setData('category_ids', data.category_ids.filter(id => id !== categoryId));
        } else {
            setData('category_ids', [...data.category_ids, categoryId]);
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/interests');
    };

    return (
        <AdminLayout>
            <div className="py-6">
                <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-6">
                        <Link
                            href="/admin/interests"
                            className="text-sm text-gray-500 hover:text-gray-700 mb-2 inline-block"
                        >
                            ← Back to Interests
                        </Link>
                        <h2 className="text-2xl font-bold text-gray-900">Create Interest</h2>
                    </div>

                    {/* Form */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Name */}
                            <div>
                                <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                    Name *
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                                    required
                                />
                                {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                            </div>

                            {/* Status */}
                            <div>
                                <label htmlFor="status" className="block text-sm font-medium text-gray-700">
                                    Status *
                                </label>
                                <select
                                    id="status"
                                    value={data.status}
                                    onChange={(e) => setData('status', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                                {errors.status && <p className="mt-1 text-sm text-red-600">{errors.status}</p>}
                            </div>

                            {/* Categories */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Categories
                                </label>
                                <div className="border border-gray-300 rounded-md p-4 max-h-60 overflow-y-auto">
                                    {categories && categories.length > 0 ? (
                                        <div className="space-y-2">
                                            {categories.map((category) => (
                                                <label key={category.id} className="flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        checked={data.category_ids.includes(category.id)}
                                                        onChange={() => handleCategoryToggle(category.id)}
                                                        className="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                                                    />
                                                    <span className="ml-2 text-sm text-gray-700">{category.name}</span>
                                                </label>
                                            ))}
                                        </div>
                                    ) : (
                                        <p className="text-sm text-gray-500">No categories available</p>
                                    )}
                                </div>
                                {errors.category_ids && <p className="mt-1 text-sm text-red-600">{errors.category_ids}</p>}
                            </div>

                            {/* Actions */}
                            <div className="flex justify-end space-x-3">
                                <Link
                                    href="/admin/interests"
                                    className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pink-600 hover:bg-pink-700 disabled:opacity-50"
                                >
                                    {processing ? 'Creating...' : 'Create Interest'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
