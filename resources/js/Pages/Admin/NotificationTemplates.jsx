import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import axios from 'axios';
import { useToast } from '@/Components/Toast';
import { useConfirm } from '@/Components/ConfirmDialog';

export default function NotificationTemplates({ templates }) {
    const toast = useToast();
    const confirm = useConfirm();
    const [showModal, setShowModal] = useState(false);
    const [editingTemplate, setEditingTemplate] = useState(null);
    const [formData, setFormData] = useState({
        name: '',
        tag: 'promotional',
        title_template: '',
        body_template: '',
        deep_link_template: '',
        is_active: true,
    });

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (editingTemplate) {
                await axios.put(`/api/v1/admin/notification-templates/${editingTemplate.id}`, formData);
                toast.success('Template updated successfully');
            } else {
                await axios.post('/api/v1/admin/notification-templates', formData);
                toast.success('Template created successfully');
            }
            router.reload();
            setShowModal(false);
            resetForm();
        } catch (error) {
            toast.error(error.response?.data?.message || 'Failed to save template');
        }
    };

    const handleDelete = (templateId) => {
        confirm({
            title: 'Delete Template',
            message: 'Are you sure you want to delete this template? This action cannot be undone.',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            type: 'danger',
            onConfirm: async () => {
                try {
                    await axios.delete(`/api/v1/admin/notification-templates/${templateId}`);
                    toast.success('Template deleted successfully');
                    router.reload();
                } catch (error) {
                    toast.error('Failed to delete template');
                }
            },
        });
    };

    const resetForm = () => {
        setFormData({
            name: '',
            tag: 'promotional',
            title_template: '',
            body_template: '',
            deep_link_template: '',
            is_active: true,
        });
        setEditingTemplate(null);
    };

    const openEditModal = (template) => {
        setEditingTemplate(template);
        setFormData({
            name: template.name,
            tag: template.tag,
            title_template: template.title_template,
            body_template: template.body_template,
            deep_link_template: template.deep_link_template || '',
            is_active: template.is_active,
        });
        setShowModal(true);
    };

    return (
        <AdminLayout>
            <div className="max-w-7xl mx-auto">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">Notification Templates</h1>
                    <button
                        onClick={() => { resetForm(); setShowModal(true); }}
                        className="px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600"
                    >
                        Add Template
                    </button>
                </div>

                <div className="grid gap-4">
                    {templates && templates.length > 0 ? (
                        templates.map((template) => (
                            <div key={template.id} className="bg-white rounded-lg shadow p-6">
                                <div className="flex justify-between items-start">
                                    <div className="flex-1">
                                        <div className="flex items-center gap-3 mb-2">
                                            <h3 className="text-lg font-semibold text-gray-900">{template.name}</h3>
                                            <span className="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded">
                                                {template.tag}
                                            </span>
                                            <span className={`px-2 py-1 text-xs font-medium rounded ${template.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                                                {template.is_active ? 'Active' : 'Inactive'}
                                            </span>
                                        </div>
                                        <div className="space-y-2">
                                            <div>
                                                <span className="text-sm font-medium text-gray-700">Title: </span>
                                                <span className="text-sm text-gray-600">{template.title_template}</span>
                                            </div>
                                            <div>
                                                <span className="text-sm font-medium text-gray-700">Body: </span>
                                                <span className="text-sm text-gray-600">{template.body_template}</span>
                                            </div>
                                            {template.deep_link_template && (
                                                <div>
                                                    <span className="text-sm font-medium text-gray-700">Deep Link: </span>
                                                    <span className="text-sm text-gray-600">{template.deep_link_template}</span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                    <div className="flex gap-2 ml-4">
                                        <button
                                            onClick={() => openEditModal(template)}
                                            className="px-3 py-1 text-sm text-pink-600 hover:text-pink-800"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            onClick={() => handleDelete(template.id)}
                                            className="px-3 py-1 text-sm text-red-600 hover:text-red-800"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                            No templates found. Click "Add Template" to create one.
                        </div>
                    )}
                </div>
            </div>

            {/* Modal */}
            {showModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-6 w-full max-w-2xl">
                        <h2 className="text-xl font-bold mb-4">
                            {editingTemplate ? 'Edit Template' : 'Add Template'}
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
                                <label className="block text-sm font-medium text-gray-700 mb-1">Tag</label>
                                <select
                                    value={formData.tag}
                                    onChange={(e) => setFormData({ ...formData, tag: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                >
                                    <option value="promotional">Promotional</option>
                                    <option value="nearby">Nearby</option>
                                    <option value="new_offer">New Offer</option>
                                    <option value="offer_expiring">Offer Expiring</option>
                                    <option value="brand_update">Brand Update</option>
                                    <option value="system">System</option>
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Title Template
                                    <span className="text-xs text-gray-500 ml-2">(Use {'{'}{'{'} placeholders {'}'}{'}'})</span>
                                </label>
                                <input
                                    type="text"
                                    value={formData.title_template}
                                    onChange={(e) => setFormData({ ...formData, title_template: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="New offer from {{brand_name}}!"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Body Template
                                    <span className="text-xs text-gray-500 ml-2">(Use {'{'}{'{'} placeholders {'}'}{'}'})</span>
                                </label>
                                <textarea
                                    value={formData.body_template}
                                    onChange={(e) => setFormData({ ...formData, body_template: e.target.value })}
                                    rows="3"
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="Check out {{offer_count}} new offers from {{brand_name}}"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Deep Link Template (Optional)</label>
                                <input
                                    type="text"
                                    value={formData.deep_link_template}
                                    onChange={(e) => setFormData({ ...formData, deep_link_template: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    placeholder="app://offers/{{offer_id}}"
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
                                    {editingTemplate ? 'Update' : 'Create'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
