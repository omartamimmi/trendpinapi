import { useState, useMemo } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useToast } from '@/Components/Toast';

export default function BankOfferForm({ offer, banks, cardTypes, brands = [], existingAssignments = [] }) {
    const toast = useToast();
    const isEdit = !!offer;

    const [form, setForm] = useState({
        bank_id: offer?.bank_id || '',
        card_type_id: offer?.card_type_id || '',
        title: offer?.title || '',
        title_ar: offer?.title_ar || '',
        description: offer?.description || '',
        description_ar: offer?.description_ar || '',
        offer_type: offer?.offer_type || 'percentage',
        offer_value: offer?.offer_value || '',
        min_purchase_amount: offer?.min_purchase_amount || '',
        max_discount_amount: offer?.max_discount_amount || '',
        start_date: offer?.start_date ? offer.start_date.split('T')[0] : '',
        end_date: offer?.end_date ? offer.end_date.split('T')[0] : '',
        terms: offer?.terms || '',
        terms_ar: offer?.terms_ar || '',
        redemption_type: offer?.redemption_type || 'show_only',
        max_claims: offer?.max_claims || '',
        status: offer?.status || 'draft',
    });

    // Brand assignments state
    const [brandAssignments, setBrandAssignments] = useState(() => {
        // Initialize from existing assignments or empty
        const assignments = {};
        existingAssignments.forEach(a => {
            assignments[a.brand_id] = {
                selected: true,
                all_branches: a.all_branches,
                branch_ids: a.branch_ids || [],
            };
        });
        return assignments;
    });

    const [brandSearch, setBrandSearch] = useState('');
    const [processing, setProcessing] = useState(false);

    // Filter brands by search
    const filteredBrands = useMemo(() => {
        if (!brandSearch) return brands;
        const search = brandSearch.toLowerCase();
        return brands.filter(brand =>
            brand.title?.toLowerCase().includes(search) ||
            brand.title_ar?.toLowerCase().includes(search) ||
            brand.name?.toLowerCase().includes(search)
        );
    }, [brands, brandSearch]);

    // Get selected brands count
    const selectedBrandsCount = useMemo(() => {
        return Object.values(brandAssignments).filter(a => a.selected).length;
    }, [brandAssignments]);

    // Toggle brand selection
    const toggleBrand = (brandId) => {
        setBrandAssignments(prev => {
            const current = prev[brandId];
            if (current?.selected) {
                // Remove selection
                const { [brandId]: _, ...rest } = prev;
                return rest;
            } else {
                // Add selection with all branches by default
                return {
                    ...prev,
                    [brandId]: { selected: true, all_branches: true, branch_ids: [] }
                };
            }
        });
    };

    // Toggle all branches for a brand
    const toggleAllBranches = (brandId, allBranches) => {
        setBrandAssignments(prev => ({
            ...prev,
            [brandId]: { ...prev[brandId], all_branches: allBranches, branch_ids: allBranches ? [] : prev[brandId]?.branch_ids || [] }
        }));
    };

    // Toggle specific branch for a brand
    const toggleBranch = (brandId, branchId) => {
        setBrandAssignments(prev => {
            const current = prev[brandId];
            const branchIds = current?.branch_ids || [];
            const newBranchIds = branchIds.includes(branchId)
                ? branchIds.filter(id => id !== branchId)
                : [...branchIds, branchId];
            return {
                ...prev,
                [brandId]: { ...current, branch_ids: newBranchIds, all_branches: false }
            };
        });
    };

    // Filter card types by selected bank
    const filteredCardTypes = useMemo(() => {
        if (!form.bank_id) return [];
        return cardTypes?.filter(ct => ct.bank_id === parseInt(form.bank_id)) || [];
    }, [form.bank_id, cardTypes]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm(prev => ({ ...prev, [name]: value }));

        // Reset card_type_id when bank changes
        if (name === 'bank_id') {
            setForm(prev => ({ ...prev, card_type_id: '' }));
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setProcessing(true);

        // Transform brand assignments into array format for backend
        const brand_assignments = Object.entries(brandAssignments)
            .filter(([_, data]) => data.selected)
            .map(([brandId, data]) => ({
                brand_id: parseInt(brandId),
                all_branches: data.all_branches,
                branch_ids: data.all_branches ? [] : data.branch_ids,
            }));

        const url = isEdit
            ? `/admin/bank-offer/offers/${offer.id}`
            : '/admin/bank-offer/offers';

        const method = isEdit ? 'put' : 'post';

        router[method](url, { ...form, brand_assignments }, {
            onSuccess: () => {
                toast.success(isEdit ? 'Bank offer updated successfully' : 'Bank offer created successfully');
            },
            onError: (errors) => {
                toast.error(Object.values(errors).flat().join(', '));
            },
            onFinish: () => setProcessing(false),
        });
    };

    const getOfferPreview = () => {
        if (!form.offer_value) return 'Set discount value';
        switch (form.offer_type) {
            case 'percentage': return `${form.offer_value}% Off`;
            case 'fixed': return `JOD ${form.offer_value} Off`;
            case 'cashback': return `${form.offer_value}% Cashback`;
            default: return form.offer_value;
        }
    };

    const statusConfig = {
        draft: { bg: 'bg-gray-100', text: 'text-gray-700', label: 'Draft' },
        pending: { bg: 'bg-yellow-100', text: 'text-yellow-700', label: 'Pending Approval' },
        active: { bg: 'bg-green-100', text: 'text-green-700', label: 'Active' },
        paused: { bg: 'bg-blue-100', text: 'text-blue-700', label: 'Paused' },
        expired: { bg: 'bg-red-100', text: 'text-red-700', label: 'Expired' },
    };

    const selectedBank = banks?.find(b => b.id === parseInt(form.bank_id));

    return (
        <AdminLayout>
            <div className="py-6">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <nav className="flex items-center gap-2 text-sm text-gray-500 mb-6">
                        <Link href="/admin/bank-offer/offers" className="hover:text-pink-600 transition-colors">
                            Bank Offers
                        </Link>
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span className="text-gray-900 font-medium">{isEdit ? 'Edit Offer' : 'Create Offer'}</span>
                    </nav>

                    {/* Header */}
                    <div className="flex items-center justify-between mb-8">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">
                                {isEdit ? 'Edit Bank Offer' : 'Create Bank Offer'}
                            </h1>
                            <p className="mt-1 text-sm text-gray-500">
                                {isEdit ? 'Update offer details and settings' : 'Create a new bank card offer for retailers'}
                            </p>
                        </div>
                        <Link
                            href="/admin/bank-offer/offers"
                            className="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back
                        </Link>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Preview Card */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Offer Preview</h2>
                                <p className="text-sm text-gray-500 mt-0.5">How the offer will appear</p>
                            </div>
                            <div className="p-6">
                                <div className="bg-gradient-to-br from-pink-50 to-purple-50 rounded-2xl p-6 border border-pink-100">
                                    <div className="flex items-start justify-between">
                                        <div className="flex items-center gap-4">
                                            {selectedBank?.logo ? (
                                                <img src={selectedBank.logo.url} alt="" className="w-14 h-14 rounded-xl object-contain bg-white shadow-sm" />
                                            ) : (
                                                <div className="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                                    <span className="text-white font-bold text-lg">{selectedBank?.name?.charAt(0) || 'B'}</span>
                                                </div>
                                            )}
                                            <div>
                                                <h3 className="font-semibold text-gray-900 text-lg">
                                                    {form.title || 'Offer Title'}
                                                </h3>
                                                <p className="text-sm text-gray-500">
                                                    {selectedBank?.name || 'Select a bank'} {form.card_type_id ? `- ${filteredCardTypes.find(c => c.id === parseInt(form.card_type_id))?.name}` : '- All Cards'}
                                                </p>
                                            </div>
                                        </div>
                                        <span className={`px-3 py-1.5 rounded-full text-sm font-semibold ${statusConfig[form.status]?.bg} ${statusConfig[form.status]?.text}`}>
                                            {statusConfig[form.status]?.label}
                                        </span>
                                    </div>
                                    <div className="mt-4 flex items-center gap-6">
                                        <div className="bg-white rounded-xl px-4 py-2 shadow-sm">
                                            <span className="text-2xl font-bold text-pink-600">{getOfferPreview()}</span>
                                        </div>
                                        {form.start_date && form.end_date && (
                                            <div className="text-sm text-gray-500">
                                                <span className="font-medium">Valid:</span> {form.start_date} to {form.end_date}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Bank & Card Selection */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Bank & Card Type</h2>
                                <p className="text-sm text-gray-500 mt-0.5">Select the bank and optional card type for this offer</p>
                            </div>
                            <div className="p-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label htmlFor="bank_id" className="block text-sm font-medium text-gray-700 mb-2">
                                            Bank <span className="text-red-500">*</span>
                                        </label>
                                        <select
                                            id="bank_id"
                                            name="bank_id"
                                            value={form.bank_id}
                                            onChange={handleChange}
                                            required
                                            className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all cursor-pointer"
                                        >
                                            <option value="">Select a bank</option>
                                            {banks?.map(bank => (
                                                <option key={bank.id} value={bank.id}>{bank.name}</option>
                                            ))}
                                        </select>
                                    </div>
                                    <div>
                                        <label htmlFor="card_type_id" className="block text-sm font-medium text-gray-700 mb-2">
                                            Card Type <span className="text-gray-400">(Optional)</span>
                                        </label>
                                        <select
                                            id="card_type_id"
                                            name="card_type_id"
                                            value={form.card_type_id}
                                            onChange={handleChange}
                                            disabled={!form.bank_id}
                                            className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all cursor-pointer disabled:opacity-50"
                                        >
                                            <option value="">All Cards</option>
                                            {filteredCardTypes.map(cardType => (
                                                <option key={cardType.id} value={cardType.id}>
                                                    {cardType.name} ({cardType.card_network})
                                                </option>
                                            ))}
                                        </select>
                                        <p className="mt-2 text-xs text-gray-400">Leave empty to apply to all cards from this bank</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Offer Details */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Offer Details</h2>
                                <p className="text-sm text-gray-500 mt-0.5">Title and description in both languages</p>
                            </div>
                            <div className="p-6 space-y-6">
                                {/* Titles */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label htmlFor="title" className="block text-sm font-medium text-gray-700 mb-2">
                                            Title (English) <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="title"
                                            name="title"
                                            value={form.title}
                                            onChange={handleChange}
                                            required
                                            placeholder="e.g., Summer Savings Offer"
                                            className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                        />
                                    </div>
                                    <div>
                                        <label htmlFor="title_ar" className="block text-sm font-medium text-gray-700 mb-2">
                                            Title (Arabic)
                                        </label>
                                        <input
                                            type="text"
                                            id="title_ar"
                                            name="title_ar"
                                            value={form.title_ar}
                                            onChange={handleChange}
                                            dir="rtl"
                                            placeholder="العنوان بالعربية"
                                            className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all text-right"
                                        />
                                    </div>
                                </div>

                                {/* Descriptions */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
                                            Description (English)
                                        </label>
                                        <textarea
                                            id="description"
                                            name="description"
                                            value={form.description}
                                            onChange={handleChange}
                                            rows={3}
                                            placeholder="Brief description of the offer..."
                                            className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all resize-none"
                                        />
                                    </div>
                                    <div>
                                        <label htmlFor="description_ar" className="block text-sm font-medium text-gray-700 mb-2">
                                            Description (Arabic)
                                        </label>
                                        <textarea
                                            id="description_ar"
                                            name="description_ar"
                                            value={form.description_ar}
                                            onChange={handleChange}
                                            rows={3}
                                            dir="rtl"
                                            placeholder="وصف العرض بالعربية..."
                                            className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all resize-none text-right"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Discount Configuration */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Discount Configuration</h2>
                                <p className="text-sm text-gray-500 mt-0.5">Set the discount type and value</p>
                            </div>
                            <div className="p-6 space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label htmlFor="offer_type" className="block text-sm font-medium text-gray-700 mb-2">
                                            Discount Type <span className="text-red-500">*</span>
                                        </label>
                                        <select
                                            id="offer_type"
                                            name="offer_type"
                                            value={form.offer_type}
                                            onChange={handleChange}
                                            className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all cursor-pointer"
                                        >
                                            <option value="percentage">Percentage Off</option>
                                            <option value="fixed">Fixed Amount Off</option>
                                            <option value="cashback">Cashback</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label htmlFor="offer_value" className="block text-sm font-medium text-gray-700 mb-2">
                                            Discount Value <span className="text-red-500">*</span>
                                        </label>
                                        <div className="relative">
                                            <input
                                                type="number"
                                                id="offer_value"
                                                name="offer_value"
                                                value={form.offer_value}
                                                onChange={handleChange}
                                                required
                                                min="0"
                                                step="0.01"
                                                placeholder={form.offer_type === 'fixed' ? '10.00' : '15'}
                                                className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                            />
                                            <div className="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                                <span className="text-gray-400 text-sm">
                                                    {form.offer_type === 'fixed' ? 'JOD' : '%'}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label htmlFor="redemption_type" className="block text-sm font-medium text-gray-700 mb-2">
                                            Redemption Type
                                        </label>
                                        <select
                                            id="redemption_type"
                                            name="redemption_type"
                                            value={form.redemption_type}
                                            onChange={handleChange}
                                            className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all cursor-pointer"
                                        >
                                            <option value="show_only">Show Only</option>
                                            <option value="qr_code">QR Code</option>
                                            <option value="in_app">In-App</option>
                                        </select>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label htmlFor="min_purchase_amount" className="block text-sm font-medium text-gray-700 mb-2">
                                            Minimum Purchase
                                        </label>
                                        <div className="relative">
                                            <input
                                                type="number"
                                                id="min_purchase_amount"
                                                name="min_purchase_amount"
                                                value={form.min_purchase_amount}
                                                onChange={handleChange}
                                                min="0"
                                                step="0.01"
                                                placeholder="0.00"
                                                className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                            />
                                            <div className="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                                <span className="text-gray-400 text-sm">JOD</span>
                                            </div>
                                        </div>
                                        <p className="mt-2 text-xs text-gray-400">Leave empty for no minimum</p>
                                    </div>
                                    <div>
                                        <label htmlFor="max_discount_amount" className="block text-sm font-medium text-gray-700 mb-2">
                                            Maximum Discount
                                        </label>
                                        <div className="relative">
                                            <input
                                                type="number"
                                                id="max_discount_amount"
                                                name="max_discount_amount"
                                                value={form.max_discount_amount}
                                                onChange={handleChange}
                                                min="0"
                                                step="0.01"
                                                placeholder="0.00"
                                                className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                            />
                                            <div className="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                                <span className="text-gray-400 text-sm">JOD</span>
                                            </div>
                                        </div>
                                        <p className="mt-2 text-xs text-gray-400">Cap for percentage discounts</p>
                                    </div>
                                    <div>
                                        <label htmlFor="max_claims" className="block text-sm font-medium text-gray-700 mb-2">
                                            Maximum Claims
                                        </label>
                                        <input
                                            type="number"
                                            id="max_claims"
                                            name="max_claims"
                                            value={form.max_claims}
                                            onChange={handleChange}
                                            min="0"
                                            placeholder="Unlimited"
                                            className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                        />
                                        <p className="mt-2 text-xs text-gray-400">Leave empty for unlimited</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Validity Period */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Validity Period</h2>
                                <p className="text-sm text-gray-500 mt-0.5">When the offer is active</p>
                            </div>
                            <div className="p-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label htmlFor="start_date" className="block text-sm font-medium text-gray-700 mb-2">
                                            Start Date <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="date"
                                            id="start_date"
                                            name="start_date"
                                            value={form.start_date}
                                            onChange={handleChange}
                                            required
                                            className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                        />
                                    </div>
                                    <div>
                                        <label htmlFor="end_date" className="block text-sm font-medium text-gray-700 mb-2">
                                            End Date <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="date"
                                            id="end_date"
                                            name="end_date"
                                            value={form.end_date}
                                            onChange={handleChange}
                                            required
                                            min={form.start_date}
                                            className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Terms & Conditions */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Terms & Conditions</h2>
                                <p className="text-sm text-gray-500 mt-0.5">Additional terms for the offer</p>
                            </div>
                            <div className="p-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label htmlFor="terms" className="block text-sm font-medium text-gray-700 mb-2">
                                            Terms (English)
                                        </label>
                                        <textarea
                                            id="terms"
                                            name="terms"
                                            value={form.terms}
                                            onChange={handleChange}
                                            rows={4}
                                            placeholder="Enter terms and conditions..."
                                            className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all resize-none"
                                        />
                                    </div>
                                    <div>
                                        <label htmlFor="terms_ar" className="block text-sm font-medium text-gray-700 mb-2">
                                            Terms (Arabic)
                                        </label>
                                        <textarea
                                            id="terms_ar"
                                            name="terms_ar"
                                            value={form.terms_ar}
                                            onChange={handleChange}
                                            rows={4}
                                            dir="rtl"
                                            placeholder="الشروط والأحكام بالعربية..."
                                            className="block w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all resize-none text-right"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Participating Brands & Branches */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <h2 className="font-semibold text-gray-900">Participating Brands & Branches</h2>
                                        <p className="text-sm text-gray-500 mt-0.5">Select which brands can use this offer</p>
                                    </div>
                                    <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-pink-100 text-pink-700">
                                        {selectedBrandsCount} selected
                                    </span>
                                </div>
                            </div>
                            <div className="p-6">
                                {/* Search */}
                                <div className="relative mb-4">
                                    <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <input
                                        type="text"
                                        placeholder="Search brands..."
                                        value={brandSearch}
                                        onChange={(e) => setBrandSearch(e.target.value)}
                                        className="w-full pl-12 pr-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                    />
                                </div>

                                {/* Brands List */}
                                <div className="max-h-96 overflow-y-auto space-y-3">
                                    {filteredBrands.length === 0 ? (
                                        <p className="text-center text-gray-500 py-8">No brands found</p>
                                    ) : (
                                        filteredBrands.map(brand => {
                                            const isSelected = brandAssignments[brand.id]?.selected;
                                            const assignment = brandAssignments[brand.id];

                                            return (
                                                <div key={brand.id} className={`rounded-xl border-2 transition-all ${isSelected ? 'border-pink-300 bg-pink-50/50' : 'border-gray-100 hover:border-gray-200'}`}>
                                                    {/* Brand Header */}
                                                    <div
                                                        className="flex items-center justify-between p-4 cursor-pointer"
                                                        onClick={() => toggleBrand(brand.id)}
                                                    >
                                                        <div className="flex items-center gap-3">
                                                            <div className={`w-5 h-5 rounded border-2 flex items-center justify-center transition-all ${isSelected ? 'bg-pink-500 border-pink-500' : 'border-gray-300'}`}>
                                                                {isSelected && (
                                                                    <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                )}
                                                            </div>
                                                            <div>
                                                                <p className="font-medium text-gray-900">{brand.title || brand.name}</p>
                                                                {brand.title_ar && <p className="text-sm text-gray-500" dir="rtl">{brand.title_ar}</p>}
                                                            </div>
                                                        </div>
                                                        <span className="text-xs text-gray-400">
                                                            {brand.branches?.length || 0} branches
                                                        </span>
                                                    </div>

                                                    {/* Branch Selection (shown when brand is selected) */}
                                                    {isSelected && brand.branches?.length > 0 && (
                                                        <div className="px-4 pb-4 border-t border-pink-200">
                                                            <div className="pt-3 space-y-2">
                                                                {/* All Branches Toggle */}
                                                                <label className="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-pink-100/50">
                                                                    <input
                                                                        type="radio"
                                                                        name={`branch_mode_${brand.id}`}
                                                                        checked={assignment?.all_branches}
                                                                        onChange={() => toggleAllBranches(brand.id, true)}
                                                                        className="w-4 h-4 text-pink-500 border-gray-300 focus:ring-pink-500"
                                                                    />
                                                                    <span className="text-sm font-medium text-gray-700">All Branches</span>
                                                                </label>

                                                                <label className="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-pink-100/50">
                                                                    <input
                                                                        type="radio"
                                                                        name={`branch_mode_${brand.id}`}
                                                                        checked={!assignment?.all_branches}
                                                                        onChange={() => toggleAllBranches(brand.id, false)}
                                                                        className="w-4 h-4 text-pink-500 border-gray-300 focus:ring-pink-500"
                                                                    />
                                                                    <span className="text-sm font-medium text-gray-700">Specific Branches</span>
                                                                </label>

                                                                {/* Individual Branches */}
                                                                {!assignment?.all_branches && (
                                                                    <div className="ml-6 mt-2 space-y-1">
                                                                        {brand.branches.map(branch => (
                                                                            <label
                                                                                key={branch.id}
                                                                                className="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-pink-100/50"
                                                                            >
                                                                                <input
                                                                                    type="checkbox"
                                                                                    checked={assignment?.branch_ids?.includes(branch.id)}
                                                                                    onChange={() => toggleBranch(brand.id, branch.id)}
                                                                                    className="w-4 h-4 rounded text-pink-500 border-gray-300 focus:ring-pink-500"
                                                                                />
                                                                                <span className="text-sm text-gray-600">
                                                                                    {branch.name}
                                                                                    {branch.is_main && <span className="ml-1 text-xs text-pink-500">(Main)</span>}
                                                                                </span>
                                                                                {branch.location && (
                                                                                    <span className="text-xs text-gray-400 ml-auto">{branch.location}</span>
                                                                                )}
                                                                            </label>
                                                                        ))}
                                                                        {assignment?.branch_ids?.length === 0 && (
                                                                            <p className="text-xs text-red-500 ml-2">Please select at least one branch</p>
                                                                        )}
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>
                                            );
                                        })
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Status */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Status</h2>
                                <p className="text-sm text-gray-500 mt-0.5">Control offer visibility and activation</p>
                            </div>
                            <div className="p-6">
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    {['draft', 'pending', 'active', 'paused'].map((status) => (
                                        <button
                                            key={status}
                                            type="button"
                                            onClick={() => setForm(prev => ({ ...prev, status }))}
                                            className={`p-4 rounded-xl border-2 transition-all ${
                                                form.status === status
                                                    ? 'border-pink-500 bg-pink-50'
                                                    : 'border-gray-200 hover:border-gray-300'
                                            }`}
                                        >
                                            <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${statusConfig[status]?.bg} ${statusConfig[status]?.text}`}>
                                                {statusConfig[status]?.label}
                                            </span>
                                            <p className="mt-2 text-xs text-gray-500">
                                                {status === 'draft' && 'Save as draft to edit later'}
                                                {status === 'pending' && 'Submit for approval'}
                                                {status === 'active' && 'Make immediately active'}
                                                {status === 'paused' && 'Temporarily pause offer'}
                                            </p>
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Actions */}
                        <div className="flex items-center justify-end gap-3 pt-4">
                            <Link
                                href="/admin/bank-offer/offers"
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
                                        {isEdit ? 'Update Offer' : 'Create Offer'}
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
