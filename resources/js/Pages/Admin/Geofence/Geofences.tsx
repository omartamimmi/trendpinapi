import { useState } from 'react';
import { router, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import axios from 'axios';

interface Geofence {
    id: number;
    name: string;
    brand_id: number | null;
    branch_id: number | null;
    brand_name: string | null;
    branch_name: string | null;
    latitude: number;
    longitude: number;
    radius: number;
    is_active: boolean;
    radar_geofence_id: string | null;
    synced_at: string | null;
    created_at: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    data: Geofence[];
}

interface Props {
    geofences: Pagination;
    filters: {
        search: string | null;
        status: string | null;
    };
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

// Location Icon
const LocationIcon = () => (
    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
);

// Geofence Modal Component
const GeofenceModal = ({
    isOpen,
    onClose,
    geofence,
    onSave
}: {
    isOpen: boolean;
    onClose: () => void;
    geofence: Geofence | null;
    onSave: (data: any) => void;
}) => {
    const [formData, setFormData] = useState({
        name: geofence?.name || '',
        brand_id: geofence?.brand_id || '',
        branch_id: geofence?.branch_id || '',
        latitude: geofence?.latitude || '',
        longitude: geofence?.longitude || '',
        radius: geofence?.radius || 200,
        is_active: geofence?.is_active ?? true,
    });
    const [saving, setSaving] = useState(false);
    const [gettingLocation, setGettingLocation] = useState(false);
    const [locationError, setLocationError] = useState<string | null>(null);

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
                        setLocationError('Location permission denied. Please allow location access.');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        setLocationError('Location information unavailable.');
                        break;
                    case error.TIMEOUT:
                        setLocationError('Location request timed out.');
                        break;
                    default:
                        setLocationError('An error occurred getting your location.');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    };

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4">
                <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 className="text-lg font-semibold text-gray-900">
                        {geofence ? 'Edit Geofence' : 'Create Geofence'}
                    </h3>
                    <button onClick={onClose} className="text-gray-400 hover:text-gray-600">
                        <XIcon />
                    </button>
                </div>
                <form onSubmit={handleSubmit} className="p-6 space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input
                            type="text"
                            value={formData.name}
                            onChange={(e) => setFormData(prev => ({ ...prev, name: e.target.value }))}
                            required
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                        />
                    </div>

                    <div>
                        <div className="flex justify-between items-center mb-1">
                            <label className="block text-sm font-medium text-gray-700">Location</label>
                            <button
                                type="button"
                                onClick={handleGetCurrentLocation}
                                disabled={gettingLocation}
                                className="flex items-center gap-1 text-sm text-pink-600 hover:text-pink-700 disabled:opacity-50"
                            >
                                <LocationIcon />
                                {gettingLocation ? 'Getting location...' : 'Use My Location'}
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
                        <label className="block text-sm font-medium text-gray-700 mb-1">Radius (meters)</label>
                        <input
                            type="number"
                            min="50"
                            max="5000"
                            value={formData.radius}
                            onChange={(e) => setFormData(prev => ({ ...prev, radius: parseInt(e.target.value) }))}
                            required
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                        />
                        <p className="mt-1 text-xs text-gray-500">Between 50 and 5000 meters</p>
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

export default function GeofencesList({ geofences, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [modalOpen, setModalOpen] = useState(false);
    const [selectedGeofence, setSelectedGeofence] = useState<Geofence | null>(null);

    const handleSearch = () => {
        router.get('/admin/geofence/geofences', { search, status }, { preserveState: true });
    };

    const handleStatusChange = (newStatus: string) => {
        setStatus(newStatus);
        router.get('/admin/geofence/geofences', { search, status: newStatus }, { preserveState: true });
    };

    const handleEdit = (geofence: Geofence) => {
        setSelectedGeofence(geofence);
        setModalOpen(true);
    };

    const handleCreate = () => {
        setSelectedGeofence(null);
        setModalOpen(true);
    };

    const handleSave = async (data: any) => {
        try {
            if (selectedGeofence) {
                await axios.put(`/admin/geofence/geofences/${selectedGeofence.id}`, data);
            } else {
                await axios.post('/admin/geofence/geofences', data);
            }
            setModalOpen(false);
            router.reload();
        } catch (error) {
            alert('Failed to save geofence');
        }
    };

    const handleDelete = async (id: number) => {
        if (!confirm('Are you sure you want to delete this geofence?')) return;

        try {
            await axios.delete(`/admin/geofence/geofences/${id}`);
            router.reload();
        } catch (error) {
            alert('Failed to delete geofence');
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
                        <h1 className="text-2xl font-bold text-gray-900">Geofences</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            Manage location-based notification zones
                        </p>
                    </div>
                    <button
                        onClick={handleCreate}
                        className="flex items-center gap-2 px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors"
                    >
                        <PlusIcon />
                        Add Geofence
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
                                    placeholder="Search geofences..."
                                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                />
                                <div className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <SearchIcon />
                                </div>
                            </div>
                        </div>
                        <div className="flex gap-2">
                            <select
                                value={status}
                                onChange={(e) => handleStatusChange(e.target.value)}
                                className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
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

                {/* Table */}
                <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Brand / Branch
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Location
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Radius
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
                            {geofences.data.map((geofence) => (
                                <tr key={geofence.id} className="hover:bg-gray-50">
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="font-medium text-gray-900">{geofence.name}</div>
                                        <div className="text-xs text-gray-500">
                                            Created {formatDate(geofence.created_at)}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm text-gray-900">
                                            {geofence.brand_name || '-'}
                                        </div>
                                        {geofence.branch_name && (
                                            <div className="text-xs text-gray-500">
                                                {geofence.branch_name}
                                            </div>
                                        )}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm text-gray-600">
                                            {parseFloat(String(geofence.latitude)).toFixed(6)},
                                        </div>
                                        <div className="text-sm text-gray-600">
                                            {parseFloat(String(geofence.longitude)).toFixed(6)}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className="text-sm text-gray-900">{geofence.radius}m</span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                        <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                                            geofence.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-100 text-gray-800'
                                        }`}>
                                            {geofence.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                        {geofence.radar_geofence_id ? (
                                            <span className="text-green-500"><CheckIcon /></span>
                                        ) : (
                                            <span className="text-gray-400"><XIcon /></span>
                                        )}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-right">
                                        <div className="flex justify-end gap-2">
                                            <button
                                                onClick={() => handleEdit(geofence)}
                                                className="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg"
                                            >
                                                <EditIcon />
                                            </button>
                                            <button
                                                onClick={() => handleDelete(geofence.id)}
                                                className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg"
                                            >
                                                <TrashIcon />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {geofences.data.length === 0 && (
                        <div className="px-6 py-12 text-center text-gray-500">
                            No geofences found
                        </div>
                    )}

                    {/* Pagination */}
                    {geofences.last_page > 1 && (
                        <div className="px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                            <p className="text-sm text-gray-500">
                                Showing {(geofences.current_page - 1) * geofences.per_page + 1} to{' '}
                                {Math.min(geofences.current_page * geofences.per_page, geofences.total)} of{' '}
                                {geofences.total} results
                            </p>
                            <div className="flex gap-2">
                                {geofences.current_page > 1 && (
                                    <Link
                                        href={`/admin/geofence/geofences?page=${geofences.current_page - 1}&search=${search}&status=${status}`}
                                        className="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50"
                                    >
                                        Previous
                                    </Link>
                                )}
                                {geofences.current_page < geofences.last_page && (
                                    <Link
                                        href={`/admin/geofence/geofences?page=${geofences.current_page + 1}&search=${search}&status=${status}`}
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

            <GeofenceModal
                isOpen={modalOpen}
                onClose={() => setModalOpen(false)}
                geofence={selectedGeofence}
                onSave={handleSave}
            />
        </AdminLayout>
    );
}
