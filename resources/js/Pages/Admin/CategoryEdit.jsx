import { useForm, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function CategoryEdit({ category }) {
    const { data, setData, put, processing, errors } = useForm({
        name: category.name || '',
        name_ar: category.name_ar || '',
        description: category.description || '',
        description_ar: category.description_ar || '',
        status: category.status || 'draft',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(`/admin/categories/${category.id}`);
    };

    return (
        <AdminLayout>
            <div className="max-w-4xl mx-auto">
                {/* Header */}
                <div className="mb-8">
                    <Link
                        href="/admin/categories"
                        className="inline-flex items-center text-sm text-gray-500 hover:text-pink-600 transition-colors mb-4"
                    >
                        <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Categories
                    </Link>
                    <div className="flex items-center gap-3">
                        <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-500 to-purple-600 flex items-center justify-center">
                            <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </div>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">Edit Category</h1>
                            <p className="text-sm text-gray-500">Update category details and settings</p>
                        </div>
                    </div>
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* English Section */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                            <div className="flex items-center gap-2">
                                <span className="text-lg">🇬🇧</span>
                                <h2 className="text-sm font-semibold text-gray-700">English Content</h2>
                            </div>
                        </div>
                        <div className="p-6 space-y-5">
                            {/* Name (English) */}
                            <div>
                                <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
                                    Category Name <span className="text-pink-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Enter category name"
                                    className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                    required
                                />
                                {errors.name && <p className="mt-2 text-sm text-red-500">{errors.name}</p>}
                            </div>

                            {/* Description (English) */}
                            <div>
                                <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
                                    Description <span className="text-pink-500">*</span>
                                </label>
                                <textarea
                                    id="description"
                                    rows="4"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Enter category description"
                                    className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all resize-none"
                                    required
                                />
                                {errors.description && <p className="mt-2 text-sm text-red-500">{errors.description}</p>}
                            </div>
                        </div>
                    </div>

                    {/* Arabic Section */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                            <div className="flex items-center gap-2">
                                <span className="text-lg">🇸🇦</span>
                                <h2 className="text-sm font-semibold text-gray-700">Arabic Content</h2>
                                <span className="text-xs text-gray-400">(Optional)</span>
                            </div>
                        </div>
                        <div className="p-6 space-y-5">
                            {/* Name (Arabic) */}
                            <div>
                                <label htmlFor="name_ar" className="block text-sm font-medium text-gray-700 mb-2">
                                    Category Name (Arabic)
                                </label>
                                <input
                                    type="text"
                                    id="name_ar"
                                    value={data.name_ar}
                                    onChange={(e) => setData('name_ar', e.target.value)}
                                    placeholder="أدخل اسم التصنيف"
                                    className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all text-right"
                                    dir="rtl"
                                />
                                {errors.name_ar && <p className="mt-2 text-sm text-red-500">{errors.name_ar}</p>}
                            </div>

                            {/* Description (Arabic) */}
                            <div>
                                <label htmlFor="description_ar" className="block text-sm font-medium text-gray-700 mb-2">
                                    Description (Arabic)
                                </label>
                                <textarea
                                    id="description_ar"
                                    rows="4"
                                    value={data.description_ar}
                                    onChange={(e) => setData('description_ar', e.target.value)}
                                    placeholder="أدخل وصف التصنيف"
                                    className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all resize-none text-right"
                                    dir="rtl"
                                />
                                {errors.description_ar && <p className="mt-2 text-sm text-red-500">{errors.description_ar}</p>}
                            </div>
                        </div>
                    </div>

                    {/* Settings Section */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                            <div className="flex items-center gap-2">
                                <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <h2 className="text-sm font-semibold text-gray-700">Settings</h2>
                            </div>
                        </div>
                        <div className="p-6">
                            {/* Status */}
                            <div>
                                <label htmlFor="status" className="block text-sm font-medium text-gray-700 mb-2">
                                    Status <span className="text-pink-500">*</span>
                                </label>
                                <div className="grid grid-cols-2 gap-3">
                                    <button
                                        type="button"
                                        onClick={() => setData('status', 'draft')}
                                        className={`px-4 py-3 rounded-xl text-sm font-medium transition-all ${
                                            data.status === 'draft'
                                                ? 'bg-gray-900 text-white'
                                                : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                                        }`}
                                    >
                                        <div className="flex items-center justify-center gap-2">
                                            <div className={`w-2 h-2 rounded-full ${data.status === 'draft' ? 'bg-gray-400' : 'bg-gray-300'}`}></div>
                                            Draft
                                        </div>
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setData('status', 'published')}
                                        className={`px-4 py-3 rounded-xl text-sm font-medium transition-all ${
                                            data.status === 'published'
                                                ? 'bg-green-500 text-white'
                                                : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                                        }`}
                                    >
                                        <div className="flex items-center justify-center gap-2">
                                            <div className={`w-2 h-2 rounded-full ${data.status === 'published' ? 'bg-green-200' : 'bg-gray-300'}`}></div>
                                            Published
                                        </div>
                                    </button>
                                </div>
                                {errors.status && <p className="mt-2 text-sm text-red-500">{errors.status}</p>}
                            </div>
                        </div>
                    </div>

                    {/* Actions */}
                    <div className="flex items-center justify-end gap-3 pt-4">
                        <Link
                            href="/admin/categories"
                            className="px-6 py-3 rounded-xl text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-all"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-8 py-3 rounded-xl text-sm font-medium text-white bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 shadow-sm hover:shadow transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {processing ? (
                                <span className="flex items-center gap-2">
                                    <svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Updating...
                                </span>
                            ) : (
                                'Update Category'
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
