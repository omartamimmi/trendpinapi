import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import axios from 'axios';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Geofence {
    id: number;
    name: string;
    latitude: number;
    longitude: number;
    radius: number;
    brand_id: number | null;
    branch_id: number | null;
    brand_name: string | null;
    branch_name: string | null;
}

interface Brand {
    id: number;
    name: string;
}

interface Props {
    users: User[];
    geofences: Geofence[];
    brands: Brand[];
}

interface SimulationResult {
    success: boolean;
    message: string;
    event?: any;
    result?: {
        processed: boolean;
        notification_sent: boolean;
        reason: string | null;
    };
    debug?: any;
}

interface EligibilityResult {
    success: boolean;
    eligibility: {
        should_notify: boolean;
        reason: string | null;
        offer: any | null;
    };
    user: {
        id: number;
        name: string;
        email: string;
        has_fcm_token: boolean;
    };
    user_interests: string[];
    brand_categories: string[];
    recent_notifications: { data: any[] };
    throttle_config: {
        max_per_day: number;
        max_per_week: number;
        is_quiet_hours: boolean;
    };
}

// Icons
const PlayIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
);

const CheckCircleIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
);

const XCircleIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
);

const SearchIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>
);

const MapPinIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
);

export default function GeofenceTest({ users, geofences, brands }: Props) {
    const [selectedUserId, setSelectedUserId] = useState<number | ''>('');
    const [selectedGeofenceId, setSelectedGeofenceId] = useState<number | ''>('');
    const [selectedBrandId, setSelectedBrandId] = useState<number | ''>('');
    const [latitude, setLatitude] = useState<string>('');
    const [longitude, setLongitude] = useState<string>('');
    const [eventType, setEventType] = useState<'entry' | 'exit' | 'dwell'>('entry');
    const [isSimulating, setIsSimulating] = useState(false);
    const [isCheckingEligibility, setIsCheckingEligibility] = useState(false);
    const [simulationResult, setSimulationResult] = useState<SimulationResult | null>(null);
    const [eligibilityResult, setEligibilityResult] = useState<EligibilityResult | null>(null);
    const [error, setError] = useState<string | null>(null);

    // When geofence is selected, auto-fill coordinates
    const handleGeofenceSelect = (geofenceId: number | '') => {
        setSelectedGeofenceId(geofenceId);
        if (geofenceId) {
            const geofence = geofences.find(g => g.id === geofenceId);
            if (geofence) {
                setLatitude(geofence.latitude.toString());
                setLongitude(geofence.longitude.toString());
                if (geofence.brand_id) {
                    setSelectedBrandId(geofence.brand_id);
                }
            }
        }
    };

    const handleSimulate = async () => {
        if (!selectedUserId || !latitude || !longitude) {
            setError('Please select a user and enter coordinates');
            return;
        }

        setIsSimulating(true);
        setError(null);
        setSimulationResult(null);

        try {
            const response = await axios.post('/admin/geofence/test/simulate', {
                user_id: selectedUserId,
                geofence_id: selectedGeofenceId || null,
                brand_id: selectedBrandId || null,
                latitude: parseFloat(latitude),
                longitude: parseFloat(longitude),
                event_type: eventType,
            });
            setSimulationResult(response.data);
        } catch (err: any) {
            setError(err.response?.data?.message || 'Simulation failed');
        } finally {
            setIsSimulating(false);
        }
    };

    const handleCheckEligibility = async () => {
        if (!selectedUserId || !selectedBrandId) {
            setError('Please select a user and brand to check eligibility');
            return;
        }

        setIsCheckingEligibility(true);
        setError(null);
        setEligibilityResult(null);

        try {
            const response = await axios.post('/admin/geofence/test/eligibility', {
                user_id: selectedUserId,
                brand_id: selectedBrandId,
            });
            setEligibilityResult(response.data);
        } catch (err: any) {
            setError(err.response?.data?.message || 'Eligibility check failed');
        } finally {
            setIsCheckingEligibility(false);
        }
    };

    const getReasonLabel = (reason: string | null): string => {
        const labels: Record<string, string> = {
            not_an_entry_event: 'Not an entry event (only entry triggers notifications)',
            no_user_id: 'No user ID in event',
            no_brand_identified: 'Could not identify brand from geofence',
            no_interest_match: 'User interests do not match brand categories',
            no_matching_offers: 'No active offers for this brand',
            no_matching_offer: 'No matching offer found',
            user_not_found: 'User not found',
            notifications_disabled: 'User has notifications disabled',
            daily_limit_exceeded: 'Daily notification limit reached',
            weekly_limit_exceeded: 'Weekly notification limit reached',
            quiet_hours: 'Currently in quiet hours',
            brand_cooldown: 'Brand cooldown period active',
            location_cooldown: 'Location cooldown period active',
            offer_cooldown: 'Offer cooldown period active',
            min_interval: 'Minimum interval between notifications not met',
            notification_send_failed: 'Failed to send notification (check FCM token)',
        };
        return labels[reason || ''] || reason || 'Unknown';
    };

    return (
        <AdminLayout>
            <div className="max-w-7xl mx-auto">
                {/* Header */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">Geofence Testing</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        Simulate geofence events to test the notification system without mobile app
                    </p>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Simulation Form */}
                    <div className="bg-white rounded-xl shadow-sm p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <PlayIcon />
                            Simulate Event
                        </h2>

                        <div className="space-y-4">
                            {/* User Selection */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Select User *
                                </label>
                                <select
                                    value={selectedUserId}
                                    onChange={(e) => setSelectedUserId(e.target.value ? parseInt(e.target.value) : '')}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                >
                                    <option value="">-- Select User --</option>
                                    {users.map((user) => (
                                        <option key={user.id} value={user.id}>
                                            {user.name} ({user.email})
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {/* Geofence Selection */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Select Geofence (optional)
                                </label>
                                <select
                                    value={selectedGeofenceId}
                                    onChange={(e) => handleGeofenceSelect(e.target.value ? parseInt(e.target.value) : '')}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                >
                                    <option value="">-- Select Geofence --</option>
                                    {geofences.map((geofence) => (
                                        <option key={geofence.id} value={geofence.id}>
                                            {geofence.name} {geofence.brand_name && `(${geofence.brand_name})`}
                                        </option>
                                    ))}
                                </select>
                                <p className="text-xs text-gray-500 mt-1">
                                    Selecting a geofence will auto-fill coordinates and brand
                                </p>
                            </div>

                            {/* Brand Selection */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Brand (optional override)
                                </label>
                                <select
                                    value={selectedBrandId}
                                    onChange={(e) => setSelectedBrandId(e.target.value ? parseInt(e.target.value) : '')}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                >
                                    <option value="">-- Select Brand --</option>
                                    {brands.map((brand) => (
                                        <option key={brand.id} value={brand.id}>
                                            {brand.name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {/* Coordinates */}
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Latitude *
                                    </label>
                                    <input
                                        type="number"
                                        step="any"
                                        value={latitude}
                                        onChange={(e) => setLatitude(e.target.value)}
                                        placeholder="25.2048"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Longitude *
                                    </label>
                                    <input
                                        type="number"
                                        step="any"
                                        value={longitude}
                                        onChange={(e) => setLongitude(e.target.value)}
                                        placeholder="55.2708"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                    />
                                </div>
                            </div>

                            {/* Event Type */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Event Type
                                </label>
                                <div className="flex gap-4">
                                    {(['entry', 'exit', 'dwell'] as const).map((type) => (
                                        <label key={type} className="flex items-center gap-2 cursor-pointer">
                                            <input
                                                type="radio"
                                                name="event_type"
                                                value={type}
                                                checked={eventType === type}
                                                onChange={() => setEventType(type)}
                                                className="text-pink-600 focus:ring-pink-500"
                                            />
                                            <span className="text-sm capitalize">{type}</span>
                                        </label>
                                    ))}
                                </div>
                                <p className="text-xs text-gray-500 mt-1">
                                    Only "entry" events trigger notifications
                                </p>
                            </div>

                            {/* Error Display */}
                            {error && (
                                <div className="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                                    {error}
                                </div>
                            )}

                            {/* Action Buttons */}
                            <div className="flex gap-3 pt-2">
                                <button
                                    onClick={handleSimulate}
                                    disabled={isSimulating}
                                    className="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 disabled:opacity-50 transition-colors"
                                >
                                    <PlayIcon />
                                    {isSimulating ? 'Simulating...' : 'Simulate Event'}
                                </button>
                                <button
                                    onClick={handleCheckEligibility}
                                    disabled={isCheckingEligibility || !selectedUserId || !selectedBrandId}
                                    className="flex items-center justify-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 disabled:opacity-50 transition-colors"
                                >
                                    <SearchIcon />
                                    {isCheckingEligibility ? 'Checking...' : 'Check Eligibility'}
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Results Panel */}
                    <div className="space-y-6">
                        {/* Simulation Result */}
                        {simulationResult && (
                            <div className="bg-white rounded-xl shadow-sm p-6">
                                <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    {simulationResult.result?.notification_sent ? (
                                        <span className="text-green-600"><CheckCircleIcon /></span>
                                    ) : (
                                        <span className="text-amber-600"><XCircleIcon /></span>
                                    )}
                                    Simulation Result
                                </h2>

                                <div className="space-y-4">
                                    {/* Status */}
                                    <div className={`p-4 rounded-lg ${
                                        simulationResult.result?.notification_sent
                                            ? 'bg-green-50 border border-green-200'
                                            : 'bg-amber-50 border border-amber-200'
                                    }`}>
                                        <p className={`font-medium ${
                                            simulationResult.result?.notification_sent
                                                ? 'text-green-800'
                                                : 'text-amber-800'
                                        }`}>
                                            {simulationResult.result?.notification_sent
                                                ? 'Notification Sent Successfully!'
                                                : 'Notification Not Sent'}
                                        </p>
                                        {simulationResult.result?.reason && (
                                            <p className={`text-sm mt-1 ${
                                                simulationResult.result?.notification_sent
                                                    ? 'text-green-700'
                                                    : 'text-amber-700'
                                            }`}>
                                                Reason: {getReasonLabel(simulationResult.result.reason)}
                                            </p>
                                        )}
                                    </div>

                                    {/* Event Details */}
                                    <div>
                                        <h3 className="text-sm font-medium text-gray-700 mb-2">Event Details</h3>
                                        <div className="bg-gray-50 rounded-lg p-3 text-sm">
                                            <pre className="overflow-x-auto whitespace-pre-wrap">
                                                {JSON.stringify(simulationResult.event, null, 2)}
                                            </pre>
                                        </div>
                                    </div>

                                    {/* Debug Info */}
                                    {simulationResult.debug && Object.keys(simulationResult.debug).length > 0 && (
                                        <div>
                                            <h3 className="text-sm font-medium text-gray-700 mb-2">Debug Info</h3>
                                            <div className="bg-gray-50 rounded-lg p-3 text-sm">
                                                <pre className="overflow-x-auto whitespace-pre-wrap">
                                                    {JSON.stringify(simulationResult.debug, null, 2)}
                                                </pre>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}

                        {/* Eligibility Result */}
                        {eligibilityResult && (
                            <div className="bg-white rounded-xl shadow-sm p-6">
                                <h2 className="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <SearchIcon />
                                    Eligibility Check
                                </h2>

                                <div className="space-y-4">
                                    {/* Main Status */}
                                    <div className={`p-4 rounded-lg ${
                                        eligibilityResult.eligibility.should_notify
                                            ? 'bg-green-50 border border-green-200'
                                            : 'bg-red-50 border border-red-200'
                                    }`}>
                                        <div className="flex items-center gap-2">
                                            {eligibilityResult.eligibility.should_notify ? (
                                                <span className="text-green-600"><CheckCircleIcon /></span>
                                            ) : (
                                                <span className="text-red-600"><XCircleIcon /></span>
                                            )}
                                            <p className={`font-medium ${
                                                eligibilityResult.eligibility.should_notify
                                                    ? 'text-green-800'
                                                    : 'text-red-800'
                                            }`}>
                                                {eligibilityResult.eligibility.should_notify
                                                    ? 'User IS eligible for notifications'
                                                    : 'User is NOT eligible'}
                                            </p>
                                        </div>
                                        {eligibilityResult.eligibility.reason && (
                                            <p className={`text-sm mt-1 ${
                                                eligibilityResult.eligibility.should_notify
                                                    ? 'text-green-700'
                                                    : 'text-red-700'
                                            }`}>
                                                Reason: {getReasonLabel(eligibilityResult.eligibility.reason)}
                                            </p>
                                        )}
                                    </div>

                                    {/* User Info */}
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <h3 className="text-sm font-medium text-gray-700 mb-2">User</h3>
                                            <div className="text-sm text-gray-600">
                                                <p>{eligibilityResult.user.name}</p>
                                                <p>{eligibilityResult.user.email}</p>
                                                <p className={`mt-1 ${eligibilityResult.user.has_fcm_token ? 'text-green-600' : 'text-red-600'}`}>
                                                    {eligibilityResult.user.has_fcm_token ? 'Has FCM Token' : 'No FCM Token'}
                                                </p>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 className="text-sm font-medium text-gray-700 mb-2">Throttle Status</h3>
                                            <div className="text-sm text-gray-600">
                                                <p>Max/Day: {eligibilityResult.throttle_config.max_per_day}</p>
                                                <p>Max/Week: {eligibilityResult.throttle_config.max_per_week}</p>
                                                <p className={eligibilityResult.throttle_config.is_quiet_hours ? 'text-amber-600' : 'text-green-600'}>
                                                    {eligibilityResult.throttle_config.is_quiet_hours ? 'Quiet Hours Active' : 'Not Quiet Hours'}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Interests & Categories */}
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <h3 className="text-sm font-medium text-gray-700 mb-2">User Interests</h3>
                                            {eligibilityResult.user_interests.length > 0 ? (
                                                <div className="flex flex-wrap gap-1">
                                                    {eligibilityResult.user_interests.map((interest, i) => (
                                                        <span key={i} className="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                                                            {interest}
                                                        </span>
                                                    ))}
                                                </div>
                                            ) : (
                                                <p className="text-sm text-gray-500">No interests selected</p>
                                            )}
                                        </div>
                                        <div>
                                            <h3 className="text-sm font-medium text-gray-700 mb-2">Brand Categories</h3>
                                            {eligibilityResult.brand_categories.length > 0 ? (
                                                <div className="flex flex-wrap gap-1">
                                                    {eligibilityResult.brand_categories.map((cat, i) => (
                                                        <span key={i} className="px-2 py-1 bg-purple-100 text-purple-700 text-xs rounded-full">
                                                            {cat}
                                                        </span>
                                                    ))}
                                                </div>
                                            ) : (
                                                <p className="text-sm text-gray-500">No categories assigned</p>
                                            )}
                                        </div>
                                    </div>

                                    {/* Matching Offer */}
                                    {eligibilityResult.eligibility.offer && (
                                        <div>
                                            <h3 className="text-sm font-medium text-gray-700 mb-2">Matching Offer</h3>
                                            <div className="p-3 bg-green-50 rounded-lg">
                                                <p className="font-medium text-green-800">
                                                    {eligibilityResult.eligibility.offer.title}
                                                </p>
                                                {eligibilityResult.eligibility.offer.discount_percentage && (
                                                    <p className="text-sm text-green-700">
                                                        {eligibilityResult.eligibility.offer.discount_percentage}% off
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    )}

                                    {/* Recent Notifications */}
                                    <div>
                                        <h3 className="text-sm font-medium text-gray-700 mb-2">Recent Notifications (This User)</h3>
                                        {eligibilityResult.recent_notifications.data.length > 0 ? (
                                            <div className="space-y-2">
                                                {eligibilityResult.recent_notifications.data.map((notif: any, i: number) => (
                                                    <div key={i} className="p-2 bg-gray-50 rounded text-sm">
                                                        <p className="text-gray-700">{notif.brand_name} - {notif.offer_title || 'N/A'}</p>
                                                        <p className="text-xs text-gray-500">{new Date(notif.created_at).toLocaleString()}</p>
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <p className="text-sm text-gray-500">No recent notifications</p>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Help Section */}
                        {!simulationResult && !eligibilityResult && (
                            <div className="bg-blue-50 border border-blue-200 rounded-xl p-6">
                                <h2 className="text-lg font-semibold text-blue-900 mb-3 flex items-center gap-2">
                                    <MapPinIcon />
                                    How to Test
                                </h2>
                                <ol className="list-decimal list-inside space-y-2 text-sm text-blue-800">
                                    <li>Select a user from the dropdown</li>
                                    <li>Choose a geofence (this auto-fills coordinates and brand)</li>
                                    <li>Or manually enter coordinates and select a brand</li>
                                    <li>Click "Simulate Event" to trigger the notification flow</li>
                                    <li>Use "Check Eligibility" to see why a user might/might not get notified</li>
                                </ol>
                                <div className="mt-4 p-3 bg-blue-100 rounded-lg">
                                    <p className="text-sm text-blue-900">
                                        <strong>Note:</strong> For notifications to be sent, the user must:
                                    </p>
                                    <ul className="list-disc list-inside text-sm text-blue-800 mt-1">
                                        <li>Have interests matching the brand's categories</li>
                                        <li>Have a valid FCM token (from mobile app)</li>
                                        <li>Not be in a cooldown period</li>
                                        <li>Not exceed daily/weekly limits</li>
                                        <li>Not be in quiet hours</li>
                                    </ul>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
