import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useToast } from '@/Components/Toast';
import MediaUploader from '@/Components/Media/MediaUploader';

export default function BankForm({ bank }) {
    const toast = useToast();
    const isEdit = !!bank;

    const [form, setForm] = useState({
        name: bank?.name || '',
        name_ar: bank?.name_ar || '',
        description: bank?.description || '',
        logo_id: bank?.logo_id || null,
        status: bank?.status || 'active',
    });

    const [logo, setLogo] = useState(bank?.logo || null);
    const [processing, setProcessing] = useState(false);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm(prev => ({ ...prev, [name]: value }));
    };

    const handleLogoChange = (media) => {
        setLogo(media);
        setForm(prev => ({ ...prev, logo_id: media?.id || null }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setProcessing(true);

        const url = isEdit
            ? `/admin/bank-offer/banks/${bank.id}`
            : '/admin/bank-offer/banks';

        const method = isEdit ? 'put' : 'post';

        router[method](url, form, {
            onSuccess: () => {
                toast.success(isEdit ? 'Bank updated successfully' : 'Bank created successfully');
            },
            onError: (errors) => {
                toast.error(Object.values(errors).flat().join(', '));
            },
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <AdminLayout>
            <div className="py-6">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <nav className="flex items-center gap-2 text-sm text-gray-500 mb-6">
                        <Link href="/admin/bank-offer/banks" className="hover:text-pink-600 transition-colors">
                            Banks
                        </Link>
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span className="text-gray-900 font-medium">{isEdit ? 'Edit Bank' : 'Add Bank'}</span>
                    </nav>

                    {/* Header */}
                    <div className="flex items-center justify-between mb-8">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">
                                {isEdit ? 'Edit Bank' : 'Add New Bank'}
                            </h1>
                            <p className="mt-1 text-sm text-gray-500">
                                {isEdit ? 'Update bank information and settings' : 'Create a new bank for card offers'}
                            </p>
                        </div>
                        <Link
                            href="/admin/bank-offer/banks"
                            className="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back
                        </Link>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Logo & Preview Card */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Bank Logo</h2>
                                <p className="text-sm text-gray-500 mt-0.5">Upload your bank's logo for brand recognition</p>
                            </div>
                            <div className="p-6">
                                <div className="flex flex-col md:flex-row gap-8">
                                    {/* Logo Uploader */}
                                    <div className="flex-1">
                                        <MediaUploader
                                            value={logo}
                                            onChange={handleLogoChange}
                                            accept="image/*"
                                        />
                                        <p className="mt-3 text-xs text-gray-400">
                                            Recommended: Square image, at least 200x200px
                                        </p>
                                    </div>

                                    {/* Preview */}
                                    <div className="flex-1">
                                        <p className="text-sm font-medium text-gray-700 mb-3">Preview</p>
                                        <div className="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-6 border border-gray-200">
                                            <div className="flex items-center gap-4">
                                                {logo ? (
                                                    <img
                                                        src={logo.url}
                                                        alt="Bank Logo"
                                                        className="w-16 h-16 rounded-xl object-contain bg-white shadow-sm"
                                                    />
                                                ) : (
                                                    <div className="w-16 h-16 rounded-xl bg-gray-200 flex items-center justify-center">
                                                        <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                        </svg>
                                                    </div>
                                                )}
                                                <div>
                                                    <h3 className="font-semibold text-gray-900">
                                                        {form.name || 'Bank Name'}
                                                    </h3>
                                                    {form.name_ar && (
                                                        <p className="text-sm text-gray-500" dir="rtl">{form.name_ar}</p>
                                                    )}
                                                    <span className={`inline-flex items-center mt-2 px-2 py-0.5 rounded-full text-xs font-medium ${
                                                        form.status === 'active'
                                                            ? 'bg-green-100 text-green-700'
                                                            : 'bg-gray-100 text-gray-600'
                                                    }`}>
                                                        {form.status === 'active' ? 'Active' : 'Inactive'}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Basic Information */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Basic Information</h2>
                                <p className="text-sm text-gray-500 mt-0.5">Bank name in both languages</p>
                            </div>
                            <div className="p-6 space-y-6">
                                {/* Names */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
                                            Name (English) <span className="text-red-500">*</span>
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            </div>
                                            <input
                                                type="text"
                                                id="name"
                                                name="name"
                                                value={form.name}
                                                onChange={handleChange}
                                                required
                                                placeholder="e.g., Arab Bank"
                                                className="block w-full pl-12 pr-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label htmlFor="name_ar" className="block text-sm font-medium text-gray-700 mb-2">
                                            Name (Arabic)
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                                <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                                </svg>
                                            </div>
                                            <input
                                                type="text"
                                                id="name_ar"
                                                name="name_ar"
                                                value={form.name_ar}
                                                onChange={handleChange}
                                                dir="rtl"
                                                placeholder="الاسم بالعربية"
                                                className="block w-full pr-12 pl-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all text-right"
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Description */}
                                <div>
                                    <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
                                        Description
                                    </label>
                                    <textarea
                                        id="description"
                                        name="description"
                                        value={form.description}
                                        onChange={handleChange}
                                        rows={4}
                                        placeholder="Brief description about the bank..."
                                        className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all resize-none"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Status */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Status</h2>
                                <p className="text-sm text-gray-500 mt-0.5">Control bank visibility</p>
                            </div>
                            <div className="p-6">
                                <div className="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                    <div className="flex items-center gap-4">
                                        <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${
                                            form.status === 'active' ? 'bg-green-100' : 'bg-gray-200'
                                        }`}>
                                            {form.status === 'active' ? (
                                                <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            ) : (
                                                <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            )}
                                        </div>
                                        <div>
                                            <p className="font-medium text-gray-900">
                                                {form.status === 'active' ? 'Bank is Active' : 'Bank is Inactive'}
                                            </p>
                                            <p className="text-sm text-gray-500">
                                                {form.status === 'active'
                                                    ? 'This bank is visible and can have active offers'
                                                    : 'This bank is hidden from the system'}
                                            </p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() => setForm(prev => ({
                                            ...prev,
                                            status: prev.status === 'active' ? 'inactive' : 'active'
                                        }))}
                                        className={`relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 ${
                                            form.status === 'active' ? 'bg-green-500' : 'bg-gray-300'
                                        }`}
                                    >
                                        <span
                                            className={`pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${
                                                form.status === 'active' ? 'translate-x-5' : 'translate-x-0'
                                            }`}
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>

                        {/* Actions */}
                        <div className="flex items-center justify-end gap-3 pt-4">
                            <Link
                                href="/admin/bank-offer/banks"
                                className="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-pink-600 to-pink-500 rounded-xl hover:from-pink-700 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-pink-500/25"
                            >
                                {processing ? (
                                    <>
                                        <svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                        </svg>
                                        Saving...
                                    </>
                                ) : (
                                    <>
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {isEdit ? 'Update Bank' : 'Create Bank'}
                                    </>
                                )}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
