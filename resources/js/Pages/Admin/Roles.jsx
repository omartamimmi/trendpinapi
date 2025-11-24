import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Roles({ roles, permissions }) {
    const [showModal, setShowModal] = useState(false);
    const [editingRole, setEditingRole] = useState(null);
    const [formData, setFormData] = useState({
        name: '',
        permissions: [],
    });

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this role?')) {
            router.delete(`/admin/roles/${id}`);
        }
    };

    const handleEdit = (role) => {
        setEditingRole(role);
        setFormData({
            name: role.name,
            permissions: role.permissions?.map(p => p.name) || [],
        });
        setShowModal(true);
    };

    const handleCreate = () => {
        setEditingRole(null);
        setFormData({ name: '', permissions: [] });
        setShowModal(true);
    };

    const handlePermissionToggle = (permName) => {
        setFormData(prev => ({
            ...prev,
            permissions: prev.permissions.includes(permName)
                ? prev.permissions.filter(p => p !== permName)
                : [...prev.permissions, permName]
        }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingRole) {
            router.put(`/admin/roles/${editingRole.id}`, formData);
        } else {
            router.post('/admin/roles', formData);
        }
        setShowModal(false);
    };

    return (
        <AdminLayout>
            <div className="px-4 py-6 sm:px-0">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-semibold text-gray-900">Roles & Permissions</h1>
                    <button
                        onClick={handleCreate}
                        className="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
                    >
                        Add Role
                    </button>
                </div>

                <div className="bg-white shadow overflow-hidden sm:rounded-md">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role Name</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {roles?.map((role) => (
                                <tr key={role.id}>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{role.name}</td>
                                    <td className="px-6 py-4 text-sm text-gray-500">
                                        <div className="flex flex-wrap gap-1">
                                            {role.permissions?.map(p => (
                                                <span key={p.id} className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    {p.name}
                                                </span>
                                            ))}
                                            {(!role.permissions || role.permissions.length === 0) && (
                                                <span className="text-gray-400">No permissions</span>
                                            )}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            onClick={() => handleEdit(role)}
                                            className="text-indigo-600 hover:text-indigo-900 mr-4"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            onClick={() => handleDelete(role.id)}
                                            className="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {showModal && (
                    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
                        <div className="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
                            <h2 className="text-lg font-semibold mb-4">
                                {editingRole ? 'Edit Role' : 'Create Role'}
                            </h2>
                            <form onSubmit={handleSubmit}>
                                <div className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Role Name</label>
                                        <input
                                            type="text"
                                            value={formData.name}
                                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                            className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                                        <div className="space-y-2 max-h-48 overflow-y-auto border rounded-md p-3">
                                            {permissions?.map(perm => (
                                                <label key={perm.id} className="flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        checked={formData.permissions.includes(perm.name)}
                                                        onChange={() => handlePermissionToggle(perm.name)}
                                                        className="h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                                    />
                                                    <span className="ml-2 text-sm text-gray-700">{perm.name}</span>
                                                </label>
                                            ))}
                                            {(!permissions || permissions.length === 0) && (
                                                <p className="text-gray-400 text-sm">No permissions available</p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                                <div className="mt-6 flex justify-end space-x-3">
                                    <button
                                        type="button"
                                        onClick={() => setShowModal(false)}
                                        className="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        className="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                    >
                                        {editingRole ? 'Update' : 'Create'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
