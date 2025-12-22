import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import axios from 'axios';

interface Gateway {
    name: string;
    description: string;
    is_enabled: boolean;
    is_sandbox: boolean;
    has_credentials: boolean;
    supports: string[];
}

interface PaymentMethod {
    id: number;
    method: string;
    name: string;
    description: string;
    icon: string;
    is_enabled: boolean;
    supported_gateways: string[];
    display_order: number;
}

interface GeneralSettings {
    default_gateway: string;
    qr_expiry_minutes: number;
    require_subscription: boolean;
    enable_discounts: boolean;
}

interface Props {
    gateways: Record<string, Gateway>;
    methods: PaymentMethod[];
    generalSettings: GeneralSettings;
}

// Icons
const CreditCardIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
    </svg>
);

const SettingsIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
);

const KeyIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
    </svg>
);

const CheckCircleIcon = () => (
    <svg className="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
);

const XCircleIcon = () => (
    <svg className="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
);

// Toggle Switch Component
const ToggleSwitch = ({
    enabled,
    onChange,
    disabled = false,
}: {
    enabled: boolean;
    onChange: () => void;
    disabled?: boolean;
}) => (
    <button
        type="button"
        onClick={onChange}
        disabled={disabled}
        className={`relative inline-flex w-11 h-6 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 ${
            enabled ? 'bg-pink-500' : 'bg-gray-200'
        } ${disabled ? 'opacity-50 cursor-not-allowed' : ''}`}
    >
        <span
            className={`pointer-events-none inline-block w-5 h-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${
                enabled ? 'translate-x-5' : 'translate-x-0'
            }`}
        />
    </button>
);

// Gateway logos/icons
const getGatewayIcon = (gateway: string) => {
    switch (gateway) {
        case 'tap':
            return (
                <div className="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <span className="text-blue-600 font-bold text-sm">TAP</span>
                </div>
            );
        case 'hyperpay':
            return (
                <div className="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <span className="text-purple-600 font-bold text-xs">HP</span>
                </div>
            );
        case 'paytabs':
            return (
                <div className="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <span className="text-green-600 font-bold text-xs">PT</span>
                </div>
            );
        case 'cliq':
            return (
                <div className="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                    <span className="text-orange-600 font-bold text-xs">CLQ</span>
                </div>
            );
        default:
            return (
                <div className="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                    <CreditCardIcon />
                </div>
            );
    }
};

// Payment method icons
const getMethodIcon = (method: string) => {
    switch (method) {
        case 'card':
            return (
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            );
        case 'apple_pay':
            return (
                <svg className="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z" />
                </svg>
            );
        case 'google_pay':
            return (
                <svg className="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.545 10.239v3.821h5.445c-.712 2.315-2.647 3.972-5.445 3.972a6.033 6.033 0 110-12.064c1.498 0 2.866.549 3.921 1.453l2.814-2.814A9.969 9.969 0 0012.545 2C6.505 2 1.571 6.933 1.571 12s4.934 10 10.974 10c6.348 0 10.545-4.462 10.545-10.739 0-.729-.078-1.424-.209-2.022H12.545z" />
                </svg>
            );
        case 'cliq':
            return (
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                </svg>
            );
        default:
            return <CreditCardIcon />;
    }
};

