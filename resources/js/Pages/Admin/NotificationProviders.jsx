import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import axios from 'axios';
import { useToast } from '@/Components/Toast';
import { useConfirm } from '@/Components/ConfirmDialog';

export default function NotificationProviders({ providers }) {
    const toast = useToast();
    const confirm = useConfirm();
    const [showModal, setShowModal] = useState(false);
    const [editingProvider, setEditingProvider] = useState(null);
    const [formData, setFormData] = useState({
        type: 'push',
        provider: 'fcm',
        name: '',
        credentials: {
            server_key: '',
        },
        is_active: true,
        priority: 1,
    });

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (editingProvider) {
                await axios.put(`/api/v1/admin/notification-providers/${editingProvider.id}`, formData);
                toast.success('Provider updated successfully');
            } else {
                await axios.post('/api/v1/admin/notification-providers', formData);
                toast.success('Provider created successfully');
            }
            router.reload();
            setShowModal(false);
            resetForm();
        } catch (error) {
            toast.error(error.response?.data?.message || 'Failed to save provider');
        }
    };

    const handleTest = async (providerId) => {
        try {
            const response = await axios.post(`/api/v1/admin/notification-providers/${providerId}/test`);
            if (response.data.valid) {
                toast.success('Provider credentials are valid!');
            } else {
                toast.warning(`Test failed: ${response.data.message}`);
            }
        } catch (error) {
            toast.error('Failed to test provider');
        }
    };

    const handleDelete = (providerId) => {
        confirm({
            title: 'Delete Provider',
            message: 'Are you sure you want to delete this provider? This action cannot be undone.',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            type: 'danger',
            onConfirm: async () => {
                try {
                    await axios.delete(`/api/v1/admin/notification-providers/${providerId}`);
                    toast.success('Provider deleted successfully');
                    router.reload();
                } catch (error) {
                    toast.error('Failed to delete provider');
                }
            },
        });
    };

    const resetForm = () => {
        setFormData({
            type: 'push',
            provider: 'fcm',
            name: '',
            credentials: { server_key: '' },
            is_active: true,
            priority: 1,
        });
        setEditingProvider(null);
    };

    const openEditModal = (provider) => {
        setEditingProvider(provider);
        setFormData({
            type: provider.type,
            provider: provider.provider,
            name: provider.name,
            credentials: provider.credentials,
            is_active: provider.is_active,
            priority: provider.priority,
        });
        setShowModal(true);
    };

    return (
        <AdminLayout>
            <div className="max-w-7xl mx-auto">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">Notification Providers</h1>
                    <button
                        onClick={() => { resetForm(); setShowModal(true); }}
                        className="px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600"
                    >
                        Add Provider
                    </button>
                </div>

                <div className="bg-white rounded-lg shadow">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {providers && providers.length > 0 ? (
                                    providers.map((provider) => (
                                        <tr key={provider.id}>
                                            <td className="px-6 py-4 text-sm font-medium text-gray-900">{provider.name}</td>
                                            <td className="px-6 py-4 text-sm text-gray-500 uppercase">{provider.type}</td>
                                            <td className="px-6 py-4 text-sm text-gray-500 uppercase">{provider.provider}</td>
                                            <td className="px-6 py-4">
                                                <span className={`px-2 py-1 text-xs font-medium rounded ${provider.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                                                    {provider.is_active ? 'Active' : 'Inactive'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-500">{provider.priority}</td>
                                            <td className="px-6 py-4 text-sm">
                                                <div className="flex gap-2">
                                                    <button
                                                        onClick={() => handleTest(provider.id)}
                                                        className="text-blue-600 hover:text-blue-800"
                                                    >
                                                        Test
                                                    </button>
                                                    <button
                                                        onClick={() => openEditModal(provider)}
                                                        className="text-pink-600 hover:text-pink-800"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        onClick={() => handleDelete(provider.id)}
                                                        className="text-red-600 hover:text-red-800"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="px-6 py-8 text-center text-gray-500">
                                            No providers configured. Click "Add Provider" to create one.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Modal */}
            {showModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-6 w-full max-w-md">
                        <h2 className="text-xl font-bold mb-4">
                            {editingProvider ? 'Edit Provider' : 'Add Provider'}
                        </h2>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input
                                    type="text"
                                    value={formData.name}
                                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select
                                    value={formData.type}
                                    onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                >
                                    <option value="push">Push</option>
                                    <option value="sms">SMS</option>
                                    <option value="email">Email</option>
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                                <input
                                    type="text"
                                    value={formData.provider}
                                    onChange={(e) => setFormData({ ...formData, provider: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="fcm, twilio, sendgrid"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Server Key</label>
                                <input
                                    type="text"
                                    value={formData.credentials.server_key}
                                    onChange={(e) => setFormData({
                                        ...formData,
                                        credentials: { ...formData.credentials, server_key: e.target.value }
                                    })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                <input
                                    type="number"
                                    value={formData.priority}
                                    onChange={(e) => setFormData({ ...formData, priority: parseInt(e.target.value) })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    min="1"
                                />
                            </div>
                            <div className="flex items-center">
                                <input
                                    type="checkbox"
                                    checked={formData.is_active}
                                    onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                                    className="w-4 h-4 text-pink-500 border-gray-300 rounded"
                                />
                                <label className="ml-2 text-sm text-gray-700">Active</label>
                            </div>
                            <div className="flex justify-end gap-3 pt-4">
                                <button
                                    type="button"
                                    onClick={() => { setShowModal(false); resetForm(); }}
                                    className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    className="px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600"
                                >
                                    {editingProvider ? 'Update' : 'Create'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
