import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useToast } from '@/Components/Toast';
import MediaUploader from '@/Components/Media/MediaUploader';

const cardNetworks = [
    {
        value: 'visa',
        label: 'Visa',
        color: 'from-blue-600 to-blue-700',
        bgColor: 'bg-blue-50',
        borderColor: 'border-blue-500',
        textColor: 'text-blue-600',
    },
    {
        value: 'mastercard',
        label: 'Mastercard',
        color: 'from-orange-500 to-red-500',
        bgColor: 'bg-orange-50',
        borderColor: 'border-orange-500',
        textColor: 'text-orange-600',
    },
    {
        value: 'amex',
        label: 'American Express',
        color: 'from-indigo-600 to-indigo-700',
        bgColor: 'bg-indigo-50',
        borderColor: 'border-indigo-500',
        textColor: 'text-indigo-600',
    },
    {
        value: 'other',
        label: 'Other',
        color: 'from-gray-500 to-gray-600',
        bgColor: 'bg-gray-50',
        borderColor: 'border-gray-500',
        textColor: 'text-gray-600',
    },
];

const cardColorPresets = [
    { name: 'Blue', value: 'from-blue-600 to-blue-700' },
    { name: 'Navy', value: 'from-blue-800 to-blue-900' },
    { name: 'Indigo', value: 'from-indigo-600 to-indigo-700' },
    { name: 'Purple', value: 'from-purple-600 to-purple-700' },
    { name: 'Pink', value: 'from-pink-500 to-pink-600' },
    { name: 'Red', value: 'from-red-500 to-red-600' },
    { name: 'Orange', value: 'from-orange-500 to-red-500' },
    { name: 'Amber', value: 'from-amber-500 to-orange-500' },
    { name: 'Green', value: 'from-green-500 to-green-600' },
    { name: 'Teal', value: 'from-teal-500 to-teal-600' },
    { name: 'Cyan', value: 'from-cyan-500 to-cyan-600' },
    { name: 'Gray', value: 'from-gray-600 to-gray-700' },
    { name: 'Slate', value: 'from-slate-700 to-slate-800' },
    { name: 'Black', value: 'from-gray-800 to-gray-900' },
    { name: 'Gold', value: 'from-yellow-500 to-amber-600' },
    { name: 'Rose Gold', value: 'from-rose-400 to-pink-500' },
];