export default function QrPaymentSettings({ gateways, methods, generalSettings }: Props) {
    const [activeTab, setActiveTab] = useState<'gateways' | 'methods' | 'general'>('gateways');
    const [editingGateway, setEditingGateway] = useState<string | null>(null);
    const [gatewayCredentials, setGatewayCredentials] = useState<Record<string, any>>({});
    const [saving, setSaving] = useState(false);
    const [testing, setTesting] = useState<string | null>(null);
    const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);
    const [generalForm, setGeneralForm] = useState(generalSettings);

    const handleToggleGateway = async (gateway: string) => {
        setSaving(true);
        try {
            router.put(`/admin/qr-payment/gateways/${gateway}`, {
                is_enabled: !gateways[gateway].is_enabled,
            }, {
                preserveState: true,
                onSuccess: () => {
                    setMessage({ type: 'success', text: `${gateways[gateway].name} ${gateways[gateway].is_enabled ? 'disabled' : 'enabled'} successfully` });
                },
                onError: () => {
                    setMessage({ type: 'error', text: 'Failed to update gateway' });
                },
            });
        } finally {
            setSaving(false);
        }
    };

    const handleSaveCredentials = async (gateway: string) => {
        setSaving(true);
        try {
            router.put(`/admin/qr-payment/gateways/${gateway}`, gatewayCredentials[gateway] || {}, {
                preserveState: true,
                onSuccess: () => {
                    setMessage({ type: 'success', text: 'Credentials saved successfully' });
                    setEditingGateway(null);
                },
                onError: () => {
                    setMessage({ type: 'error', text: 'Failed to save credentials' });
                },
            });
        } finally {
            setSaving(false);
        }
    };

    const handleTestGateway = async (gateway: string) => {
        setTesting(gateway);
        try {
            const response = await axios.post(`/admin/qr-payment/gateways/${gateway}/test`);
            setMessage({
                type: response.data.success ? 'success' : 'error',
                text: response.data.message,
            });
        } catch (error: any) {
            setMessage({
                type: 'error',
                text: error.response?.data?.message || 'Connection test failed',
            });
        } finally {
            setTesting(null);
        }
    };

    const handleToggleMethod = async (method: string) => {
        router.post(`/admin/qr-payment/methods/${method}/toggle`, {}, {
            preserveState: true,
            onSuccess: () => {
                setMessage({ type: 'success', text: 'Payment method updated' });
            },
        });
    };

    const handleSaveGeneral = async () => {
        setSaving(true);
        try {
            router.put('/admin/qr-payment/settings/general', generalForm, {
                preserveState: true,
                onSuccess: () => {
                    setMessage({ type: 'success', text: 'Settings saved successfully' });
                },
                onError: () => {
                    setMessage({ type: 'error', text: 'Failed to save settings' });
                },
            });
        } finally {
            setSaving(false);
        }
    };

    const updateCredential = (gateway: string, field: string, value: any) => {
        setGatewayCredentials(prev => ({
            ...prev,
            [gateway]: {
                ...prev[gateway],
                [field]: value,
            },
        }));
    };

    return (
        <AdminLayout>
            <div className="max-w-5xl mx-auto">
                {/* Header */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">QR Payment Settings</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        Configure payment gateways, methods, and system settings
                    </p>
                </div>

                {/* Message */}
                {message && (
                    <div
                        className={`mb-6 p-4 rounded-lg ${
                            message.type === 'success'
                                ? 'bg-green-50 text-green-800 border border-green-200'
                                : 'bg-red-50 text-red-800 border border-red-200'
                        }`}
                    >
                        {message.text}
                        <button
                            onClick={() => setMessage(null)}
                            className="float-right text-gray-400 hover:text-gray-600"
                        >
                            &times;
                        </button>
                    </div>
                )}

                {/* Tabs */}
                <div className="border-b border-gray-200 mb-6">
                    <nav className="flex space-x-8">
                        <button
                            onClick={() => setActiveTab('gateways')}
                            className={`py-4 px-1 border-b-2 font-medium text-sm ${
                                activeTab === 'gateways'
                                    ? 'border-pink-500 text-pink-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            }`}
                        >
                            <div className="flex items-center gap-2">
                                <KeyIcon />
                                Payment Gateways
                            </div>
                        </button>
                        <button
                            onClick={() => setActiveTab('methods')}
                            className={`py-4 px-1 border-b-2 font-medium text-sm ${
                                activeTab === 'methods'
                                    ? 'border-pink-500 text-pink-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            }`}
                        >
                            <div className="flex items-center gap-2">
                                <CreditCardIcon />
                                Payment Methods
                            </div>
                        </button>
                        <button
                            onClick={() => setActiveTab('general')}
                            className={`py-4 px-1 border-b-2 font-medium text-sm ${
                                activeTab === 'general'
                                    ? 'border-pink-500 text-pink-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            }`}
                        >
                            <div className="flex items-center gap-2">
                                <SettingsIcon />
                                General Settings
                            </div>
                        </button>
                    </nav>
                </div>

                {/* Tab Content */}
                <div className="space-y-6">
                    {/* Gateways Tab */}
                    {activeTab === 'gateways' && (
                        <div className="space-y-4">
                            {Object.entries(gateways).map(([key, gateway]) => (
                                <div
                                    key={key}
                                    className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                                >
                                    <div className="p-6">
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-start gap-4">
                                                {getGatewayIcon(key)}
                                                <div>
                                                    <h3 className="font-semibold text-gray-900">
                                                        {gateway.name}
                                                    </h3>
                                                    <p className="text-sm text-gray-500 mt-1">
                                                        {gateway.description}
                                                    </p>
                                                    <div className="flex items-center gap-4 mt-2">
                                                        <span className="flex items-center gap-1 text-xs">
                                                            {gateway.has_credentials ? (
                                                                <>
                                                                    <CheckCircleIcon />
                                                                    <span className="text-green-600">
                                                                        Credentials configured
                                                                    </span>
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <XCircleIcon />
                                                                    <span className="text-red-600">
                                                                        Credentials not set
                                                                    </span>
                                                                </>
                                                            )}
                                                        </span>
                                                        {gateway.is_sandbox && (
                                                            <span className="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded-full">
                                                                Sandbox Mode
                                                            </span>
                                                        )}
                                                    </div>
                                                    <div className="flex gap-2 mt-2">
                                                        {gateway.supports.map((method) => (
                                                            <span
                                                                key={method}
                                                                className="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded"
                                                            >
                                                                {method.replace('_', ' ')}
                                                            </span>
                                                        ))}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                <ToggleSwitch
                                                    enabled={gateway.is_enabled}
                                                    onChange={() => handleToggleGateway(key)}
                                                    disabled={saving}
                                                />
                                                <button
                                                    onClick={() =>
                                                        setEditingGateway(
                                                            editingGateway === key ? null : key
                                                        )
                                                    }
                                                    className="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded-lg hover:bg-gray-50"
                                                >
                                                    {editingGateway === key ? 'Cancel' : 'Configure'}
                                                </button>
                                            </div>
                                        </div>

                                        {/* Credentials Form */}
                                        {editingGateway === key && (
                                            <div className="mt-6 pt-6 border-t border-gray-200">
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    {key !== 'cliq' && (
                                                        <>
                                                            <div>
                                                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                                                    Public Key
                                                                </label>
                                                                <input
                                                                    type="text"
                                                                    placeholder="pk_live_..."
                                                                    value={
                                                                        gatewayCredentials[key]
                                                                            ?.public_key || ''
                                                                    }
                                                                    onChange={(e) =>
                                                                        updateCredential(
                                                                            key,
                                                                            'public_key',
                                                                            e.target.value
                                                                        )
                                                                    }
                                                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                                                />
                                                            </div>
                                                            <div>
                                                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                                                    Secret Key
                                                                </label>
                                                                <input
                                                                    type="password"
                                                                    placeholder="sk_live_..."
                                                                    value={
                                                                        gatewayCredentials[key]
                                                                            ?.secret_key || ''
                                                                    }
                                                                    onChange={(e) =>
                                                                        updateCredential(
                                                                            key,
                                                                            'secret_key',
                                                                            e.target.value
                                                                        )
                                                                    }
                                                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                                                />
                                                            </div>
                                                            <div>
                                                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                                                    Webhook Secret
                                                                </label>
                                                                <input
                                                                    type="password"
                                                                    placeholder="whsec_..."
                                                                    value={
                                                                        gatewayCredentials[key]
                                                                            ?.webhook_secret || ''
                                                                    }
                                                                    onChange={(e) =>
                                                                        updateCredential(
                                                                            key,
                                                                            'webhook_secret',
                                                                            e.target.value
                                                                        )
                                                                    }
                                                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                                                />
                                                            </div>
                                                        </>
                                                    )}
                                                    {key === 'cliq' && (
                                                        <>
                                                            <div>
                                                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                                                    Merchant ID
                                                                </label>
                                                                <input
                                                                    type="text"
                                                                    placeholder="Your CliQ Merchant ID"
                                                                    value={
                                                                        gatewayCredentials[key]
                                                                            ?.merchant_id || ''
                                                                    }
                                                                    onChange={(e) =>
                                                                        updateCredential(
                                                                            key,
                                                                            'merchant_id',
                                                                            e.target.value
                                                                        )
                                                                    }
                                                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                                                />
                                                            </div>
                                                            <div>
                                                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                                                    Merchant Alias
                                                                </label>
                                                                <input
                                                                    type="text"
                                                                    placeholder="TRENDPIN_MERCHANT"
                                                                    value={
                                                                        gatewayCredentials[key]
                                                                            ?.merchant_alias || ''
                                                                    }
                                                                    onChange={(e) =>
                                                                        updateCredential(
                                                                            key,
                                                                            'merchant_alias',
                                                                            e.target.value
                                                                        )
                                                                    }
                                                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                                                />
                                                            </div>
                                                        </>
                                                    )}
                                                    <div className="flex items-center gap-2">
                                                        <input
                                                            type="checkbox"
                                                            id={`sandbox-${key}`}
                                                            checked={
                                                                gatewayCredentials[key]?.is_sandbox ??
                                                                gateway.is_sandbox
                                                            }
                                                            onChange={(e) =>
                                                                updateCredential(
                                                                    key,
                                                                    'is_sandbox',
                                                                    e.target.checked
                                                                )
                                                            }
                                                            className="w-4 h-4 text-pink-500 border-gray-300 rounded focus:ring-pink-500"
                                                        />
                                                        <label
                                                            htmlFor={`sandbox-${key}`}
                                                            className="text-sm text-gray-700"
                                                        >
                                                            Sandbox/Test Mode
                                                        </label>
                                                    </div>
                                                </div>

                                                <div className="mt-4 p-4 bg-blue-50 rounded-lg">
                                                    <p className="text-sm text-blue-800">
                                                        <strong>Webhook URL:</strong>{' '}
                                                        <code className="bg-blue-100 px-2 py-1 rounded">
                                                            {window.location.origin}/webhooks/payment/
                                                            {key}
                                                        </code>
                                                    </p>
                                                </div>

                                                <div className="flex justify-end gap-3 mt-4">
                                                    <button
                                                        onClick={() => handleTestGateway(key)}
                                                        disabled={testing === key}
                                                        className="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                                                    >
                                                        {testing === key
                                                            ? 'Testing...'
                                                            : 'Test Connection'}
                                                    </button>
                                                    <button
                                                        onClick={() => handleSaveCredentials(key)}
                                                        disabled={saving}
                                                        className="px-4 py-2 text-sm text-white bg-pink-500 rounded-lg hover:bg-pink-600 disabled:opacity-50"
                                                    >
                                                        {saving ? 'Saving...' : 'Save Credentials'}
                                                    </button>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}

                    {/* Methods Tab */}
                    {activeTab === 'methods' && (
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <h2 className="font-semibold text-gray-900">
                                    Available Payment Methods
                                </h2>
                                <p className="text-sm text-gray-500 mt-1">
                                    Enable or disable payment methods for customers
                                </p>
                            </div>
                            <div className="divide-y divide-gray-200">
                                {methods.map((method) => (
                                    <div
                                        key={method.id}
                                        className="p-6 flex items-center justify-between"
                                    >
                                        <div className="flex items-center gap-4">
                                            <div
                                                className={`w-12 h-12 rounded-lg flex items-center justify-center ${
                                                    method.is_enabled
                                                        ? 'bg-pink-100 text-pink-600'
                                                        : 'bg-gray-100 text-gray-400'
                                                }`}
                                            >
                                                {getMethodIcon(method.method)}
                                            </div>
                                            <div>
                                                <h3 className="font-medium text-gray-900">
                                                    {method.name}
                                                </h3>
                                                <p className="text-sm text-gray-500">
                                                    {method.description}
                                                </p>
                                                <div className="flex gap-1 mt-1">
                                                    {method.supported_gateways?.map((gw) => (
                                                        <span
                                                            key={gw}
                                                            className="text-xs text-gray-400"
                                                        >
                                                            {gw}
                                                        </span>
                                                    ))}
                                                </div>
                                            </div>
                                        </div>
                                        <ToggleSwitch
                                            enabled={method.is_enabled}
                                            onChange={() => handleToggleMethod(method.method)}
                                        />
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* General Tab */}
                    {activeTab === 'general' && (
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div className="px-6 py-4 border-b border-gray-200">
                                <h2 className="font-semibold text-gray-900">General Settings</h2>
                                <p className="text-sm text-gray-500 mt-1">
                                    Configure general QR payment system settings
                                </p>
                            </div>
                            <div className="p-6 space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Default Payment Gateway
                                        </label>
                                        <select
                                            value={generalForm.default_gateway}
                                            onChange={(e) =>
                                                setGeneralForm({
                                                    ...generalForm,
                                                    default_gateway: e.target.value,
                                                })
                                            }
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                        >
                                            <option value="tap">Tap Payments</option>
                                            <option value="hyperpay">HyperPay</option>
                                            <option value="paytabs">PayTabs</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            QR Code Expiry (minutes)
                                        </label>
                                        <input
                                            type="number"
                                            min="5"
                                            max="60"
                                            value={generalForm.qr_expiry_minutes}
                                            onChange={(e) =>
                                                setGeneralForm({
                                                    ...generalForm,
                                                    qr_expiry_minutes: parseInt(e.target.value),
                                                })
                                            }
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                        />
                                        <p className="mt-1 text-xs text-gray-500">
                                            How long before a QR payment session expires
                                        </p>
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div>
                                            <p className="font-medium text-gray-900">
                                                Require Active Subscription
                                            </p>
                                            <p className="text-sm text-gray-500">
                                                Only allow payments from subscribed retailers
                                            </p>
                                        </div>
                                        <ToggleSwitch
                                            enabled={generalForm.require_subscription}
                                            onChange={() =>
                                                setGeneralForm({
                                                    ...generalForm,
                                                    require_subscription:
                                                        !generalForm.require_subscription,
                                                })
                                            }
                                        />
                                    </div>

                                    <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div>
                                            <p className="font-medium text-gray-900">
                                                Enable Bank Discounts
                                            </p>
                                            <p className="text-sm text-gray-500">
                                                Allow automatic discounts based on card BIN
                                            </p>
                                        </div>
                                        <ToggleSwitch
                                            enabled={generalForm.enable_discounts}
                                            onChange={() =>
                                                setGeneralForm({
                                                    ...generalForm,
                                                    enable_discounts: !generalForm.enable_discounts,
                                                })
                                            }
                                        />
                                    </div>
                                </div>

                                <div className="flex justify-end">
                                    <button
                                        onClick={handleSaveGeneral}
                                        disabled={saving}
                                        className="px-6 py-2 text-white bg-pink-500 rounded-lg hover:bg-pink-600 disabled:opacity-50"
                                    >
                                        {saving ? 'Saving...' : 'Save Settings'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
