import { useState, useEffect } from 'react';
import { router, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import axios from 'axios';

interface Location {
    id: number;
    name: string;
    name_ar: string | null;
    type: string;
    address: string | null;
    city: string | null;
    lat: number;
    lng: number;
    radius: number;
    is_active: boolean;
    branches_count: number;
    geofence_id: number | null;
    radar_geofence_id: string | null;
    synced_at: string | null;
    created_at: string;
}

interface LocationType {
    value: string;
    label: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    data: Location[];
}

interface Props {
    locations: Pagination;
    filters: {
        search: string | null;
        type: string | null;
    };
    types: LocationType[];
}

// Icons
const PlusIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
    </svg>
);

const SearchIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>
);

const EditIcon = () => (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
    </svg>
);

const TrashIcon = () => (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
    </svg>
);

const CheckIcon = () => (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
    </svg>
);

const XIcon = () => (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
    </svg>
);

const LocationIcon = () => (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
);

const BuildingIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
    </svg>
);

const UsersIcon = () => (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
);

interface Branch {
    id: number;
    name: string;
    location_id: number | null;
    brand_id: number;
    brand_name: string;
    location_name: string | null;
}

// Branches Modal Component
const BranchesModal = ({
    isOpen,
    onClose,
    location,
    onSave
}: {
    isOpen: boolean;
    onClose: () => void;
    location: Location | null;
    onSave: () => void;
}) => {
    const [allBranches, setAllBranches] = useState<Branch[]>([]);
    const [selectedBranches, setSelectedBranches] = useState<number[]>([]);
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');

    useEffect(() => {
        if (isOpen && location) {
            fetchBranches();
        }
    }, [isOpen, location]);

    const fetchBranches = async () => {
        setLoading(true);
        try {
            const response = await axios.get('/admin/geofence/locations/all-branches');
            setAllBranches(response.data.branches);
            // Pre-select branches that belong to this location
            const assigned = response.data.branches
                .filter((b: Branch) => b.location_id === location?.id)
                .map((b: Branch) => b.id);
            setSelectedBranches(assigned);
        } catch (error) {
            console.error('Failed to fetch branches', error);
        }
        setLoading(false);
    };

    const handleToggleBranch = (branchId: number) => {
        setSelectedBranches(prev =>
            prev.includes(branchId)
                ? prev.filter(id => id !== branchId)
                : [...prev, branchId]
        );
    };

    const handleSave = async () => {
        if (!location) return;
        setSaving(true);
        try {
            await axios.post(`/admin/geofence/locations/${location.id}/branches`, {
                branch_ids: selectedBranches
            });
            onSave();
            onClose();
        } catch (error) {
            alert('Failed to assign branches');
        }
        setSaving(false);
    };

    const filteredBranches = allBranches.filter(branch =>
        branch.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        branch.brand_name.toLowerCase().includes(searchTerm.toLowerCase())
    );

    // Group branches by brand
    const groupedBranches = filteredBranches.reduce((acc, branch) => {
        const brandName = branch.brand_name || 'Unknown Brand';
        if (!acc[brandName]) {
            acc[brandName] = [];
        }
        acc[brandName].push(branch);
        return acc;
    }, {} as Record<string, Branch[]>);

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
                <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900">
                            Manage Branches
                        </h3>
                        <p className="text-sm text-gray-500">{location?.name}</p>
                    </div>
                    <button onClick={onClose} className="text-gray-400 hover:text-gray-600">
                        <XIcon />
                    </button>
                </div>

                <div className="p-4 border-b border-gray-200">
                    <input
                        type="text"
                        placeholder="Search branches or brands..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                    />
                </div>

                <div className="flex-1 overflow-y-auto p-4">
                    {loading ? (
                        <div className="text-center py-8 text-gray-500">Loading branches...</div>
                    ) : Object.keys(groupedBranches).length === 0 ? (
                        <div className="text-center py-8 text-gray-500">No branches found</div>
                    ) : (
                        <div className="space-y-4">
                            {Object.entries(groupedBranches).map(([brandName, branches]) => (
                                <div key={brandName} className="bg-gray-50 rounded-lg p-4">
                                    <h4 className="font-medium text-gray-700 mb-3">{brandName}</h4>
                                    <div className="space-y-2">
                                        {branches.map((branch) => (
                                            <label
                                                key={branch.id}
                                                className={`flex items-center justify-between p-3 rounded-lg cursor-pointer transition-colors ${
                                                    selectedBranches.includes(branch.id)
                                                        ? 'bg-pink-50 border border-pink-200'
                                                        : 'bg-white border border-gray-200 hover:border-pink-300'
                                                }`}
                                            >
                                                <div className="flex items-center gap-3">
                                                    <input
                                                        type="checkbox"
                                                        checked={selectedBranches.includes(branch.id)}
                                                        onChange={() => handleToggleBranch(branch.id)}
                                                        className="w-4 h-4 text-pink-500 border-gray-300 rounded focus:ring-pink-500"
                                                    />
                                                    <span className="text-gray-900">{branch.name}</span>
                                                </div>
                                                {branch.location_id && branch.location_id !== location?.id && (
                                                    <span className="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded">
                                                        Currently at: {branch.location_name}
                                                    </span>
                                                )}
                                            </label>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                <div className="px-6 py-4 border-t border-gray-200 flex justify-between items-center bg-gray-50">
                    <span className="text-sm text-gray-600">
                        {selectedBranches.length} branch(es) selected
                    </span>
                    <div className="flex gap-3">
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            onClick={handleSave}
                            disabled={saving}
                            className="px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 disabled:opacity-50"
                        >
                            {saving ? 'Saving...' : 'Save'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

// Location Modal Component
const LocationModal = ({
    isOpen,
    onClose,
    location,
    types,
    onSave
}: {
    isOpen: boolean;
    onClose: () => void;
    location: Location | null;
    types: LocationType[];
    onSave: (data: any) => void;
}) => {
    const [formData, setFormData] = useState({
        name: '',
        name_ar: '',
        type: 'mall',
        address: '',
        address_ar: '',
        city: '',
        latitude: '',
        longitude: '',
        radius: 200,
        is_active: true,
    });
    const [saving, setSaving] = useState(false);
    const [gettingLocation, setGettingLocation] = useState(false);
    const [locationError, setLocationError] = useState<string | null>(null);

    // Reset form when location changes or modal opens
    useEffect(() => {
        if (isOpen) {
            setFormData({
                name: location?.name || '',
                name_ar: location?.name_ar || '',
                type: location?.type || 'mall',
                address: location?.address || '',
                address_ar: '',
                city: location?.city || '',
                latitude: location?.lat?.toString() || '',
                longitude: location?.lng?.toString() || '',
                radius: location?.radius || 200,
                is_active: location?.is_active ?? true,
            });
            setLocationError(null);
        }
    }, [isOpen, location]);

    if (!isOpen) return null;

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);
        await onSave(formData);
        setSaving(false);
    };

    const handleGetCurrentLocation = () => {
        if (!navigator.geolocation) {
            setLocationError('Geolocation is not supported by your browser');
            return;
        }

        setGettingLocation(true);
        setLocationError(null);

        navigator.geolocation.getCurrentPosition(
            (position) => {
                setFormData(prev => ({
                    ...prev,
                    latitude: position.coords.latitude.toString(),
                    longitude: position.coords.longitude.toString(),
                }));
                setGettingLocation(false);
            },
            (error) => {
                setGettingLocation(false);
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        setLocationError('Location permission denied');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        setLocationError('Location unavailable');
                        break;
                    case error.TIMEOUT:
                        setLocationError('Location request timed out');
                        break;
                    default:
                        setLocationError('Error getting location');
                }
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    };

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
                    <h3 className="text-lg font-semibold text-gray-900">
                        {location ? 'Edit Location' : 'Add Location'}
                    </h3>
                    <button onClick={onClose} className="text-gray-400 hover:text-gray-600">
                        <XIcon />
                    </button>
                </div>
                <form onSubmit={handleSubmit} className="p-6 space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Name (English) *</label>
                            <input
                                type="text"
                                value={formData.name}
                                onChange={(e) => setFormData(prev => ({ ...prev, name: e.target.value }))}
                                required
                                placeholder="e.g., Mall of Emirates"
                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Name (Arabic)</label>
                            <input
                                type="text"
                                value={formData.name_ar}
                                onChange={(e) => setFormData(prev => ({ ...prev, name_ar: e.target.value }))}
                                placeholder="الاسم بالعربي"
                                dir="rtl"
                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                            <select
                                value={formData.type}
                                onChange={(e) => setFormData(prev => ({ ...prev, type: e.target.value }))}
                                required
                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            >
                                {types.map((type) => (
                                    <option key={type.value} value={type.value}>
                                        {type.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input
                                type="text"
                                value={formData.city}
                                onChange={(e) => setFormData(prev => ({ ...prev, city: e.target.value }))}
                                placeholder="e.g., Dubai"
                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea
                            value={formData.address}
                            onChange={(e) => setFormData(prev => ({ ...prev, address: e.target.value }))}
                            placeholder="Full address..."
                            rows={2}
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                        />
                    </div>

                    <div>
                        <div className="flex justify-between items-center mb-1">
                            <label className="block text-sm font-medium text-gray-700">Location *</label>
                            <button
                                type="button"
                                onClick={handleGetCurrentLocation}
                                disabled={gettingLocation}
                                className="flex items-center gap-1 text-sm text-pink-600 hover:text-pink-700 disabled:opacity-50"
                            >
                                <LocationIcon />
                                {gettingLocation ? 'Getting...' : 'Use My Location'}
                            </button>
                        </div>
                        {locationError && (
                            <p className="text-xs text-red-600 mb-2">{locationError}</p>
                        )}
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <input
                                    type="number"
                                    step="any"
                                    value={formData.latitude}
                                    onChange={(e) => setFormData(prev => ({ ...prev, latitude: e.target.value }))}
                                    placeholder="Latitude"
                                    required
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <input
                                    type="number"
                                    step="any"
                                    value={formData.longitude}
                                    onChange={(e) => setFormData(prev => ({ ...prev, longitude: e.target.value }))}
                                    placeholder="Longitude"
                                    required
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                />
                            </div>
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Radius (meters) *</label>
                        <input
                            type="number"
                            min="50"
                            max="5000"
                            value={formData.radius}
                            onChange={(e) => setFormData(prev => ({ ...prev, radius: parseInt(e.target.value) || 200 }))}
                            required
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                        />
                        <p className="mt-1 text-xs text-gray-500">Geofence radius (50-5000 meters)</p>
                    </div>

                    <div className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            id="is_active"
                            checked={formData.is_active}
                            onChange={(e) => setFormData(prev => ({ ...prev, is_active: e.target.checked }))}
                            className="w-4 h-4 text-pink-500 border-gray-300 rounded focus:ring-pink-500"
                        />
                        <label htmlFor="is_active" className="text-sm text-gray-700">Active</label>
                    </div>

                    <div className="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={saving}
                            className="px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 disabled:opacity-50"
                        >
                            {saving ? 'Saving...' : 'Save'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        mall: 'Mall',
        shopping_district: 'Shopping District',
        plaza: 'Plaza',
        market: 'Market',
        other: 'Other',
    };
    return labels[type] || type;
};

const getTypeBadgeColor = (type: string) => {
    const colors: Record<string, string> = {
        mall: 'bg-blue-100 text-blue-800',
        shopping_district: 'bg-purple-100 text-purple-800',
        plaza: 'bg-green-100 text-green-800',
        market: 'bg-amber-100 text-amber-800',
        other: 'bg-gray-100 text-gray-800',
    };
    return colors[type] || 'bg-gray-100 text-gray-800';
};

export default function LocationsList({ locations, filters, types }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [type, setType] = useState(filters.type || '');
    const [modalOpen, setModalOpen] = useState(false);
    const [branchesModalOpen, setBranchesModalOpen] = useState(false);
    const [selectedLocation, setSelectedLocation] = useState<Location | null>(null);

    const handleSearch = () => {
        router.get('/admin/geofence/locations', { search, type }, { preserveState: true });
    };

    const handleManageBranches = (location: Location) => {
        setSelectedLocation(location);
        setBranchesModalOpen(true);
    };

    const handleTypeChange = (newType: string) => {
        setType(newType);
        router.get('/admin/geofence/locations', { search, type: newType }, { preserveState: true });
    };

    const handleEdit = (location: Location) => {
        setSelectedLocation(location);
        setModalOpen(true);
    };

    const handleCreate = () => {
        setSelectedLocation(null);
        setModalOpen(true);
    };

    const handleSave = async (data: any) => {
        try {
            if (selectedLocation) {
                await axios.put(`/admin/geofence/locations/${selectedLocation.id}`, data);
            } else {
                await axios.post('/admin/geofence/locations', data);
            }
            setModalOpen(false);
            router.reload();
        } catch (error) {
            alert('Failed to save location');
        }
    };

    const handleDelete = async (id: number) => {
        if (!confirm('Are you sure you want to delete this location? This will also delete its geofence.')) return;

        try {
            await axios.delete(`/admin/geofence/locations/${id}`);
            router.reload();
        } catch (error) {
            alert('Failed to delete location');
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString();
    };

    return (
        <AdminLayout>
            <div className="max-w-7xl mx-auto">
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Locations</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            Manage malls, shopping districts, and other locations with geofences
                        </p>
                    </div>
                    <button
                        onClick={handleCreate}
                        className="flex items-center gap-2 px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors"
                    >
                        <PlusIcon />
                        Add Location
                    </button>
                </div>

                {/* Filters */}
                <div className="bg-white rounded-xl shadow-sm p-4 mb-6">
                    <div className="flex flex-col md:flex-row gap-4">
                        <div className="flex-1">
                            <div className="relative">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                                    placeholder="Search locations..."
                                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                />
                                <div className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <SearchIcon />
                                </div>
                            </div>
                        </div>
                        <div className="flex gap-2">
                            <select
                                value={type}
                                onChange={(e) => handleTypeChange(e.target.value)}
                                className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            >
                                <option value="">All Types</option>
                                {types.map((t) => (
                                    <option key={t.value} value={t.value}>
                                        {t.label}
                                    </option>
                                ))}
                            </select>
                            <button
                                onClick={handleSearch}
                                className="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                            >
                                Search
                            </button>
                        </div>
                    </div>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div className="bg-white rounded-xl shadow-sm p-4">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-blue-100 rounded-lg">
                                <BuildingIcon />
                            </div>
                            <div>
                                <p className="text-2xl font-bold text-gray-900">{locations.total}</p>
                                <p className="text-sm text-gray-500">Total Locations</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-green-100 rounded-lg">
                                <CheckIcon />
                            </div>
                            <div>
                                <p className="text-2xl font-bold text-gray-900">
                                    {locations.data.filter(l => l.is_active).length}
                                </p>
                                <p className="text-sm text-gray-500">Active (this page)</p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-white rounded-xl shadow-sm p-4">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-purple-100 rounded-lg">
                                <LocationIcon />
                            </div>
                            <div>
                                <p className="text-2xl font-bold text-gray-900">
                                    {locations.data.reduce((acc, l) => acc + (l.branches_count || 0), 0)}
                                </p>
                                <p className="text-sm text-gray-500">Branches (this page)</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Table */}
                <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Location
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Coordinates
                                </th>
                                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Branches
                                </th>
                                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Synced
                                </th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {locations.data.map((location) => (
                                <tr key={location.id} className="hover:bg-gray-50">
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="font-medium text-gray-900">{location.name}</div>
                                        {location.city && (
                                            <div className="text-xs text-gray-500">{location.city}</div>
                                        )}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getTypeBadgeColor(location.type)}`}>
                                            {getTypeLabel(location.type)}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm text-gray-600">
                                            {parseFloat(String(location.lat)).toFixed(6)},
                                        </div>
                                        <div className="text-sm text-gray-600">
                                            {parseFloat(String(location.lng)).toFixed(6)}
                                        </div>
                                        <div className="text-xs text-gray-400">{location.radius}m radius</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                        <span className="inline-flex items-center gap-1 px-2 py-1 text-sm font-medium text-gray-700 bg-gray-100 rounded-full">
                                            <BuildingIcon />
                                            {location.branches_count || 0}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                        <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                                            location.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-100 text-gray-800'
                                        }`}>
                                            {location.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                        {location.radar_geofence_id ? (
                                            <span className="text-green-500"><CheckIcon /></span>
                                        ) : (
                                            <span className="text-gray-400"><XIcon /></span>
                                        )}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-right">
                                        <div className="flex justify-end gap-2">
                                            <button
                                                onClick={() => handleManageBranches(location)}
                                                className="p-2 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg"
                                                title="Manage Branches"
                                            >
                                                <UsersIcon />
                                            </button>
                                            <button
                                                onClick={() => handleEdit(location)}
                                                className="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg"
                                                title="Edit Location"
                                            >
                                                <EditIcon />
                                            </button>
                                            <button
                                                onClick={() => handleDelete(location.id)}
                                                className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg"
                                                title="Delete Location"
                                            >
                                                <TrashIcon />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {locations.data.length === 0 && (
                        <div className="px-6 py-12 text-center text-gray-500">
                            No locations found. Add your first location to start managing geofences for malls and shopping areas.
                        </div>
                    )}

                    {/* Pagination */}
                    {locations.last_page > 1 && (
                        <div className="px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                            <p className="text-sm text-gray-500">
                                Showing {(locations.current_page - 1) * locations.per_page + 1} to{' '}
                                {Math.min(locations.current_page * locations.per_page, locations.total)} of{' '}
                                {locations.total} results
                            </p>
                            <div className="flex gap-2">
                                {locations.current_page > 1 && (
                                    <Link
                                        href={`/admin/geofence/locations?page=${locations.current_page - 1}&search=${search}&type=${type}`}
                                        className="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50"
                                    >
                                        Previous
                                    </Link>
                                )}
                                {locations.current_page < locations.last_page && (
                                    <Link
                                        href={`/admin/geofence/locations?page=${locations.current_page + 1}&search=${search}&type=${type}`}
                                        className="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50"
                                    >
                                        Next
                                    </Link>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            <LocationModal
                isOpen={modalOpen}
                onClose={() => setModalOpen(false)}
                location={selectedLocation}
                types={types}
                onSave={handleSave}
            />

            <BranchesModal
                isOpen={branchesModalOpen}
                onClose={() => setBranchesModalOpen(false)}
                location={selectedLocation}
                onSave={() => router.reload()}
            />
        </AdminLayout>
    );
}
