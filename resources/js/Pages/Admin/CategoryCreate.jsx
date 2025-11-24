import { useForm, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function CategoryCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        name_ar: '',
        description: '',
        description_ar: '',
        status: 'draft',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/categories');
    };

    return (
        <AdminLayout>
            <div className="py-6">
                <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-6">
                        <Link
                            href="/admin/categories"
                            className="text-sm text-gray-500 hover:text-gray-700 mb-2 inline-block"
                        >
                            ← Back to Categories
                        </Link>
                        <h2 className="text-2xl font-bold text-gray-900">Create Category</h2>
                    </div>

                    {/* Form */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Name (English) */}
                            <div>
                                <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                    Name (English) *
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

                            {/* Name (Arabic) */}
                            <div>
                                <label htmlFor="name_ar" className="block text-sm font-medium text-gray-700">
                                    Name (Arabic)
                                </label>
                                <input
                                    type="text"
                                    id="name_ar"
                                    value={data.name_ar}
                                    onChange={(e) => setData('name_ar', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                                    dir="rtl"
                                />
                                {errors.name_ar && <p className="mt-1 text-sm text-red-600">{errors.name_ar}</p>}
                            </div>

                            {/* Description (English) */}
                            <div>
                                <label htmlFor="description" className="block text-sm font-medium text-gray-700">
                                    Description (English) *
                                </label>
                                <textarea
                                    id="description"
                                    rows="3"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                                    required
                                />
                                {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
                            </div>

                            {/* Description (Arabic) */}
                            <div>
                                <label htmlFor="description_ar" className="block text-sm font-medium text-gray-700">
                                    Description (Arabic)
                                </label>
                                <textarea
                                    id="description_ar"
                                    rows="3"
                                    value={data.description_ar}
                                    onChange={(e) => setData('description_ar', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                                    dir="rtl"
                                />
                                {errors.description_ar && <p className="mt-1 text-sm text-red-600">{errors.description_ar}</p>}
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

                            {/* Actions */}
                            <div className="flex justify-end space-x-3">
                                <Link
                                    href="/admin/categories"
                                    className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pink-600 hover:bg-pink-700 disabled:opacity-50"
                                >
                                    {processing ? 'Creating...' : 'Create Category'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
