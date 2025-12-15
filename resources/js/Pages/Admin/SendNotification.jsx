import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import axios from 'axios';

export default function SendNotification({ templates, providers }) {
    const [formData, setFormData] = useState({
        tag: 'promotional',
        title: '',
        body: '',
        channels: ['push'],
        target_type: 'all',
        target_criteria: {},
        image_url: '',
        deep_link: '',
    });

    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError('');
        setSuccess('');

        try {
            const response = await axios.post('/api/v1/admin/notifications/send', formData);
            setSuccess('Notification sent successfully!');
            setTimeout(() => {
                router.visit('/admin/notifications');
            }, 2000);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to send notification');
        } finally {
            setLoading(false);
        }
    };

    const handleChannelToggle = (channel) => {
        setFormData(prev => ({
            ...prev,
            channels: prev.channels.includes(channel)
                ? prev.channels.filter(c => c !== channel)
                : [...prev.channels, channel]
        }));
    };

    return (
        <AdminLayout>
            <div className="max-w-4xl mx-auto">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">Send Notification</h1>
                    <p className="text-gray-600 mt-1">Create and send notifications to your users</p>
                </div>

                {error && (
                    <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                        {error}
                    </div>
                )}

                {success && (
                    <div className="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                        {success}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow p-6 space-y-6">
                    {/* Tag */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Tag
                        </label>
                        <select
                            value={formData.tag}
                            onChange={(e) => setFormData({ ...formData, tag: e.target.value })}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            required
                        >
                            <option value="promotional">Promotional</option>
                            <option value="nearby">Nearby</option>
                            <option value="new_offer">New Offer</option>
                            <option value="offer_expiring">Offer Expiring</option>
                            <option value="brand_update">Brand Update</option>
                            <option value="system">System</option>
                        </select>
                    </div>

                    {/* Title */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Title *
                        </label>
                        <input
                            type="text"
                            value={formData.title}
                            onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            placeholder="Enter notification title"
                            required
                        />
                    </div>

                    {/* Body */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Message *
                        </label>
                        <textarea
                            value={formData.body}
                            onChange={(e) => setFormData({ ...formData, body: e.target.value })}
                            rows="4"
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            placeholder="Enter notification message"
                            required
                        />
                    </div>

                    {/* Channels */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Channels
                        </label>
                        <div className="flex gap-4">
                            {['push', 'sms', 'email'].map((channel) => (
                                <label key={channel} className="flex items-center">
                                    <input
                                        type="checkbox"
                                        checked={formData.channels.includes(channel)}
                                        onChange={() => handleChannelToggle(channel)}
                                        className="w-4 h-4 text-pink-500 border-gray-300 rounded focus:ring-pink-500"
                                    />
                                    <span className="ml-2 text-sm text-gray-700 capitalize">{channel}</span>
                                </label>
                            ))}
                        </div>
                    </div>

                    {/* Target Type */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Target Audience
                        </label>
                        <select
                            value={formData.target_type}
                            onChange={(e) => setFormData({ ...formData, target_type: e.target.value })}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                        >
                            <option value="all">All Users</option>
                            <option value="location">Location Based</option>
                            <option value="individual">Specific Users</option>
                        </select>
                    </div>

                    {/* Location Based Targeting */}
                    {formData.target_type === 'location' && (
                        <div className="grid grid-cols-3 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Latitude
                                </label>
                                <input
                                    type="number"
                                    step="0.000001"
                                    value={formData.target_criteria.lat || ''}
                                    onChange={(e) => setFormData({
                                        ...formData,
                                        target_criteria: { ...formData.target_criteria, lat: parseFloat(e.target.value) }
                                    })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    placeholder="31.9539"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Longitude
                                </label>
                                <input
                                    type="number"
                                    step="0.000001"
                                    value={formData.target_criteria.lng || ''}
                                    onChange={(e) => setFormData({
                                        ...formData,
                                        target_criteria: { ...formData.target_criteria, lng: parseFloat(e.target.value) }
                                    })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    placeholder="35.9106"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Radius (km)
                                </label>
                                <input
                                    type="number"
                                    value={formData.target_criteria.radius || ''}
                                    onChange={(e) => setFormData({
                                        ...formData,
                                        target_criteria: { ...formData.target_criteria, radius: parseFloat(e.target.value) }
                                    })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    placeholder="5"
                                />
                            </div>
                        </div>
                    )}

                    {/* Image URL */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Image URL (Optional)
                        </label>
                        <input
                            type="url"
                            value={formData.image_url}
                            onChange={(e) => setFormData({ ...formData, image_url: e.target.value })}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            placeholder="https://example.com/image.jpg"
                        />
                    </div>

                    {/* Deep Link */}
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Deep Link (Optional)
                        </label>
                        <input
                            type="text"
                            value={formData.deep_link}
                            onChange={(e) => setFormData({ ...formData, deep_link: e.target.value })}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            placeholder="app://offers/123"
                        />
                    </div>

                    {/* Submit Button */}
                    <div className="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            onClick={() => router.visit('/admin/notifications')}
                            className="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={loading}
                            className="px-6 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {loading ? 'Sending...' : 'Send Notification'}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
