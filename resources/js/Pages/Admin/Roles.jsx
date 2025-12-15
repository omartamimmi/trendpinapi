import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/Pagination';
import { useToast } from '@/Components/Toast';
import { useConfirm } from '@/Components/ConfirmDialog';

export default function Roles({ roles, permissions, filters: initialFilters }) {
    const toast = useToast();
    const confirmDialog = useConfirm();
    const [showModal, setShowModal] = useState(false);
    const [editingRole, setEditingRole] = useState(null);
    const [filters, setFilters] = useState({
        search: initialFilters?.search || '',
    });
    const [formData, setFormData] = useState({
        name: '',
        permissions: [],
    });

    const handleFilterChange = (key, value) => {
        setFilters(prev => ({ ...prev, [key]: value }));
    };

    const applyFilters = () => {
        const activeFilters = Object.fromEntries(
            Object.entries(filters).filter(([_, v]) => v !== '')
        );
        router.get('/admin/roles', activeFilters, { preserveState: true });
    };

    const clearFilters = () => {
        setFilters({ search: '' });
        router.get('/admin/roles', {}, { preserveState: true });
    };

    const handleKeyPress = (e) => {
        if (e.key === 'Enter') {
            applyFilters();
        }
    };

    const handleDelete = (id) => {
        confirmDialog({
            title: 'Delete Role',
            message: 'Are you sure you want to delete this role? This action cannot be undone.',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            type: 'danger',
            onConfirm: () => {
                router.delete(`/admin/roles/${id}`, {
                    onSuccess: () => toast.success('Role deleted successfully'),
                    onError: () => toast.error('Failed to delete role'),
                });
            },
        });
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
                        className="bg-pink-600 text-white px-4 py-2 rounded-md hover:bg-pink-700"
                    >
                        Add Role
                    </button>
                </div>

                {/* Filters */}
                <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
                    <div className="flex items-center gap-2 mb-4">
                        <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <h3 className="text-sm font-semibold text-gray-700">Filters</h3>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {/* Search */}
                        <div className="md:col-span-2">
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg className="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    placeholder="Search roles..."
                                    value={filters.search}
                                    onChange={(e) => handleFilterChange('search', e.target.value)}
                                    onKeyPress={handleKeyPress}
                                    className="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                />
                            </div>
                        </div>

                        {/* Action Buttons */}
                        <div className="flex items-center gap-2">
                            <button
                                onClick={clearFilters}
                                className="flex-1 px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 hover:text-gray-800 transition-all"
                            >
                                Clear
                            </button>
                            <button
                                onClick={applyFilters}
                                className="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-pink-500 to-pink-600 rounded-xl hover:from-pink-600 hover:to-pink-700 shadow-sm hover:shadow transition-all"
                            >
                                Apply
                            </button>
                        </div>
                    </div>
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
                            {roles.data && roles.data.length > 0 ? roles.data.map((role) => (
                                <tr key={role.id}>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{role.name}</td>
                                    <td className="px-6 py-4 text-sm text-gray-500">
                                        <div className="flex flex-wrap gap-1">
                                            {role.permissions?.map(p => (
                                                <span key={p.id} className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-pink-100 text-pink-800">
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
                                            className="text-pink-600 hover:text-pink-900 mr-4"
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
                            )) : (
                                <tr>
                                    <td colSpan="3" className="px-6 py-4 text-center text-sm text-gray-500">
                                        No roles found
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>

                    {/* Pagination */}
                    <Pagination data={roles} />
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
                                                        className="h-4 w-4 text-pink-600 border-gray-300 rounded"
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
                                        className="px-4 py-2 bg-pink-600 text-white rounded-md hover:bg-pink-700"
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
