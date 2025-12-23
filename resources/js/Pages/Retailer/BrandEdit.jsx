import { useState, useEffect, useRef, useCallback } from 'react';
import { router, useForm, Link } from '@inertiajs/react';
import RetailerLayout from '@/Layouts/RetailerLayout';
import { MapContainer, TileLayer, Marker, useMap, useMapEvents, ZoomControl } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { useToast } from '@/Components/Toast';

// Fix Leaflet default marker icons
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
});

// Component to handle map center changes
function MapController({ center }) {
    const map = useMap();
    useEffect(() => {
        if (center) {
            map.setView(center, 15);
        }
    }, [center, map]);
    return null;
}

// Component to handle map clicks
function MapClickHandler({ onLocationSelect }) {
    useMapEvents({
        click: async (e) => {
            const { lat, lng } = e.latlng;
            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
                    { headers: { 'Accept-Language': 'en' } }
                );
                const data = await response.json();
                onLocationSelect({
                    name: data.display_name?.split(',')[0] || 'Selected Location',
                    location: data.display_name || '',
                    lat,
                    lng
                });
            } catch (error) {
                onLocationSelect({
                    name: 'Selected Location',
                    location: `${lat.toFixed(6)}, ${lng.toFixed(6)}`,
                    lat,
                    lng
                });
            }
        }
    });
    return null;
}

