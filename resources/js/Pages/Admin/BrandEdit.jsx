import { useState, useEffect, useRef, useCallback } from 'react';
import { router, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { MapContainer, TileLayer, Marker, useMap, useMapEvents, ZoomControl } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { MediaUploader, MediaGallery } from '@/Components/Media';

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

export default function BrandEdit({ brand, retailer, categories = [], locations = [] }) {
    const [activeTab, setActiveTab] = useState(0);
    const [formData, setFormData] = useState({
        id: brand.id || null,
        name: brand.name || '',
        title: brand.title || '',
        title_ar: brand.title_ar || '',
        description: brand.description || '',
        description_ar: brand.description_ar || '',
        phone_number: brand.phone_number || '',
        website_link: brand.website_link || '',
        insta_link: brand.insta_link || '',
        facebook_link: brand.facebook_link || '',
        status: brand.status || 'draft',
        logo: brand.logo_media || null,
        gallery: brand.gallery_media || [],
        category_ids: brand.categories ? brand.categories.map(c => c.id) : [],
    });

    const handleCategoryToggle = (categoryId) => {
        if (formData.category_ids.includes(categoryId)) {
            setFormData({ ...formData, category_ids: formData.category_ids.filter(id => id !== categoryId) });
        } else {
            setFormData({ ...formData, category_ids: [...formData.category_ids, categoryId] });
        }
    };
    const [branches, setBranches] = useState(
        brand.branches?.map(b => ({
            id: b.id || null,
            name: b.name || '',
            location: b.location || '',
            lat: b.lat || null,
            lng: b.lng || null,
            location_id: b.location_id || null,
            status: b.status || 'draft'
        })) || []
    );
    const [saving, setSaving] = useState(false);
    const [locationModal, setLocationModal] = useState({ open: false, branchIndex: null });
    const [error, setError] = useState(null);

    const updateField = (field, value) => {
        setFormData({ ...formData, [field]: value });
    };

    const addBranch = () => {
        setLocationModal({ open: true, branchIndex: -1 });
    };

    const openEditBranchLocation = (branchIndex) => {
        setLocationModal({ open: true, branchIndex });
    };

    const handleLocationSave = (locationData) => {
        const { branchIndex } = locationModal;
        if (branchIndex === -1) {
            setBranches([...branches, {
                id: null,
                name: locationData.name || '',
                location: locationData.location || '',
                lat: locationData.lat,
                lng: locationData.lng,
                location_id: locationData.location_id || null,
                status: 'draft'
            }]);
        } else {
            const updated = [...branches];
            updated[branchIndex] = {
                ...updated[branchIndex],
                name: locationData.name || updated[branchIndex].name,
                location: locationData.location || '',
                lat: locationData.lat,
                lng: locationData.lng,
                location_id: locationData.location_id || null
            };
            setBranches(updated);
        }
        setLocationModal({ open: false, branchIndex: null });
    };

    const updateBranchStatus = (branchIndex, status) => {
        const updated = [...branches];
        updated[branchIndex] = { ...updated[branchIndex], status };
        setBranches(updated);
    };

    const getCurrentBranchLocation = () => {
        const { branchIndex } = locationModal;
        if (branchIndex === null || branchIndex === -1) return null;
        const branch = branches[branchIndex];
        if (!branch) return null;
        return {
            name: branch.name || '',
            location: branch.location || '',
            lat: branch.lat ? parseFloat(branch.lat) : 31.963158,
            lng: branch.lng ? parseFloat(branch.lng) : 35.930359,
            location_id: branch.location_id || null
        };
    };

    const removeBranch = (branchIndex) => {
        setBranches(branches.filter((_, i) => i !== branchIndex));
    };

    const handleSubmit = () => {
        setError(null);

        // Validate: cannot publish without at least one published branch
        const hasPublishedBranch = branches.some(b => b.status === 'publish');
        if (formData.status === 'publish' && !hasPublishedBranch) {
            setError('Brand must have at least one published branch to be published.');
            return;
        }

        setSaving(true);
        router.put(`/admin/brands/${brand.id}`, {
            ...formData,
            logo_id: formData.logo?.id || null,
            gallery_ids: formData.gallery?.map(m => m.id) || [],
            branches: branches,
            category_ids: formData.category_ids || [],
        }, {
            onFinish: () => setSaving(false),
            onError: (errors) => {
                if (errors.status) setError(errors.status);
            }
        });
    };

    return (
        <AdminLayout>
            <div>
                {/* Header */}
                <div className="mb-8">
                    <div className="flex items-center gap-3 mb-2">
                        <Link
                            href={`/admin/retailers/${retailer?.id}`}
                            className="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">
                                Edit Brand: {brand.name || brand.title}
                            </h1>
                            <p className="text-sm text-gray-500 mt-1">
                                {retailer?.name} - Update brand details, translations, and locations
                            </p>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Sidebar - 1/3 (right side) */}
                    <div className="lg:col-span-1 lg:order-last space-y-4">
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sticky top-4">
                            <h3 className="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Brand Summary
                            </h3>
                            <div className="space-y-3">
                                <div className="flex justify-between items-center text-sm">
                                    <span className="text-gray-500">Status</span>
                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${formData.status === 'publish' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}`}>
                                        {formData.status === 'publish' ? 'Published' : 'Draft'}
                                    </span>
                                </div>
                                <div className="flex justify-between items-center text-sm">
                                    <span className="text-gray-500">Branches</span>
                                    <span className="font-medium text-gray-700">{branches.length}</span>
                                </div>
                                <div className="flex justify-between items-center text-sm">
                                    <span className="text-gray-500">Retailer</span>
                                    <span className="font-medium text-gray-700 truncate max-w-[120px]">{retailer?.name}</span>
                                </div>
                            </div>

                            <div className="border-t border-gray-100 mt-4 pt-4 space-y-3">
                                {error && (
                                    <div className="p-3 bg-red-50 border border-red-200 rounded-xl">
                                        <p className="text-sm text-red-600 flex items-center gap-2">
                                            <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {error}
                                        </p>
                                    </div>
                                )}
                                <button
                                    onClick={handleSubmit}
                                    disabled={saving}
                                    className="w-full py-3 rounded-xl text-white font-semibold bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                >
                                    {saving ? (
                                        <>
                                            <svg className="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Saving...
                                        </>
                                    ) : (
                                        <>
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Save Changes
                                        </>
                                    )}
                                </button>
                                <Link
                                    href={`/admin/retailers/${retailer?.id}/brands`}
                                    className="w-full py-3 rounded-xl text-gray-600 font-medium bg-gray-100 hover:bg-gray-200 transition-all flex items-center justify-center gap-2"
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                    </svg>
                                    All Brands
                                </Link>
                            </div>
                        </div>
                    </div>

                    {/* Main Form - 2/3 */}
                    <div className="lg:col-span-2">
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            {/* Brand Header */}
                            <div className="p-4 bg-gradient-to-r from-pink-500 to-pink-600">
                                <div className="flex items-center gap-3">
                                    <div className="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                        <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 className="text-white font-semibold">
                                            {formData.name || 'Brand Details'}
                                        </h3>
                                        <div className="flex items-center gap-2">
                                            <span className="text-white/70 text-sm">{branches.length} branches</span>
                                            <span className={`px-2 py-0.5 rounded-full text-xs ${formData.status === 'publish' ? 'bg-green-400/30 text-white' : 'bg-white/20 text-white/80'}`}>
                                                {formData.status === 'publish' ? 'Published' : 'Draft'}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Brand Content */}
                            <div className="p-6 space-y-6">
                                {/* Language Tabs */}
                                <div className="flex gap-2 border-b border-gray-100 pb-4">
                                    <button
                                        onClick={() => setActiveTab(0)}
                                        className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${activeTab === 0 ? 'bg-pink-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}
                                    >
                                        English
                                    </button>
                                    <button
                                        onClick={() => setActiveTab(1)}
                                        className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${activeTab === 1 ? 'bg-pink-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}
                                    >
                                        Arabic
                                    </button>
                                </div>

                                {/* Basic Information */}
                                <div>
                                    <h4 className="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                        <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Basic Information
                                    </h4>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-600 mb-2">
                                                Brand Name <span className="text-red-500">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                value={formData.name}
                                                onChange={(e) => updateField('name', e.target.value)}
                                                className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                                placeholder="Enter brand name"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-600 mb-2">Status</label>
                                            <select
                                                value={formData.status}
                                                onChange={(e) => updateField('status', e.target.value)}
                                                className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                            >
                                                <option value="draft">Draft</option>
                                                <option value="publish">Published</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {/* Brand Media */}
                                <div>
                                    <h4 className="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                        <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Brand Media
                                    </h4>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <MediaUploader
                                            label="Brand Logo"
                                            value={formData.logo}
                                            onChange={(media) => updateField('logo', media)}
                                            accept="image/*"
                                            maxSize={5}
                                            placeholder="Upload brand logo"
                                            previewSize="medium"
                                        />
                                        <MediaGallery
                                            label="Gallery Images"
                                            value={formData.gallery}
                                            onChange={(media) => updateField('gallery', media)}
                                            accept="image/*"
                                            maxSize={5}
                                            maxFiles={10}
                                            columns={3}
                                            placeholder="Upload gallery images"
                                        />
                                    </div>
                                </div>

                                {/* English Content */}
                                {activeTab === 0 && (
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-600 mb-2">Title (English)</label>
                                            <input
                                                type="text"
                                                value={formData.title}
                                                onChange={(e) => updateField('title', e.target.value)}
                                                className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                                placeholder="e.g. Premium Fashion Store"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-600 mb-2">Description (English)</label>
                                            <textarea
                                                value={formData.description}
                                                onChange={(e) => updateField('description', e.target.value)}
                                                className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all resize-none"
                                                rows="3"
                                                placeholder="Describe this brand..."
                                            />
                                        </div>
                                    </div>
                                )}

                                {/* Arabic Content */}
                                {activeTab === 1 && (
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-600 mb-2">Title (Arabic)</label>
                                            <input
                                                type="text"
                                                value={formData.title_ar}
                                                onChange={(e) => updateField('title_ar', e.target.value)}
                                                className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                                placeholder="العنوان بالعربية"
                                                dir="rtl"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-600 mb-2">Description (Arabic)</label>
                                            <textarea
                                                value={formData.description_ar}
                                                onChange={(e) => updateField('description_ar', e.target.value)}
                                                className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all resize-none"
                                                rows="3"
                                                placeholder="وصف العلامة التجارية..."
                                                dir="rtl"
                                            />
                                        </div>
                                    </div>
                                )}

                                {/* Contact Information */}
                                <div>
                                    <h4 className="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                        <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        Contact Information
                                    </h4>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-600 mb-2">Phone Number</label>
                                        <input
                                            type="text"
                                            value={formData.phone_number}
                                            onChange={(e) => updateField('phone_number', e.target.value)}
                                            className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                            placeholder="+962 7XX XXX XXX"
                                        />
                                    </div>
                                </div>

                                {/* Social Links */}
                                <div>
                                    <h4 className="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                        <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        Social Links
                                    </h4>
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-600 mb-2 flex items-center gap-2">
                                                <svg className="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                                </svg>
                                                Website
                                            </label>
                                            <input
                                                type="url"
                                                value={formData.website_link}
                                                onChange={(e) => updateField('website_link', e.target.value)}
                                                className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                                placeholder="https://example.com"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-600 mb-2 flex items-center gap-2">
                                                <svg className="w-4 h-4 text-pink-500" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                                </svg>
                                                Instagram
                                            </label>
                                            <input
                                                type="url"
                                                value={formData.insta_link}
                                                onChange={(e) => updateField('insta_link', e.target.value)}
                                                className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                                placeholder="https://instagram.com/..."
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-600 mb-2 flex items-center gap-2">
                                                <svg className="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                                </svg>
                                                Facebook
                                            </label>
                                            <input
                                                type="url"
                                                value={formData.facebook_link}
                                                onChange={(e) => updateField('facebook_link', e.target.value)}
                                                className="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                                placeholder="https://facebook.com/..."
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Categories Section */}
                                <div>
                                    <div className="flex items-center justify-between mb-3">
                                        <h4 className="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                            <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                            </svg>
                                            Categories
                                        </h4>
                                        {formData.category_ids.length > 0 && (
                                            <span className="px-2.5 py-1 bg-pink-100 text-pink-600 text-xs font-medium rounded-full">
                                                {formData.category_ids.length} selected
                                            </span>
                                        )}
                                    </div>
                                    <p className="text-xs text-gray-500 mb-3">
                                        Select the categories this brand belongs to
                                    </p>
                                    {categories && categories.length > 0 ? (
                                        <div className="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto pr-2">
                                            {categories.map((category) => (
                                                <button
                                                    key={category.id}
                                                    type="button"
                                                    onClick={() => handleCategoryToggle(category.id)}
                                                    className={`px-3 py-2 rounded-lg text-xs font-medium transition-all text-left ${
                                                        formData.category_ids.includes(category.id)
                                                            ? 'bg-pink-500 text-white'
                                                            : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                                                    }`}
                                                >
                                                    <div className="flex items-center gap-2">
                                                        <div className={`w-3 h-3 rounded border flex items-center justify-center transition-all ${
                                                            formData.category_ids.includes(category.id)
                                                                ? 'border-white bg-white'
                                                                : 'border-gray-300'
                                                        }`}>
                                                            {formData.category_ids.includes(category.id) && (
                                                                <svg className="w-2 h-2 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
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
                                        <div className="text-center py-4">
                                            <p className="text-xs text-gray-400">No categories available</p>
                                        </div>
                                    )}
                                </div>

                                {/* Branch Locations */}
                                <div>
                                    <div className="flex items-center justify-between mb-3">
                                        <h4 className="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                            <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                            Branch Locations
                                        </h4>
                                        <span className="text-xs text-gray-400">{branches.length} branches</span>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {branches.map((branch, branchIndex) => (
                                            <div key={branchIndex} className="relative group">
                                                <div
                                                    className="bg-gray-50 rounded-xl overflow-hidden border border-gray-100 hover:border-pink-200 transition-colors cursor-pointer"
                                                    onClick={() => openEditBranchLocation(branchIndex)}
                                                >
                                                    <div className="h-28 bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                                                        {branch.lat && branch.lng ? (
                                                            <img
                                                                src={`https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/pin-s+e8347e(${branch.lng},${branch.lat})/${branch.lng},${branch.lat},14,0/300x150@2x?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw`}
                                                                alt="Map"
                                                                className="w-full h-full object-cover"
                                                                onError={(e) => { e.target.style.display = 'none'; }}
                                                            />
                                                        ) : (
                                                            <div className="absolute inset-0 flex items-center justify-center">
                                                                <svg className="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                </svg>
                                                            </div>
                                                        )}
                                                        <div className="absolute top-2 right-2 bg-white/90 rounded-lg px-2 py-1 flex items-center gap-1">
                                                            <svg className="w-3 h-3 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                            </svg>
                                                            <span className="text-xs text-gray-600">Edit</span>
                                                        </div>
                                                    </div>
                                                    <div className="p-3">
                                                        <div className="flex items-center justify-between mb-1">
                                                            <p className="text-sm font-medium text-gray-800">{branch.name || 'Unnamed Branch'}</p>
                                                            <select
                                                                value={branch.status || 'draft'}
                                                                onChange={(e) => { e.stopPropagation(); updateBranchStatus(branchIndex, e.target.value); }}
                                                                onClick={(e) => e.stopPropagation()}
                                                                className={`text-xs px-2 py-1 rounded-full border-0 cursor-pointer ${branch.status === 'publish' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}`}
                                                            >
                                                                <option value="draft">Draft</option>
                                                                <option value="publish">Published</option>
                                                            </select>
                                                        </div>
                                                        {branch.location && (
                                                            <p className="text-xs text-gray-500 line-clamp-1">{branch.location}</p>
                                                        )}
                                                    </div>
                                                </div>
                                                <button
                                                    onClick={(e) => { e.stopPropagation(); removeBranch(branchIndex); }}
                                                    className="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center shadow-lg"
                                                >
                                                    <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        ))}
                                        <button
                                            onClick={addBranch}
                                            className="min-h-[160px] border-2 border-dashed border-gray-200 rounded-xl flex flex-col items-center justify-center text-gray-400 hover:border-pink-300 hover:text-pink-500 hover:bg-pink-50/50 transition-all"
                                        >
                                            <svg className="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                            <span className="text-sm font-medium">Add Branch</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <LocationPickerModal
                isOpen={locationModal.open}
                onClose={() => setLocationModal({ open: false, branchIndex: null })}
                onSave={handleLocationSave}
                availableLocations={locations}
                initialLocation={getCurrentBranchLocation()}
            />
        </AdminLayout>
    );
}
