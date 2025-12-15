import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/Pagination';
import { useToast } from '@/Components/Toast';
import { useConfirm } from '@/Components/ConfirmDialog';

export default function Plans({ plans, currentType = 'retailer' }) {
    const toast = useToast();
    const confirm = useConfirm();
    const [showModal, setShowModal] = useState(false);
    const [editingPlan, setEditingPlan] = useState(null);
    const [search, setSearch] = useState('');
    const [formData, setFormData] = useState({
        name: '',
        type: currentType,
        description: '',
        price: '',
        duration_months: 1,
        offers_count: '',
        billing_period: 'monthly',
        trial_days: 0,
        color: '',
        is_active: true,
    });

    const tabs = [
        { id: 'user', name: 'End Users' },
        { id: 'retailer', name: 'Retailers' },
        { id: 'bank', name: 'Banks' },
    ];

    const handleTabChange = (type) => {
        router.get('/admin/plans', { type }, { preserveState: true });
    };

    const handleDelete = (id) => {
        confirm({
            title: 'Delete Plan',
            message: 'Are you sure you want to delete this plan? This action cannot be undone.',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            type: 'danger',
            onConfirm: () => {
                router.delete(`/admin/plans/${id}`, {
                    onSuccess: () => toast.success('Plan deleted successfully'),
                    onError: () => toast.error('Failed to delete plan'),
                });
            },
        });
    };

    const handleEdit = (plan) => {
        setEditingPlan(plan);
        setFormData({
            name: plan.name,
            type: plan.type || currentType,
            description: plan.description || '',
            price: plan.price,
            duration_months: plan.duration_months || 1,
            offers_count: plan.offers_count,
            billing_period: plan.billing_period || 'monthly',
            trial_days: plan.trial_days || 0,
            color: plan.color || '',
            is_active: plan.is_active,
        });
        setShowModal(true);
    };

    const handleCreate = () => {
        setEditingPlan(null);
        setFormData({
            name: '',
            type: currentType,
            description: '',
            price: '',
            duration_months: 1,
            offers_count: '',
            billing_period: 'monthly',
            trial_days: 0,
            color: '',
            is_active: true,
        });
        setShowModal(true);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingPlan) {
            router.put(`/admin/plans/${editingPlan.id}`, formData);
        } else {
            router.post('/admin/plans', formData);
        }
        setShowModal(false);
    };

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/admin/plans', { type: currentType, search }, { preserveState: true });
    };

    return (
        <AdminLayout>
            <div className="px-4 py-6 sm:px-0">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-semibold text-gray-900">Subscription Plans</h1>
                    <button
                        onClick={handleCreate}
                        className="text-white px-4 py-2 rounded-md hover:opacity-90"
                        style={{ backgroundColor: '#E91E8C' }}
                    >
                        Add Plan
                    </button>
                </div>

                {/* Search */}
                <div className="mb-4">
                    <form onSubmit={handleSearch} className="flex gap-2">
                        <input
                            type="text"
                            placeholder="Search plans..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="flex-1 rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                        />
                        <button
                            type="submit"
                            className="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                        >
                            Search
                        </button>
                    </form>
                </div>

                {/* Tabs */}
                <div className="border-b border-gray-200 mb-6">
                    <nav className="-mb-px flex space-x-8">
                        {tabs.map((tab) => (
                            <button
                                key={tab.id}
                                onClick={() => handleTabChange(tab.id)}
                                className={`py-4 px-1 border-b-2 font-medium text-sm ${
                                    currentType === tab.id
                                        ? 'border-pink-500 text-pink-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                {tab.name}
                            </button>
                        ))}
                    </nav>
                </div>

                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-6">
                    {plans.data && plans.data.length > 0 ? plans.data.map((plan) => (
                        <div key={plan.id} className="bg-white overflow-hidden shadow rounded-lg">
                            <div className="px-4 py-5 sm:p-6">
                                <div className="flex justify-between items-start">
                                    <h3 className="text-lg font-medium text-gray-900">{plan.name}</h3>
                                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${plan.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                                        {plan.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </div>
                                <p className="mt-1 text-sm text-gray-500">{plan.description}</p>
                                <div className="mt-4">
                                    <p className="text-3xl font-bold text-gray-900">
                                        {plan.price} JD
                                        <span className="text-sm font-normal text-gray-500">/{plan.duration_months} month{plan.duration_months > 1 ? 's' : ''}</span>
                                    </p>
                                </div>
                                <ul className="mt-4 space-y-2">
                                    <li className="flex items-center text-sm text-gray-500">
                                        <svg className="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                        </svg>
                                        {plan.offers_count} offers
                                    </li>
                                    {plan.trial_days > 0 && (
                                        <li className="flex items-center text-sm text-green-600">
                                            <svg className="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                            {plan.trial_days} days trial
                                        </li>
                                    )}
                                </ul>
                            </div>
                            <div className="bg-gray-50 px-4 py-4 sm:px-6">
                                <div className="flex justify-end space-x-3">
                                    <button
                                        onClick={() => handleEdit(plan)}
                                        className="text-pink-600 hover:text-pink-900 text-sm font-medium"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        onClick={() => handleDelete(plan.id)}
                                        className="text-red-600 hover:text-red-900 text-sm font-medium"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    )) : (
                        <div className="col-span-full text-center py-12 bg-white rounded-lg shadow">
                            <p className="text-sm text-gray-500">No plans found</p>
                        </div>
                    )}
                </div>

                {/* Pagination */}
                <div className="bg-white shadow rounded-lg">
                    <Pagination data={plans} />
                </div>

                {showModal && (
                    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
                        <div className="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
                            <h2 className="text-lg font-semibold mb-4">
                                {editingPlan ? 'Edit Plan' : 'Create Plan'}
                            </h2>
                            <form onSubmit={handleSubmit}>
                                <div className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Name</label>
                                        <input
                                            type="text"
                                            value={formData.name}
                                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                            className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Plan Type</label>
                                        <select
                                            value={formData.type}
                                            onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                                            className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                            required
                                        >
                                            <option value="user">End Users</option>
                                            <option value="retailer">Retailers</option>
                                            <option value="bank">Banks</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea
                                            value={formData.description}
                                            onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                            className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                            rows="2"
                                        />
                                    </div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Price (JD)</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                value={formData.price}
                                                onChange={(e) => setFormData({ ...formData, price: e.target.value })}
                                                className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Offers Count</label>
                                            <input
                                                type="number"
                                                value={formData.offers_count}
                                                onChange={(e) => setFormData({ ...formData, offers_count: e.target.value })}
                                                className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Duration (months)</label>
                                            <input
                                                type="number"
                                                value={formData.duration_months}
                                                onChange={(e) => setFormData({ ...formData, duration_months: e.target.value })}
                                                className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Trial Days</label>
                                            <input
                                                type="number"
                                                value={formData.trial_days}
                                                onChange={(e) => setFormData({ ...formData, trial_days: e.target.value })}
                                                className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Billing Period</label>
                                        <select
                                            value={formData.billing_period}
                                            onChange={(e) => setFormData({ ...formData, billing_period: e.target.value })}
                                            className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                        >
                                            <option value="monthly">Monthly</option>
                                            <option value="yearly">Yearly</option>
                                        </select>
                                    </div>
                                    <div className="flex items-center">
                                        <label className="flex items-center">
                                            <input
                                                type="checkbox"
                                                checked={formData.is_active}
                                                onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                                                className="h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                            />
                                            <span className="ml-2 text-sm text-gray-700">Active</span>
                                        </label>
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
                                        {editingPlan ? 'Update' : 'Create'}
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