export default function CardTypeForm({ cardType, banks }) {
    const toast = useToast();
    const isEdit = !!cardType;

    const [form, setForm] = useState({
        name: cardType?.name || '',
        name_ar: cardType?.name_ar || '',
        bank_id: cardType?.bank_id || '',
        card_network: cardType?.card_network || 'visa',
        logo_id: cardType?.logo_id || null,
        bin_prefixes: cardType?.bin_prefixes || [],
        card_color: cardType?.card_color || '',
        status: cardType?.status || 'active',
    });

    const [logo, setLogo] = useState(cardType?.logo || null);
    const [processing, setProcessing] = useState(false);
    const [newBin, setNewBin] = useState('');

    const addBinPrefix = () => {
        const bin = newBin.replace(/\D/g, '').slice(0, 8);
        if (bin.length >= 4 && !form.bin_prefixes.includes(bin)) {
            setForm(prev => ({
                ...prev,
                bin_prefixes: [...prev.bin_prefixes, bin]
            }));
            setNewBin('');
        }
    };

    const removeBinPrefix = (binToRemove) => {
        setForm(prev => ({
            ...prev,
            bin_prefixes: prev.bin_prefixes.filter(bin => bin !== binToRemove)
        }));
    };

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
            ? `/admin/bank-offer/card-types/${cardType.id}`
            : '/admin/bank-offer/card-types';

        const method = isEdit ? 'put' : 'post';

        router[method](url, form, {
            onSuccess: () => {
                toast.success(isEdit ? 'Card type updated successfully' : 'Card type created successfully');
            },
            onError: (errors) => {
                toast.error(Object.values(errors).flat().join(', '));
            },
            onFinish: () => setProcessing(false),
        });
    };

    const selectedNetwork = cardNetworks.find(n => n.value === form.card_network) || cardNetworks[0];
    const cardColor = form.card_color || selectedNetwork.color;
    const selectedBank = banks.find(b => b.id == form.bank_id);

    return (
        <AdminLayout>
            <div className="py-6">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <nav className="flex items-center gap-2 text-sm text-gray-500 mb-6">
                        <Link href="/admin/bank-offer/card-types" className="hover:text-pink-600 transition-colors">
                            Card Types
                        </Link>
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span className="text-gray-900 font-medium">{isEdit ? 'Edit Card Type' : 'Add Card Type'}</span>
                    </nav>

                    {/* Header */}
                    <div className="flex items-center justify-between mb-8">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">
                                {isEdit ? 'Edit Card Type' : 'Add New Card Type'}
                            </h1>
                            <p className="mt-1 text-sm text-gray-500">
                                {isEdit ? 'Update card type information' : 'Create a new card type for bank offers'}
                            </p>
                        </div>
                        <Link
                            href="/admin/bank-offer/card-types"
                            className="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back
                        </Link>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Card Preview */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Card Preview</h2>
                                <p className="text-sm text-gray-500 mt-0.5">Preview how your card will appear</p>
                            </div>
                            <div className="p-6">
                                <div className="flex flex-col md:flex-row gap-8">
                                    {/* Logo Uploader */}
                                    <div className="flex-1">
                                        <label className="block text-sm font-medium text-gray-700 mb-3">Card Logo</label>
                                        <MediaUploader
                                            value={logo}
                                            onChange={handleLogoChange}
                                            accept="image/*"
                                        />
                                        <p className="mt-3 text-xs text-gray-400">
                                            Upload a custom logo or use the network default
                                        </p>
                                    </div>

                                    {/* Card Preview */}
                                    <div className="flex-1">
                                        <p className="text-sm font-medium text-gray-700 mb-3">Preview</p>
                                        <div className={`relative w-full max-w-sm aspect-[1.6/1] rounded-2xl bg-gradient-to-br ${cardColor} p-6 text-white shadow-xl overflow-hidden`}>
                                            {/* Background Pattern */}
                                            <div className="absolute inset-0 opacity-10">
                                                <svg className="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                                                    <circle cx="80" cy="20" r="40" fill="white" />
                                                    <circle cx="90" cy="80" r="30" fill="white" />
                                                </svg>
                                            </div>

                                            {/* Card Content */}
                                            <div className="relative h-full flex flex-col justify-between">
                                                <div className="flex items-start justify-between">
                                                    {logo ? (
                                                        <img src={logo.url} alt="" className="h-10 w-auto object-contain bg-white/20 rounded-lg p-1" />
                                                    ) : (
                                                        <div className="h-10 w-16 bg-white/20 rounded-lg flex items-center justify-center">
                                                            <span className="text-xs font-bold">{selectedNetwork.label.slice(0, 4).toUpperCase()}</span>
                                                        </div>
                                                    )}
                                                    {selectedBank && (
                                                        <span className="text-xs font-medium bg-white/20 px-2 py-1 rounded-full">
                                                            {selectedBank.name}
                                                        </span>
                                                    )}
                                                </div>

                                                <div>
                                                    <div className="text-lg font-semibold tracking-wider mb-1">
                                                        •••• •••• •••• ••••
                                                    </div>
                                                    <div className="flex items-end justify-between">
                                                        <div>
                                                            <p className="text-xs text-white/70 mb-0.5">CARD TYPE</p>
                                                            <p className="font-medium">{form.name || 'Card Name'}</p>
                                                        </div>
                                                        <div className="text-right">
                                                            <span className="text-xl font-bold tracking-tight">
                                                                {selectedNetwork.label.toUpperCase()}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Card Network Selection */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Card Network</h2>
                                <p className="text-sm text-gray-500 mt-0.5">Select the payment network for this card</p>
                            </div>
                            <div className="p-6">
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    {cardNetworks.map((network) => (
                                        <button
                                            key={network.value}
                                            type="button"
                                            onClick={() => setForm(prev => ({ ...prev, card_network: network.value }))}
                                            className={`relative p-4 rounded-xl border-2 transition-all ${
                                                form.card_network === network.value
                                                    ? `${network.borderColor} ${network.bgColor}`
                                                    : 'border-gray-200 hover:border-gray-300 bg-white'
                                            }`}
                                        >
                                            {form.card_network === network.value && (
                                                <div className="absolute top-2 right-2">
                                                    <svg className={`w-5 h-5 ${network.textColor}`} fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                                    </svg>
                                                </div>
                                            )}
                                            <div className={`w-12 h-8 rounded-lg bg-gradient-to-br ${network.color} flex items-center justify-center text-white font-bold text-xs mb-3`}>
                                                {network.label.slice(0, 4).toUpperCase()}
                                            </div>
                                            <p className={`font-medium text-sm ${
                                                form.card_network === network.value ? network.textColor : 'text-gray-700'
                                            }`}>
                                                {network.label}
                                            </p>
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Card Color */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Card Color</h2>
                                <p className="text-sm text-gray-500 mt-0.5">Choose a color theme for the card display</p>
                            </div>
                            <div className="p-6">
                                <div className="grid grid-cols-4 sm:grid-cols-8 gap-3">
                                    {/* Default (Network Color) */}
                                    <button
                                        type="button"
                                        onClick={() => setForm(prev => ({ ...prev, card_color: '' }))}
                                        className={`relative group ${!form.card_color ? 'ring-2 ring-pink-500 ring-offset-2' : ''}`}
                                    >
                                        <div className={`w-full aspect-[1.6/1] rounded-lg bg-gradient-to-br ${selectedNetwork.color} shadow-sm`} />
                                        <p className="text-xs text-gray-600 mt-1 text-center truncate">Default</p>
                                        {!form.card_color && (
                                            <div className="absolute -top-1 -right-1 w-4 h-4 bg-pink-500 rounded-full flex items-center justify-center">
                                                <svg className="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                                </svg>
                                            </div>
                                        )}
                                    </button>

                                    {/* Color Presets */}
                                    {cardColorPresets.map((color) => (
                                        <button
                                            key={color.value}
                                            type="button"
                                            onClick={() => setForm(prev => ({ ...prev, card_color: color.value }))}
                                            className={`relative group ${form.card_color === color.value ? 'ring-2 ring-pink-500 ring-offset-2' : ''}`}
                                        >
                                            <div className={`w-full aspect-[1.6/1] rounded-lg bg-gradient-to-br ${color.value} shadow-sm hover:shadow-md transition-shadow`} />
                                            <p className="text-xs text-gray-600 mt-1 text-center truncate">{color.name}</p>
                                            {form.card_color === color.value && (
                                                <div className="absolute -top-1 -right-1 w-4 h-4 bg-pink-500 rounded-full flex items-center justify-center">
                                                    <svg className="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                                                    </svg>
                                                </div>
                                            )}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Basic Information */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Card Details</h2>
                                <p className="text-sm text-gray-500 mt-0.5">Card name and bank association</p>
                            </div>
                            <div className="p-6 space-y-6">
                                {/* Names */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
                                            Card Name (English) <span className="text-red-500">*</span>
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                </svg>
                                            </div>
                                            <input
                                                type="text"
                                                id="name"
                                                name="name"
                                                value={form.name}
                                                onChange={handleChange}
                                                required
                                                placeholder="e.g., Gold Card, Platinum Card"
                                                className="block w-full pl-12 pr-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label htmlFor="name_ar" className="block text-sm font-medium text-gray-700 mb-2">
                                            Card Name (Arabic)
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
                                                placeholder="اسم البطاقة بالعربية"
                                                className="block w-full pr-12 pl-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all text-right"
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Bank Selection */}
                                <div>
                                    <label htmlFor="bank_id" className="block text-sm font-medium text-gray-700 mb-2">
                                        Associated Bank
                                    </label>
                                    <div className="relative">
                                        <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                        <select
                                            id="bank_id"
                                            name="bank_id"
                                            value={form.bank_id}
                                            onChange={handleChange}
                                            className="block w-full pl-12 pr-10 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all cursor-pointer appearance-none"
                                        >
                                            <option value="">Generic (No specific bank)</option>
                                            {banks.map(bank => (
                                                <option key={bank.id} value={bank.id}>{bank.name}</option>
                                            ))}
                                        </select>
                                        <div className="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                            <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                    <p className="mt-2 text-xs text-gray-500">
                                        Leave empty for generic card types like Visa, Mastercard that work with any bank
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* BIN Prefixes */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">BIN Prefixes</h2>
                                <p className="text-sm text-gray-500 mt-0.5">First 6-8 digits to identify this card during payment</p>
                            </div>
                            <div className="p-6">
                                {/* Add new BIN */}
                                <div className="flex gap-3 mb-4">
                                    <div className="relative flex-1">
                                        <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                            </svg>
                                        </div>
                                        <input
                                            type="text"
                                            value={newBin}
                                            onChange={(e) => setNewBin(e.target.value.replace(/\D/g, '').slice(0, 8))}
                                            onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addBinPrefix())}
                                            placeholder="Enter BIN prefix (e.g., 411111)"
                                            maxLength={8}
                                            className="block w-full pl-12 pr-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all font-mono"
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        onClick={addBinPrefix}
                                        disabled={newBin.length < 4}
                                        className="px-4 py-3 text-sm font-medium text-white bg-pink-600 rounded-xl hover:bg-pink-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        Add
                                    </button>
                                </div>

                                {/* BIN list */}
                                {form.bin_prefixes.length > 0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {form.bin_prefixes.map((bin, index) => (
                                            <div
                                                key={index}
                                                className="inline-flex items-center gap-2 px-3 py-2 bg-gray-100 rounded-lg group"
                                            >
                                                <span className="font-mono text-sm text-gray-700">{bin}</span>
                                                <button
                                                    type="button"
                                                    onClick={() => removeBinPrefix(bin)}
                                                    className="text-gray-400 hover:text-red-500 transition-colors"
                                                >
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-6 text-gray-500">
                                        <svg className="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                        </svg>
                                        <p className="text-sm">No BIN prefixes added yet</p>
                                        <p className="text-xs text-gray-400 mt-1">Add the first 6-8 digits of cards that belong to this type</p>
                                    </div>
                                )}

                                <div className="mt-4 p-3 bg-blue-50 rounded-xl">
                                    <p className="text-xs text-blue-700">
                                        <strong>Tip:</strong> BIN (Bank Identification Number) is the first 6-8 digits of a card number.
                                        When a customer enters their card during payment, the system will match it to identify the bank and card type.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Status */}
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                <h2 className="font-semibold text-gray-900">Status</h2>
                                <p className="text-sm text-gray-500 mt-0.5">Control card type visibility</p>
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
                                                {form.status === 'active' ? 'Card Type is Active' : 'Card Type is Inactive'}
                                            </p>
                                            <p className="text-sm text-gray-500">
                                                {form.status === 'active'
                                                    ? 'This card type is available for offers'
                                                    : 'This card type is hidden from the system'}
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
                                href="/admin/bank-offer/card-types"
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
                                        {isEdit ? 'Update Card Type' : 'Create Card Type'}
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
