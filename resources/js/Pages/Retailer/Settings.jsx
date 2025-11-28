import { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import RetailerLayout from '@/Layouts/RetailerLayout';

export default function Settings({ user, onboarding, subscription }) {
    const [activeTab, setActiveTab] = useState('profile');

    const profileForm = useForm({
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || '',
    });

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const handleProfileSubmit = (e) => {
        e.preventDefault();
        profileForm.put('/retailer/settings/profile');
    };

    const handlePasswordSubmit = (e) => {
        e.preventDefault();
        passwordForm.put('/retailer/settings/password', {
            onSuccess: () => {
                passwordForm.reset();
            },
        });
    };

    const tabs = [
        { id: 'profile', name: 'Profile' },
        { id: 'security', name: 'Security' },
        { id: 'subscription', name: 'Subscription' },
    ];

    return (
        <RetailerLayout>
            <div>
                <h1 className="text-2xl font-bold text-gray-900 mb-6">Settings</h1>

                {/* Tabs */}
                <div className="bg-white rounded-xl shadow-sm mb-6">
                    <div className="border-b border-gray-200">
                        <nav className="flex -mb-px">
                            {tabs.map((tab) => (
                                <button
                                    key={tab.id}
                                    onClick={() => setActiveTab(tab.id)}
                                    className={`px-6 py-4 text-sm font-medium border-b-2 ${
                                        activeTab === tab.id
                                            ? 'border-pink-500 text-pink-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                                >
                                    {tab.name}
                                </button>
                            ))}
                        </nav>
                    </div>

                    <div className="p-6">
                        {/* Profile Tab */}
                        {activeTab === 'profile' && (
                            <form onSubmit={handleProfileSubmit}>
                                <div className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Full Name
                                        </label>
                                        <input
                                            type="text"
                                            value={profileForm.data.name}
                                            onChange={(e) => profileForm.setData('name', e.target.value)}
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                        />
                                        {profileForm.errors.name && (
                                            <p className="text-red-500 text-sm mt-1">{profileForm.errors.name}</p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Email
                                        </label>
                                        <input
                                            type="email"
                                            value={profileForm.data.email}
                                            onChange={(e) => profileForm.setData('email', e.target.value)}
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                        />
                                        {profileForm.errors.email && (
                                            <p className="text-red-500 text-sm mt-1">{profileForm.errors.email}</p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Phone Number
                                        </label>
                                        <input
                                            type="text"
                                            value={profileForm.data.phone}
                                            onChange={(e) => profileForm.setData('phone', e.target.value)}
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                        />
                                        {profileForm.errors.phone && (
                                            <p className="text-red-500 text-sm mt-1">{profileForm.errors.phone}</p>
                                        )}
                                    </div>
                                    <div className="pt-4">
                                        <button
                                            type="submit"
                                            disabled={profileForm.processing}
                                            className="px-6 py-2 rounded-lg text-white font-medium disabled:opacity-50"
                                            style={{ backgroundColor: '#E91E8C' }}
                                        >
                                            {profileForm.processing ? 'Saving...' : 'Save Changes'}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        )}

                        {/* Security Tab */}
                        {activeTab === 'security' && (
                            <form onSubmit={handlePasswordSubmit}>
                                <div className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Current Password
                                        </label>
                                        <input
                                            type="password"
                                            value={passwordForm.data.current_password}
                                            onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                        />
                                        {passwordForm.errors.current_password && (
                                            <p className="text-red-500 text-sm mt-1">{passwordForm.errors.current_password}</p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            New Password
                                        </label>
                                        <input
                                            type="password"
                                            value={passwordForm.data.password}
                                            onChange={(e) => passwordForm.setData('password', e.target.value)}
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                        />
                                        {passwordForm.errors.password && (
                                            <p className="text-red-500 text-sm mt-1">{passwordForm.errors.password}</p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Confirm New Password
                                        </label>
                                        <input
                                            type="password"
                                            value={passwordForm.data.password_confirmation}
                                            onChange={(e) => passwordForm.setData('password_confirmation', e.target.value)}
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                        />
                                    </div>
                                    <div className="pt-4">
                                        <button
                                            type="submit"
                                            disabled={passwordForm.processing}
                                            className="px-6 py-2 rounded-lg text-white font-medium disabled:opacity-50"
                                            style={{ backgroundColor: '#E91E8C' }}
                                        >
                                            {passwordForm.processing ? 'Updating...' : 'Update Password'}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        )}

                        {/* Subscription Tab */}
                        {activeTab === 'subscription' && (
                            <div>
                                {subscription ? (
                                    <div className="space-y-4">
                                        <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                            <div>
                                                <h3 className="font-semibold text-gray-900">{subscription.plan?.name}</h3>
                                                <p className="text-sm text-gray-500">
                                                    {subscription.plan?.price} JOD / {subscription.plan?.billing_period}
                                                </p>
                                            </div>
                                            <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                                                subscription.status === 'active'
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-gray-100 text-gray-800'
                                            }`}>
                                                {subscription.status}
                                            </span>
                                        </div>
                                        <div className="grid grid-cols-2 gap-4">
                                            <div>
                                                <p className="text-sm text-gray-500">Started</p>
                                                <p className="font-medium">
                                                    {new Date(subscription.starts_at).toLocaleDateString()}
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-gray-500">Expires</p>
                                                <p className="font-medium">
                                                    {new Date(subscription.ends_at).toLocaleDateString()}
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-sm text-gray-500">Offers Allowed</p>
                                                <p className="font-medium">{subscription.plan?.offers_count || 'Unlimited'}</p>
                                            </div>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <p className="text-gray-500 mb-4">No active subscription</p>
                                        <button
                                            onClick={() => router.visit('/retailer/onboarding')}
                                            className="px-6 py-2 rounded-lg text-white font-medium"
                                            style={{ backgroundColor: '#E91E8C' }}
                                        >
                                            Subscribe Now
                                        </button>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </div>

                {/* Onboarding Status */}
                {onboarding && (
                    <div className="bg-white rounded-xl shadow-sm p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">Onboarding Status</h2>
                        <div className="flex items-center space-x-3">
                            <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                                onboarding.approval_status === 'approved'
                                    ? 'bg-green-100 text-green-800'
                                    : onboarding.approval_status === 'pending_approval'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : onboarding.approval_status === 'changes_requested'
                                    ? 'bg-orange-100 text-orange-800'
                                    : onboarding.approval_status === 'rejected'
                                    ? 'bg-red-100 text-red-800'
                                    : 'bg-gray-100 text-gray-800'
                            }`}>
                                {onboarding.approval_status === 'pending_approval' ? 'Pending Approval' :
                                 onboarding.approval_status === 'changes_requested' ? 'Changes Requested' :
                                 onboarding.approval_status?.charAt(0).toUpperCase() + onboarding.approval_status?.slice(1)}
                            </span>
                        </div>
                        {onboarding.admin_notes && (
                            <div className="mt-4 p-3 bg-gray-50 rounded-lg">
                                <p className="text-sm text-gray-600">{onboarding.admin_notes}</p>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </RetailerLayout>
    );
}