// Location Search Component
function LocationSearch({ onSelect }) {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState([]);
    const [isSearching, setIsSearching] = useState(false);
    const [showResults, setShowResults] = useState(false);
    const searchRef = useRef(null);
    const debounceRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (e) => {
            if (searchRef.current && !searchRef.current.contains(e.target)) {
                setShowResults(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const searchLocation = useCallback(async (searchQuery) => {
        if (!searchQuery.trim()) {
            setResults([]);
            return;
        }
        setIsSearching(true);
        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&limit=5`,
                { headers: { 'Accept-Language': 'en' } }
            );
            const data = await response.json();
            setResults(data);
            setShowResults(true);
        } catch (error) {
            setResults([]);
        }
        setIsSearching(false);
    }, []);

    const handleInputChange = (e) => {
        const value = e.target.value;
        setQuery(value);
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => searchLocation(value), 300);
    };

    const selectResult = (result) => {
        onSelect({
            name: result.display_name.split(',')[0],
            location: result.display_name,
            lat: parseFloat(result.lat),
            lng: parseFloat(result.lon)
        });
        setQuery(result.display_name);
        setShowResults(false);
    };

    return (
        <div ref={searchRef} className="relative">
            <div className="relative">
                <input
                    type="text"
                    value={query}
                    onChange={handleInputChange}
                    onFocus={() => results.length > 0 && setShowResults(true)}
                    placeholder="Search for a location..."
                    className="w-full px-4 py-3 pl-10 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all text-sm"
                />
                <div className="absolute left-3 top-1/2 -translate-y-1/2">
                    {isSearching ? (
                        <svg className="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    ) : (
                        <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    )}
                </div>
            </div>
            {showResults && results.length > 0 && (
                <div className="absolute z-[1000] w-full mt-1 bg-white rounded-xl shadow-lg border border-gray-100 max-h-60 overflow-y-auto">
                    {results.map((result, index) => (
                        <button
                            key={index}
                            type="button"
                            onClick={() => selectResult(result)}
                            className="w-full px-4 py-3 text-left hover:bg-pink-50 transition-colors border-b border-gray-50 last:border-b-0"
                        >
                            <div className="flex items-start gap-3">
                                <svg className="w-4 h-4 text-pink-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                <div>
                                    <p className="text-sm font-medium text-gray-800">{result.display_name.split(',')[0]}</p>
                                    <p className="text-xs text-gray-500 line-clamp-1">{result.display_name}</p>
                                </div>
                            </div>
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}

// Location Picker Modal
function LocationPickerModal({ isOpen, onClose, onSave, initialLocation, availableLocations = [] }) {
    const [location, setLocation] = useState(initialLocation || {
        name: '',
        location: '',
        lat: 31.963158,
        lng: 35.930359,
        location_id: null
    });
    const [showAdvanced, setShowAdvanced] = useState(false);

    useEffect(() => {
        if (initialLocation) {
            setLocation(initialLocation);
        }
    }, [initialLocation]);

    const handleAreaChange = (areaId) => {
        const selectedArea = availableLocations.find(a => a.id === parseInt(areaId));
        if (selectedArea) {
            setLocation({
                ...location,
                location_id: selectedArea.id,
                lat: parseFloat(selectedArea.lat) || 31.963158,
                lng: parseFloat(selectedArea.lng) || 35.930359,
            });
        } else {
            setLocation({
                ...location,
                location_id: null,
            });
        }
    };

    const handleLatChange = (value) => {
        const lat = parseFloat(value);
        if (!isNaN(lat) && lat >= -90 && lat <= 90) {
            setLocation({ ...location, lat });
        }
    };

    const handleLngChange = (value) => {
        const lng = parseFloat(value);
        if (!isNaN(lng) && lng >= -180 && lng <= 180) {
            setLocation({ ...location, lng });
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
            <div className="absolute inset-0 bg-black/50" onClick={onClose}></div>
            <div className="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden max-h-[90vh] overflow-y-auto">
                <div className="p-6 border-b border-gray-100">
                    <div className="flex items-center justify-between">
                        <h3 className="text-lg font-semibold text-gray-800">Select Location</h3>
                        <button onClick={onClose} className="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div className="p-6 space-y-4">
                    {/* Area/Location Selector */}
                    {availableLocations.length > 0 && (
                        <div>
                            <label className="block text-sm font-medium text-gray-600 mb-2">
                                Is this branch inside a mall or shopping area?
                            </label>
                            <select
                                value={location.location_id || ''}
                                onChange={(e) => handleAreaChange(e.target.value)}
                                className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                            >
                                <option value="">No, standalone location</option>
                                {availableLocations.map((area) => (
                                    <option key={area.id} value={area.id}>
                                        {area.name} {area.city ? `(${area.city})` : ''} - {area.type}
                                    </option>
                                ))}
                            </select>
                            {location.location_id && (
                                <p className="mt-2 text-xs text-green-600 flex items-center gap-1">
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Branch will use the area's geofence for notifications
                                </p>
                            )}
                        </div>
                    )}

                    <LocationSearch onSelect={(loc) => setLocation({ ...location, ...loc, location_id: location.location_id })} />
                    <div className="h-[250px] rounded-xl overflow-hidden border border-gray-200 relative z-0">
                        <MapContainer
                            center={[parseFloat(location.lat) || 31.963158, parseFloat(location.lng) || 35.930359]}
                            zoom={13}
                            style={{ height: '100%', width: '100%' }}
                            zoomControl={false}
                        >
                            <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                            <ZoomControl position="bottomright" />
                            <Marker position={[parseFloat(location.lat) || 31.963158, parseFloat(location.lng) || 35.930359]} />
                            <MapController center={[parseFloat(location.lat) || 31.963158, parseFloat(location.lng) || 35.930359]} />
                            <MapClickHandler onLocationSelect={(loc) => setLocation({ ...location, ...loc, location_id: location.location_id })} />
                        </MapContainer>
                    </div>

                    {/* Custom Name Field */}
                    <div>
                        <label className="block text-sm font-medium text-gray-600 mb-2">Branch Name</label>
                        <input
                            type="text"
                            value={location.name}
                            onChange={(e) => setLocation({ ...location, name: e.target.value })}
                            className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                            placeholder="Enter custom branch name"
                        />
                    </div>

                    {/* Address Display/Edit */}
                    <div>
                        <label className="block text-sm font-medium text-gray-600 mb-2">Address</label>
                        <textarea
                            value={location.location}
                            onChange={(e) => setLocation({ ...location, location: e.target.value })}
                            className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all resize-none"
                            rows="2"
                            placeholder="Enter or edit address"
                        />
                    </div>

                    {/* Advanced Options Toggle */}
                    <button
                        type="button"
                        onClick={() => setShowAdvanced(!showAdvanced)}
                        className="flex items-center gap-2 text-sm text-gray-500 hover:text-pink-500 transition-colors"
                    >
                        <svg className={`w-4 h-4 transition-transform ${showAdvanced ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        {showAdvanced ? 'Hide' : 'Show'} coordinates
                    </button>

                    {/* Lat/Lng Manual Input */}
                    {showAdvanced && (
                        <div className="grid grid-cols-2 gap-4 p-4 bg-gray-50 rounded-xl">
                            <div>
                                <label className="block text-xs font-medium text-gray-500 mb-1">Latitude</label>
                                <input
                                    type="number"
                                    step="0.000001"
                                    value={location.lat}
                                    onChange={(e) => handleLatChange(e.target.value)}
                                    className="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 text-sm focus:ring-2 focus:ring-pink-500/20 focus:border-pink-300 transition-all"
                                    placeholder="31.963158"
                                />
                            </div>
                            <div>
                                <label className="block text-xs font-medium text-gray-500 mb-1">Longitude</label>
                                <input
                                    type="number"
                                    step="0.000001"
                                    value={location.lng}
                                    onChange={(e) => handleLngChange(e.target.value)}
                                    className="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 text-sm focus:ring-2 focus:ring-pink-500/20 focus:border-pink-300 transition-all"
                                    placeholder="35.930359"
                                />
                            </div>
                        </div>
                    )}
                </div>
                <div className="p-6 border-t border-gray-100 flex justify-end gap-3">
                    <button onClick={onClose} className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button
                        onClick={() => onSave(location)}
                        className="px-6 py-2 bg-gradient-to-r from-pink-500 to-pink-600 text-white rounded-xl font-medium hover:from-pink-600 hover:to-pink-700 transition-all"
                    >
                        Save Location
                    </button>
                </div>
            </div>
        </div>
    );
}

export default function BrandEdit({ brand, categories = [], locations = [] }) {
    const toast = useToast();
    const form = useForm({
        name: brand.name || '',
        title: brand.title || '',
        title_ar: brand.title_ar || '',
        description: brand.description || '',
        description_ar: brand.description_ar || '',
        phone_number: brand.phone_number || '',
        website_link: brand.website_link || '',
        insta_link: brand.insta_link || '',
        facebook_link: brand.facebook_link || '',
        branches: brand.branches?.map(b => ({
            id: b.id || null,
            name: b.name || '',
            location: b.location || '',
            lat: b.lat || null,
            lng: b.lng || null,
            location_id: b.location_id || null,
            status: b.status || 'draft'
        })) || [{ name: '', location: '', lat: null, lng: null, location_id: null, status: 'draft' }],
        category_ids: brand.categories ? brand.categories.map(c => c.id) : [],
        status: brand.status || 'draft',
    });

    const [locationModal, setLocationModal] = useState({ open: false, branchIndex: null });
    const [error, setError] = useState(null);

    const addBranch = () => {
        form.setData('branches', [...form.data.branches, { name: '', location: '', lat: null, lng: null, location_id: null, status: 'draft' }]);
    };

    const removeBranch = (index) => {
        const branches = form.data.branches.filter((_, i) => i !== index);
        form.setData('branches', branches.length ? branches : [{ name: '', location: '', lat: null, lng: null, location_id: null, status: 'draft' }]);
    };

    const updateBranch = (index, data) => {
        const branches = [...form.data.branches];
        branches[index] = { ...branches[index], ...data };
        form.setData('branches', branches);
    };

    const updateBranchStatus = (index, status) => {
        const branches = [...form.data.branches];
        branches[index] = { ...branches[index], status };
        form.setData('branches', branches);
    };

    const openLocationModal = (index) => {
        setLocationModal({ open: true, branchIndex: index });
    };

    const handleLocationSave = (locationData) => {
        if (locationModal.branchIndex !== null) {
            updateBranch(locationModal.branchIndex, locationData);
        }
        setLocationModal({ open: false, branchIndex: null });
    };

    const handleCategoryToggle = (categoryId) => {
        if (form.data.category_ids.includes(categoryId)) {
            form.setData('category_ids', form.data.category_ids.filter(id => id !== categoryId));
        } else {
            form.setData('category_ids', [...form.data.category_ids, categoryId]);
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setError(null);

        // Validate: cannot publish without at least one published branch
        const hasPublishedBranch = form.data.branches.some(b => b.status === 'publish');
        if (form.data.status === 'publish' && !hasPublishedBranch) {
            setError('Brand must have at least one published branch to be published.');
            toast.error('Brand must have at least one published branch to be published.');
            return;
        }

        form.put(`/retailer/brands/${brand.id}`, {
            onSuccess: () => toast.success('Brand updated successfully'),
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || 'Failed to update brand');
            },
        });
    };

    return (
        <RetailerLayout>
            <div className="max-w-4xl mx-auto">
                {/* Header */}
                <div className="mb-8">
                    <Link
                        href="/retailer/brands"
                        className="inline-flex items-center text-sm text-gray-500 hover:text-pink-600 transition-colors mb-4"
                    >
                        <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Brands
                    </Link>
                    <div className="flex items-center gap-3">
                        <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-500 to-purple-600 flex items-center justify-center">
                            <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">Edit Brand</h1>
                            <p className="text-sm text-gray-500">Update brand details and settings</p>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* English Content */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                            <div className="flex items-center gap-2">
                                <span className="text-lg">🇬🇧</span>
                                <h2 className="text-sm font-semibold text-gray-700">English Content</h2>
                            </div>
                        </div>
                        <div className="p-6 space-y-5">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Brand Name <span className="text-pink-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={form.data.name}
                                        onChange={(e) => form.setData('name', e.target.value)}
                                        placeholder="Enter brand name"
                                        className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                        required
                                    />
                                    {form.errors.name && <p className="mt-2 text-sm text-red-500">{form.errors.name}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Display Title <span className="text-pink-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={form.data.title}
                                        onChange={(e) => form.setData('title', e.target.value)}
                                        placeholder="Enter display title"
                                        className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                        required
                                    />
                                    {form.errors.title && <p className="mt-2 text-sm text-red-500">{form.errors.title}</p>}
                                </div>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea
                                    value={form.data.description}
                                    onChange={(e) => form.setData('description', e.target.value)}
                                    rows={3}
                                    placeholder="Enter brand description"
                                    className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all resize-none"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Arabic Content */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                            <div className="flex items-center gap-2">
                                <span className="text-lg">🇸🇦</span>
                                <h2 className="text-sm font-semibold text-gray-700">Arabic Content</h2>
                                <span className="text-xs text-gray-400">(Optional)</span>
                            </div>
                        </div>
                        <div className="p-6 space-y-5">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">Title (Arabic)</label>
                                <input
                                    type="text"
                                    value={form.data.title_ar}
                                    onChange={(e) => form.setData('title_ar', e.target.value)}
                                    placeholder="أدخل عنوان العلامة التجارية"
                                    className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all text-right"
                                    dir="rtl"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">Description (Arabic)</label>
                                <textarea
                                    value={form.data.description_ar}
                                    onChange={(e) => form.setData('description_ar', e.target.value)}
                                    rows={3}
                                    placeholder="أدخل وصف العلامة التجارية"
                                    className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all resize-none text-right"
                                    dir="rtl"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Categories Section */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    <h2 className="text-sm font-semibold text-gray-700">Categories</h2>
                                </div>
                                {form.data.category_ids.length > 0 && (
                                    <span className="px-2.5 py-1 bg-pink-100 text-pink-600 text-xs font-medium rounded-full">
                                        {form.data.category_ids.length} selected
                                    </span>
                                )}
                            </div>
                        </div>
                        <div className="p-6">
                            <p className="text-sm text-gray-500 mb-4">
                                Select the categories this brand belongs to
                            </p>
                            {categories && categories.length > 0 ? (
                                <div className="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-72 overflow-y-auto pr-2">
                                    {categories.map((category) => (
                                        <button
                                            key={category.id}
                                            type="button"
                                            onClick={() => handleCategoryToggle(category.id)}
                                            className={`px-4 py-3 rounded-xl text-sm font-medium transition-all text-left ${
                                                form.data.category_ids.includes(category.id)
                                                    ? 'bg-pink-500 text-white'
                                                    : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                                            }`}
                                        >
                                            <div className="flex items-center gap-2">
                                                <div className={`w-4 h-4 rounded border-2 flex items-center justify-center transition-all ${
                                                    form.data.category_ids.includes(category.id)
                                                        ? 'border-white bg-white'
                                                        : 'border-gray-300'
                                                }`}>
                                                    {form.data.category_ids.includes(category.id) && (
                                                        <svg className="w-3 h-3 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                                        </svg>
                                                    )}
                                                </div>
                                                <span className="truncate">{category.name}</span>
                                            </div>
                                        </button>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <svg className="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    <p className="text-sm text-gray-500">No categories available</p>
                                </div>
                            )}
                            {form.errors.category_ids && <p className="mt-3 text-sm text-red-500">{form.errors.category_ids}</p>}
                        </div>
                    </div>

                    {/* Brand Status */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                            <div className="flex items-center gap-2">
                                <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h2 className="text-sm font-semibold text-gray-700">Brand Status</h2>
                            </div>
                        </div>
                        <div className="p-6">
                            <p className="text-sm text-gray-500 mb-4">
                                Set the visibility status of this brand. To publish, you must have at least one published branch.
                            </p>
                            {error && (
                                <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                                    <p className="text-sm text-red-600 flex items-center gap-2">
                                        <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {error}
                                    </p>
                                </div>
                            )}
                            <div className="grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    onClick={() => form.setData('status', 'draft')}
                                    className={`px-4 py-3 rounded-xl text-sm font-medium transition-all ${
                                        form.data.status === 'draft'
                                            ? 'bg-gray-900 text-white'
                                            : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                                    }`}
                                >
                                    <div className="flex items-center justify-center gap-2">
                                        <div className={`w-2 h-2 rounded-full ${form.data.status === 'draft' ? 'bg-gray-400' : 'bg-gray-300'}`}></div>
                                        Draft
                                    </div>
                                </button>
                                <button
                                    type="button"
                                    onClick={() => form.setData('status', 'publish')}
                                    className={`px-4 py-3 rounded-xl text-sm font-medium transition-all ${
                                        form.data.status === 'publish'
                                            ? 'bg-green-500 text-white'
                                            : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                                    }`}
                                >
                                    <div className="flex items-center justify-center gap-2">
                                        <div className={`w-2 h-2 rounded-full ${form.data.status === 'publish' ? 'bg-green-200' : 'bg-gray-300'}`}></div>
                                        Published
                                    </div>
                                </button>
                            </div>
                            {form.errors.status && <p className="mt-2 text-sm text-red-500">{form.errors.status}</p>}
                        </div>
                    </div>

                    {/* Contact */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                            <div className="flex items-center gap-2">
                                <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <h2 className="text-sm font-semibold text-gray-700">Contact Information</h2>
                            </div>
                        </div>
                        <div className="p-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input
                                    type="text"
                                    value={form.data.phone_number}
                                    onChange={(e) => form.setData('phone_number', e.target.value)}
                                    placeholder="+962 7XX XXX XXX"
                                    className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Social Links */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                            <div className="flex items-center gap-2">
                                <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                <h2 className="text-sm font-semibold text-gray-700">Social Links</h2>
                            </div>
                        </div>
                        <div className="p-6">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">Website</label>
                                    <input
                                        type="url"
                                        value={form.data.website_link}
                                        onChange={(e) => form.setData('website_link', e.target.value)}
                                        placeholder="https://"
                                        className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">Instagram</label>
                                    <input
                                        type="url"
                                        value={form.data.insta_link}
                                        onChange={(e) => form.setData('insta_link', e.target.value)}
                                        placeholder="https://instagram.com/"
                                        className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">Facebook</label>
                                    <input
                                        type="url"
                                        value={form.data.facebook_link}
                                        onChange={(e) => form.setData('facebook_link', e.target.value)}
                                        placeholder="https://facebook.com/"
                                        className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Branches with Map */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-100">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <h2 className="text-sm font-semibold text-gray-700">Branch Locations</h2>
                                </div>
                                <button
                                    type="button"
                                    onClick={addBranch}
                                    className="text-sm text-pink-600 hover:text-pink-700 font-medium"
                                >
                                    + Add Branch
                                </button>
                            </div>
                        </div>
                        <div className="p-6 space-y-4">
                            <p className="text-sm text-gray-500">
                                Click on a branch to set its location using the map
                            </p>
                            {form.data.branches.map((branch, index) => (
                                <div key={index} className="relative group">
                                    <div
                                        className={`p-4 rounded-xl border-2 cursor-pointer transition-all ${
                                            branch.lat && branch.lng
                                                ? 'border-pink-200 bg-pink-50/50'
                                                : 'border-gray-200 bg-gray-50 hover:border-pink-300'
                                        }`}
                                        onClick={() => openLocationModal(index)}
                                    >
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-start gap-3">
                                                <div className={`w-10 h-10 rounded-lg flex items-center justify-center ${
                                                    branch.lat && branch.lng
                                                        ? 'bg-pink-500'
                                                        : 'bg-gray-200'
                                                }`}>
                                                    <svg className={`w-5 h-5 ${branch.lat && branch.lng ? 'text-white' : 'text-gray-400'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p className="font-medium text-gray-800">
                                                        {branch.name || `Branch ${index + 1}`}
                                                    </p>
                                                    {branch.location_id && locations?.find(l => l.id === branch.location_id) && (
                                                        <p className="text-xs text-blue-600 font-medium flex items-center gap-1">
                                                            <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                            </svg>
                                                            {locations?.find(l => l.id === branch.location_id)?.name}
                                                        </p>
                                                    )}
                                                    {branch.location ? (
                                                        <p className="text-sm text-gray-500 line-clamp-1">{branch.location}</p>
                                                    ) : (
                                                        <p className="text-sm text-gray-400">Click to set location</p>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                {/* Status Toggle */}
                                                <div className="flex items-center gap-1" onClick={(e) => e.stopPropagation()}>
                                                    <button
                                                        type="button"
                                                        onClick={() => updateBranchStatus(index, 'draft')}
                                                        className={`px-2 py-1 text-xs rounded-lg transition-all ${
                                                            branch.status === 'draft'
                                                                ? 'bg-gray-800 text-white'
                                                                : 'bg-gray-100 text-gray-500 hover:bg-gray-200'
                                                        }`}
                                                    >
                                                        Draft
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => updateBranchStatus(index, 'publish')}
                                                        className={`px-2 py-1 text-xs rounded-lg transition-all ${
                                                            branch.status === 'publish'
                                                                ? 'bg-green-500 text-white'
                                                                : 'bg-gray-100 text-gray-500 hover:bg-gray-200'
                                                        }`}
                                                    >
                                                        Publish
                                                    </button>
                                                </div>
                                                {form.data.branches.length > 1 && (
                                                    <button
                                                        type="button"
                                                        onClick={(e) => {
                                                            e.stopPropagation();
                                                            removeBranch(index);
                                                        }}
                                                        className="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors opacity-0 group-hover:opacity-100"
                                                    >
                                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Actions */}
                    <div className="flex items-center justify-end gap-3 pt-4">
                        <Link
                            href="/retailer/brands"
                            className="px-6 py-3 rounded-xl text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-all"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="px-8 py-3 rounded-xl text-sm font-medium text-white bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 shadow-sm hover:shadow transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {form.processing ? (
                                <span className="flex items-center gap-2">
                                    <svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Saving...
                                </span>
                            ) : (
                                'Save Changes'
                            )}
                        </button>
                    </div>
                </form>

                {/* Location Picker Modal */}
                <LocationPickerModal
                    isOpen={locationModal.open}
                    onClose={() => setLocationModal({ open: false, branchIndex: null })}
                    onSave={handleLocationSave}
                    availableLocations={locations}
                    initialLocation={locationModal.branchIndex !== null ? {
                        name: form.data.branches[locationModal.branchIndex]?.name || '',
                        location: form.data.branches[locationModal.branchIndex]?.location || '',
                        lat: form.data.branches[locationModal.branchIndex]?.lat ? parseFloat(form.data.branches[locationModal.branchIndex].lat) : 31.963158,
                        lng: form.data.branches[locationModal.branchIndex]?.lng ? parseFloat(form.data.branches[locationModal.branchIndex].lng) : 35.930359,
                        location_id: form.data.branches[locationModal.branchIndex]?.location_id || null
                    } : null}
                />
            </div>
        </RetailerLayout>
    );
}
