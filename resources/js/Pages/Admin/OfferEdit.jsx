import { useState, useEffect } from 'react';
import { router, useForm, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function OfferEdit({ offer, retailers, brands: initialBrands }) {
    const [selectedRetailer, setSelectedRetailer] = useState(offer.user_id?.toString() || '');
    const [brands, setBrands] = useState(initialBrands || []);
    const [selectedBrand, setSelectedBrand] = useState(null);
    const [loadingBrands, setLoadingBrands] = useState(false);

    const form = useForm({
        user_id: offer.user_id || '',
        name: offer.name || '',
        description: offer.description || '',
        brand_id: offer.brand_id || '',
        discount_type: offer.discount_type || 'percentage',
        discount_value: offer.discount_value || '',
        start_date: offer.start_date ? offer.start_date.split('T')[0] : '',
        end_date: offer.end_date ? offer.end_date.split('T')[0] : '',
        max_claims: offer.max_claims || '',
        terms: offer.terms || '',
        branch_ids: offer.branch_ids || [],
        all_branches: offer.all_branches || false,
        status: offer.status || 'active',
    });

    // Set initial selected brand
    useEffect(() => {
        if (offer.brand_id && initialBrands) {
            const brand = initialBrands.find(b => b.id === parseInt(offer.brand_id));
            setSelectedBrand(brand);
        }
    }, []);

    // Fetch brands when retailer changes
    const handleRetailerChange = (newRetailerId) => {
        setSelectedRetailer(newRetailerId);
        if (newRetailerId && newRetailerId !== offer.user_id?.toString()) {
            setLoadingBrands(true);
            fetch(`/admin/offers/brands/${newRetailerId}`)
                .then(res => res.json())
                .then(data => {
                    setBrands(data);
                    setLoadingBrands(false);
                })
                .catch(() => {
                    setBrands([]);
                    setLoadingBrands(false);
                });
            form.setData(data => ({
                ...data,
                user_id: newRetailerId,
                brand_id: '',
                branch_ids: [],
                all_branches: false,
            }));
            setSelectedBrand(null);
        } else if (newRetailerId) {
            form.setData('user_id', newRetailerId);
        }
    };

    // Update selected brand when brand_id changes
    useEffect(() => {
        if (form.data.brand_id) {
            const brand = brands.find(b => b.id === parseInt(form.data.brand_id));
            setSelectedBrand(brand);
        } else {
            setSelectedBrand(null);
        }
    }, [form.data.brand_id, brands]);

    const handleBranchToggle = (branchId) => {
        const currentIds = form.data.branch_ids || [];
        if (currentIds.includes(branchId)) {
            form.setData('branch_ids', currentIds.filter(id => id !== branchId));
        } else {
            form.setData('branch_ids', [...currentIds, branchId]);
        }
        if (form.data.all_branches) {
            form.setData('all_branches', false);
        }
    };

    const handleAllBranchesToggle = () => {
        if (form.data.all_branches) {
            form.setData({
                ...form.data,
                all_branches: false,
                branch_ids: [],
            });
        } else {
            form.setData({
                ...form.data,
                all_branches: true,
                branch_ids: selectedBrand?.branches?.map(b => b.id) || [],
            });
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        form.put(`/admin/offers/${offer.id}`);
    };

    return (
        <AdminLayout>
            <div>
                {/* Header */}
                <div className="flex items-center space-x-4 mb-6">
                    <Link
                        href="/admin/offers"
                        className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Edit Offer</h1>
                        <p className="text-sm text-gray-500 mt-1">Update offer details</p>
                    </div>
                </div>

                <form onSubmit={handleSubmit}>
                    <div className="space-y-6">
                        {/* Retailer Selection */}
                        <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Retailer</h2>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Retailer *</label>
                                <select
                                    value={selectedRetailer}
                                    onChange={(e) => handleRetailerChange(e.target.value)}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                    required
                                >
                                    <option value="">Select Retailer</option>
                                    {retailers?.map((retailer) => (
                                        <option key={retailer.id} value={retailer.id}>
                                            {retailer.name} ({retailer.email})
                                        </option>
                                    ))}
                                </select>
                                {form.errors.user_id && (
                                    <p className="text-red-500 text-sm mt-1">{form.errors.user_id}</p>
                                )}
                            </div>
                        </div>

                        {/* Basic Info */}
                        <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Offer Details</h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="md:col-span-2">
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Offer Name *</label>
                                    <input
                                        type="text"
                                        value={form.data.name}
                                        onChange={(e) => form.setData('name', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                        required
                                    />
                                    {form.errors.name && (
                                        <p className="text-red-500 text-sm mt-1">{form.errors.name}</p>
                                    )}
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
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                    <select
                                        value={form.data.brand_id}
                                        onChange={(e) => form.setData('brand_id', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                        disabled={loadingBrands}
                                    >
                                        <option value="">Select Brand (Optional)</option>
                                        {brands?.map((brand) => (
                                            <option key={brand.id} value={brand.id}>
                                                {brand.title || brand.name}
                                            </option>
                                        ))}
                                    </select>
                                    {loadingBrands && (
                                        <p className="text-gray-500 text-sm mt-1">Loading brands...</p>
                                    )}
                                    {form.errors.brand_id && (
                                        <p className="text-red-500 text-sm mt-1">{form.errors.brand_id}</p>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select
                                        value={form.data.status}
                                        onChange={(e) => form.setData('status', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                    >
                                        <option value="draft">Draft</option>
                                        <option value="active">Active</option>
                                        <option value="paused">Paused</option>
                                        <option value="expired">Expired</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {/* Branch Selection */}
                        {selectedBrand && selectedBrand.branches && selectedBrand.branches.length > 0 && (
                            <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                                <h2 className="text-lg font-semibold text-gray-900 mb-4">Select Branches</h2>

                                <div className="mb-4">
                                    <label className="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                                        <input
                                            type="checkbox"
                                            checked={form.data.all_branches}
                                            onChange={handleAllBranchesToggle}
                                            className="w-5 h-5 text-pink-600 border-gray-300 rounded focus:ring-pink-500"
                                        />
                                        <div>
                                            <span className="font-medium text-gray-900">All Branches</span>
                                            <p className="text-sm text-gray-500">
                                                Apply to all {selectedBrand.branches.length} branches
                                            </p>
                                        </div>
                                    </label>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    {selectedBrand.branches.map((branch) => (
                                        <label
                                            key={branch.id}
                                            className={`flex items-center space-x-3 p-3 border rounded-lg cursor-pointer transition-colors ${
                                                form.data.branch_ids?.includes(branch.id) || form.data.all_branches
                                                    ? 'border-pink-500 bg-pink-50'
                                                    : 'border-gray-200 hover:border-gray-300'
                                            }`}
                                        >
                                            <input
                                                type="checkbox"
                                                checked={form.data.branch_ids?.includes(branch.id) || form.data.all_branches}
                                                onChange={() => handleBranchToggle(branch.id)}
                                                disabled={form.data.all_branches}
                                                className="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500"
                                            />
                                            <span className="text-sm text-gray-700">{branch.name}</span>
                                        </label>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Discount Settings */}
                        <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Discount Settings</h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Discount Type</label>
                                    <select
                                        value={form.data.discount_type}
                                        onChange={(e) => form.setData('discount_type', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                    >
                                        <option value="percentage">Percentage (%)</option>
                                        <option value="fixed">Fixed Amount (JOD)</option>
                                        <option value="bogo">Buy One Get One</option>
                                    </select>
                                </div>
                                {form.data.discount_type !== 'bogo' && (
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            {form.data.discount_type === 'percentage' ? 'Discount (%)' : 'Discount Amount (JOD)'}
                                        </label>
                                        <input
                                            type="number"
                                            value={form.data.discount_value}
                                            onChange={(e) => form.setData('discount_value', e.target.value)}
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                            min="0"
                                        />
                                        {form.errors.discount_value && (
                                            <p className="text-red-500 text-sm mt-1">{form.errors.discount_value}</p>
                                        )}
                                    </div>
                                )}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                    <input
                                        type="date"
                                        value={form.data.start_date}
                                        onChange={(e) => form.setData('start_date', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                    <input
                                        type="date"
                                        value={form.data.end_date}
                                        onChange={(e) => form.setData('end_date', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                    />
                                    {form.errors.end_date && (
                                        <p className="text-red-500 text-sm mt-1">{form.errors.end_date}</p>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Maximum Claims</label>
                                    <input
                                        type="number"
                                        value={form.data.max_claims}
                                        onChange={(e) => form.setData('max_claims', e.target.value)}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                                        placeholder="Leave empty for unlimited"
                                        min="1"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Terms */}
                        <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Terms & Conditions</h2>
                            <textarea
                                value={form.data.terms}
                                onChange={(e) => form.setData('terms', e.target.value)}
                                rows={4}
                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"
                            />
                        </div>

                        {/* Stats */}
                        <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <h2 className="text-lg font-semibold text-gray-900 mb-4">Statistics</h2>
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <p className="text-sm text-gray-500">Total Claims</p>
                                    <p className="text-xl font-bold text-gray-900">{offer.claims_count || 0}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-500">Total Views</p>
                                    <p className="text-xl font-bold text-gray-900">{offer.views_count || 0}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-500">Created</p>
                                    <p className="text-sm font-medium text-gray-700">
                                        {offer.created_at ? new Date(offer.created_at).toLocaleDateString() : '-'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-500">Last Updated</p>
                                    <p className="text-sm font-medium text-gray-700">
                                        {offer.updated_at ? new Date(offer.updated_at).toLocaleDateString() : '-'}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Submit */}
                        <div className="flex justify-end space-x-3">
                            <Link
                                href="/admin/offers"
                                className="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={form.processing}
                                className="px-6 py-2 rounded-lg text-white font-medium disabled:opacity-50 bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 transition-all"
                            >
                                {form.processing ? 'Saving...' : 'Save Changes'}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
