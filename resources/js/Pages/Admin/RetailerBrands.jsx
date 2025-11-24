import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function RetailerBrands({ retailer, brands, groups }) {
    const [expandedBrands, setExpandedBrands] = useState([0]);
    const [brandList, setBrandList] = useState(brands || [
        {
            id: null,
            name: '',
            gallery: [],
            description: '',
            discount_type: 'percentage',
            discount_value: '',
            buy_one_get: '',
            apply_all_branches: false,
            start_date: '',
            end_date: '',
            branches: []
        }
    ]);

    const toggleBrand = (index) => {
        setExpandedBrands(prev =>
            prev.includes(index)
                ? prev.filter(i => i !== index)
                : [...prev, index]
        );
    };

    const addBrand = () => {
        setBrandList([...brandList, {
            id: null,
            name: 'New Brand',
            gallery: [],
            description: '',
            discount_type: 'percentage',
            discount_value: '',
            buy_one_get: '',
            apply_all_branches: false,
            start_date: '',
            end_date: '',
            branches: []
        }]);
        setExpandedBrands([...expandedBrands, brandList.length]);
    };

    const removeBrand = (index) => {
        if (brandList.length > 1) {
            setBrandList(brandList.filter((_, i) => i !== index));
            setExpandedBrands(expandedBrands.filter(i => i !== index).map(i => i > index ? i - 1 : i));
        }
    };

    const updateBrand = (index, field, value) => {
        const updated = [...brandList];
        updated[index] = { ...updated[index], [field]: value };
        setBrandList(updated);
    };

    const addBranch = (brandIndex) => {
        const updated = [...brandList];
        updated[brandIndex].branches.push({
            id: null,
            name: '',
            location: '',
            lat: null,
            lng: null,
            discount_value: '',
            buy_one_get: '',
            start_date: '',
            end_date: ''
        });
        setBrandList(updated);
    };

    const removeBranch = (brandIndex, branchIndex) => {
        const updated = [...brandList];
        updated[brandIndex].branches = updated[brandIndex].branches.filter((_, i) => i !== branchIndex);
        setBrandList(updated);
    };

    const updateBranch = (brandIndex, branchIndex, field, value) => {
        const updated = [...brandList];
        updated[brandIndex].branches[branchIndex] = {
            ...updated[brandIndex].branches[branchIndex],
            [field]: value
        };
        setBrandList(updated);
    };

    const handleSubmit = () => {
        router.post(`/admin/retailers/${retailer?.id}/brands`, {
            brands: brandList
        });
    };

    return (
        <AdminLayout>
            <div className="max-w-6xl">
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">
                            {retailer?.name || 'New Retailer'} - Brands
                        </h1>
                        <p className="text-sm text-gray-500">Manage brands and offers</p>
                    </div>
                    <button
                        onClick={handleSubmit}
                        className="px-6 py-2 rounded-lg text-white font-medium"
                        style={{ backgroundColor: '#E91E8C' }}
                    >
                        Save Changes
                    </button>
                </div>

                <div className="grid grid-cols-3 gap-6">
                    {/* Brand Forms */}
                    <div className="col-span-2 space-y-4">
                        {brandList.map((brand, brandIndex) => (
                            <div key={brandIndex} className="bg-white rounded-xl shadow-sm overflow-hidden">
                                {/* Brand Header */}
                                <div
                                    className="flex items-center justify-between p-4 cursor-pointer"
                                    style={{ backgroundColor: '#E91E8C' }}
                                    onClick={() => toggleBrand(brandIndex)}
                                >
                                    <input
                                        type="text"
                                        value={brand.name}
                                        onChange={(e) => updateBrand(brandIndex, 'name', e.target.value)}
                                        onClick={(e) => e.stopPropagation()}
                                        className="bg-transparent text-white font-medium border-none focus:outline-none"
                                        placeholder="Brand Name"
                                    />
                                    <div className="flex items-center space-x-2">
                                        <button
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                removeBrand(brandIndex);
                                            }}
                                            className="p-1 rounded-full bg-white/20 text-white hover:bg-white/30"
                                        >
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <svg
                                            className={`w-5 h-5 text-white transition-transform ${expandedBrands.includes(brandIndex) ? 'rotate-180' : ''}`}
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>

                                {/* Brand Content */}
                                {expandedBrands.includes(brandIndex) && (
                                    <div className="p-6 space-y-6">
                                        {/* Gallery Images */}
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Brand {brandIndex + 1} Gallery Images <span className="text-red-500">*</span>
                                            </label>
                                            <div className="border-2 border-dashed border-gray-200 rounded-lg p-8 text-center">
                                                <svg className="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p className="text-sm text-gray-500">Click to drop image</p>
                                            </div>
                                        </div>

                                        {/* Description */}
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Description
                                            </label>
                                            <textarea
                                                value={brand.description}
                                                onChange={(e) => updateBrand(brandIndex, 'description', e.target.value)}
                                                className="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                                rows="3"
                                                placeholder="Type description here"
                                            />
                                        </div>

                                        {/* Discount Types */}
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-3">
                                                Discounts Types
                                            </label>
                                            <div className="grid grid-cols-3 gap-4">
                                                <div>
                                                    <label className="block text-xs text-gray-500 mb-1">Percentage %</label>
                                                    <input
                                                        type="text"
                                                        value={brand.discount_value}
                                                        onChange={(e) => updateBrand(brandIndex, 'discount_value', e.target.value)}
                                                        className="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                                        placeholder="%"
                                                    />
                                                </div>
                                                <div>
                                                    <label className="block text-xs text-gray-500 mb-1">Buy one Get</label>
                                                    <div className="flex items-center">
                                                        <input
                                                            type="text"
                                                            value={brand.buy_one_get}
                                                            onChange={(e) => updateBrand(brandIndex, 'buy_one_get', e.target.value)}
                                                            className="flex-1 px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                                            placeholder="Buy 1 Get 1"
                                                        />
                                                        <input
                                                            type="checkbox"
                                                            className="ml-2 w-4 h-4"
                                                            style={{ accentColor: '#E91E8C' }}
                                                        />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label className="block text-xs text-gray-500 mb-1">Apply Discount for all branches</label>
                                                    <div className="flex items-center">
                                                        <input
                                                            type="text"
                                                            className="flex-1 px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                                            placeholder="Apply for all branches"
                                                            disabled
                                                        />
                                                        <input
                                                            type="checkbox"
                                                            checked={brand.apply_all_branches}
                                                            onChange={(e) => updateBrand(brandIndex, 'apply_all_branches', e.target.checked)}
                                                            className="ml-2 w-4 h-4"
                                                            style={{ accentColor: '#E91E8C' }}
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Discount Period */}
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-3">
                                                Discount Period
                                            </label>
                                            <div className="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label className="block text-xs text-gray-500 mb-1">Start</label>
                                                    <input
                                                        type="date"
                                                        value={brand.start_date}
                                                        onChange={(e) => updateBrand(brandIndex, 'start_date', e.target.value)}
                                                        className="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                                    />
                                                </div>
                                                <div>
                                                    <label className="block text-xs text-gray-500 mb-1">End</label>
                                                    <input
                                                        type="date"
                                                        value={brand.end_date}
                                                        onChange={(e) => updateBrand(brandIndex, 'end_date', e.target.value)}
                                                        className="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        {/* Branches/Locations */}
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-3">
                                                Location
                                            </label>
                                            <div className="flex flex-wrap gap-4 mb-4">
                                                {brand.branches.map((branch, branchIndex) => (
                                                    <div key={branchIndex} className="relative">
                                                        <div className="w-40 h-32 bg-gray-100 rounded-lg overflow-hidden">
                                                            <div className="h-24 bg-gray-200 flex items-center justify-center">
                                                                <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                </svg>
                                                            </div>
                                                            <div className="p-2 text-center">
                                                                <input
                                                                    type="text"
                                                                    value={branch.name}
                                                                    onChange={(e) => updateBranch(brandIndex, branchIndex, 'name', e.target.value)}
                                                                    className="w-full text-xs text-center border-none focus:outline-none bg-transparent"
                                                                    placeholder="Branch name"
                                                                />
                                                            </div>
                                                        </div>
                                                        <button
                                                            onClick={() => removeBranch(brandIndex, branchIndex)}
                                                            className="absolute -bottom-2 left-1/2 transform -translate-x-1/2 p-2 rounded-full text-white"
                                                            style={{ backgroundColor: '#E91E8C' }}
                                                        >
                                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                ))}
                                                <button
                                                    onClick={() => addBranch(brandIndex)}
                                                    className="w-40 h-32 border-2 border-dashed border-gray-200 rounded-lg flex flex-col items-center justify-center text-gray-400 hover:border-pink-300 hover:text-pink-500"
                                                >
                                                    <svg className="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    </svg>
                                                    <span className="text-xs px-3 py-1 rounded-full" style={{ backgroundColor: '#E91E8C', color: 'white' }}>
                                                        Add Branch
                                                    </span>
                                                </button>
                                            </div>

                                            {/* Branch-specific discounts */}
                                            {!brand.apply_all_branches && brand.branches.map((branch, branchIndex) => (
                                                <div key={branchIndex} className="mt-4 p-4 bg-gray-50 rounded-lg">
                                                    <h4 className="text-sm font-medium text-gray-700 mb-3">
                                                        {branch.name || `Branch ${branchIndex + 1}`}
                                                    </h4>
                                                    <div className="grid grid-cols-2 gap-4 mb-3">
                                                        <div>
                                                            <label className="block text-xs text-gray-500 mb-1">Percentage %</label>
                                                            <input
                                                                type="text"
                                                                value={branch.discount_value}
                                                                onChange={(e) => updateBranch(brandIndex, branchIndex, 'discount_value', e.target.value)}
                                                                className="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                                                placeholder="%"
                                                            />
                                                        </div>
                                                        <div>
                                                            <label className="block text-xs text-gray-500 mb-1">Buy one Get</label>
                                                            <div className="flex items-center">
                                                                <input
                                                                    type="text"
                                                                    value={branch.buy_one_get}
                                                                    onChange={(e) => updateBranch(brandIndex, branchIndex, 'buy_one_get', e.target.value)}
                                                                    className="flex-1 px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                                                    placeholder="Buy 1 Get 1"
                                                                />
                                                                <input type="checkbox" className="ml-2 w-4 h-4" style={{ accentColor: '#E91E8C' }} />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label className="block text-xs text-gray-500 mb-1">Discount Period</label>
                                                        <div className="grid grid-cols-2 gap-4">
                                                            <div className="flex items-center">
                                                                <span className="text-xs text-gray-500 mr-2">Start</span>
                                                                <input
                                                                    type="date"
                                                                    value={branch.start_date}
                                                                    onChange={(e) => updateBranch(brandIndex, branchIndex, 'start_date', e.target.value)}
                                                                    className="flex-1 px-2 py-1 border border-gray-200 rounded text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
                                                                />
                                                            </div>
                                                            <div className="flex items-center">
                                                                <span className="text-xs text-gray-500 mr-2">End</span>
                                                                <input
                                                                    type="date"
                                                                    value={branch.end_date}
                                                                    onChange={(e) => updateBranch(brandIndex, branchIndex, 'end_date', e.target.value)}
                                                                    className="flex-1 px-2 py-1 border border-gray-200 rounded text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>

                    {/* Brand List Sidebar */}
                    <div className="space-y-2">
                        {brandList.map((brand, index) => (
                            <div
                                key={index}
                                className="flex items-center justify-between p-3 rounded-lg text-white cursor-pointer"
                                style={{ backgroundColor: '#E91E8C' }}
                                onClick={() => {
                                    setExpandedBrands([index]);
                                    document.querySelector(`[data-brand-index="${index}"]`)?.scrollIntoView({ behavior: 'smooth' });
                                }}
                            >
                                <span className="font-medium">{brand.name || 'Unnamed Brand'}</span>
                                <div className="flex items-center space-x-1">
                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            removeBrand(index);
                                        }}
                                        className="p-1 rounded-full bg-white/20 hover:bg-white/30"
                                    >
                                        <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        ))}
                        <button
                            onClick={addBrand}
                            className="w-full p-3 border-2 border-dashed border-pink-300 rounded-lg text-pink-500 font-medium hover:bg-pink-50"
                        >
                            + Add Brand
                        </button>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
