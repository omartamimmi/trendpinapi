import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import axios from 'axios';

// Icons
const EmailIcon = () => (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
    </svg>
);

const SmsIcon = () => (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
    </svg>
);

const WhatsAppIcon = () => (
    <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
);

const PushIcon = () => (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
    </svg>
);

const CheckIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
    </svg>
);

const XIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
    </svg>
);

const EyeIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
    </svg>
);

const EyeOffIcon = () => (
    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
    </svg>
);

const LoadingSpinner = () => (
    <svg className="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
);

// Types
interface ProviderCredentials {
    smtp: {
        host: string;
        port: string;
        username: string;
        password: string;
        encryption: 'tls' | 'ssl' | 'none';
        from_address: string;
        from_name: string;
    };
    sms: {
        provider: 'twilio' | 'nexmo' | 'messagebird' | 'none';
        account_sid: string;
        auth_token: string;
        from_number: string;
    };
    whatsapp: {
        provider: 'twilio' | 'meta' | 'none';
        account_sid: string;
        auth_token: string;
        from_number: string;
        business_id: string;
        access_token: string;
    };
    push: {
        provider: 'firebase' | 'none';
        project_id: string;
        server_key: string;
        service_account_json: string;
    };
}

interface TestResult {
    success: boolean;
    message: string;
    details?: string;
}

// Default credentials state
const defaultCredentials: ProviderCredentials = {
    smtp: {
        host: '',
        port: '587',
        username: '',
        password: '',
        encryption: 'tls',
        from_address: '',
        from_name: '',
    },
    sms: {
        provider: 'none',
        account_sid: '',
        auth_token: '',
        from_number: '',
    },
    whatsapp: {
        provider: 'none',
        account_sid: '',
        auth_token: '',
        from_number: '',
        business_id: '',
        access_token: '',
    },
    push: {
        provider: 'none',
        project_id: '',
        server_key: '',
        service_account_json: '',
    },
};

// Password Input Component
const PasswordInput = ({
    value,
    onChange,
    placeholder,
    className = '',
}: {
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    className?: string;
}) => {
    const [show, setShow] = useState(false);

    return (
        <div className="relative">
            <input
                type={show ? 'text' : 'password'}
                value={value}
                onChange={(e) => onChange(e.target.value)}
                placeholder={placeholder}
                className={`w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent ${className}`}
            />
            <button
                type="button"
                onClick={() => setShow(!show)}
                className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
                {show ? <EyeOffIcon /> : <EyeIcon />}
            </button>
        </div>
    );
};

// Credential Card Component
const CredentialCard = ({
    title,
    icon: Icon,
    children,
    status,
    onTest,
    onSave,
    isTesting,
    isSaving,
    testResult,
}: {
    title: string;
    icon: React.ComponentType;
    children: React.ReactNode;
    status: 'configured' | 'not_configured' | 'error';
    onTest: () => void;
    onSave: () => void;
    isTesting: boolean;
    isSaving: boolean;
    testResult: TestResult | null;
}) => {
    const statusColors = {
        configured: 'bg-green-100 text-green-800 border-green-200',
        not_configured: 'bg-gray-100 text-gray-800 border-gray-200',
        error: 'bg-red-100 text-red-800 border-red-200',
    };

    const statusLabels = {
        configured: 'Configured',
        not_configured: 'Not Configured',
        error: 'Error',
    };

    return (
        <div className="bg-white rounded-lg shadow overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-pink-100 text-pink-600 rounded-lg">
                        <Icon />
                    </div>
                    <h3 className="text-lg font-semibold text-gray-900">{title}</h3>
                </div>
                <span className={`px-3 py-1 text-xs font-medium rounded-full border ${statusColors[status]}`}>
                    {statusLabels[status]}
                </span>
            </div>
            <div className="p-6">
                {children}

                {/* Test Result */}
                {testResult && (
                    <div className={`mt-4 p-4 rounded-lg ${testResult.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}`}>
                        <div className="flex items-center gap-2">
                            {testResult.success ? (
                                <CheckIcon />
                            ) : (
                                <XIcon />
                            )}
                            <span className={`font-medium ${testResult.success ? 'text-green-800' : 'text-red-800'}`}>
                                {testResult.message}
                            </span>
                        </div>
                        {testResult.details && (
                            <p className={`mt-2 text-sm ${testResult.success ? 'text-green-700' : 'text-red-700'}`}>
                                {testResult.details}
                            </p>
                        )}
                    </div>
                )}

                {/* Actions */}
                <div className="mt-6 flex gap-3">
                    <button
                        onClick={onTest}
                        disabled={isTesting}
                        className="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {isTesting ? <LoadingSpinner /> : null}
                        Test Connection
                    </button>
                    <button
                        onClick={onSave}
                        disabled={isSaving}
                        className="flex items-center gap-2 px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {isSaving ? <LoadingSpinner /> : null}
                        Save Credentials
                    </button>
                </div>
            </div>
        </div>
    );
};

interface Props {
    credentials?: ProviderCredentials;
    statuses?: {
        smtp: 'configured' | 'not_configured' | 'error';
        sms: 'configured' | 'not_configured' | 'error';
        whatsapp: 'configured' | 'not_configured' | 'error';
        push: 'configured' | 'not_configured' | 'error';
    };
}

export default function NotificationCredentials({ credentials: initialCredentials, statuses: initialStatuses }: Props) {
    const [credentials, setCredentials] = useState<ProviderCredentials>(initialCredentials || defaultCredentials);
    const [statuses, setStatuses] = useState(initialStatuses || {
        smtp: 'not_configured' as const,
        sms: 'not_configured' as const,
        whatsapp: 'not_configured' as const,
        push: 'not_configured' as const,
    });
    const [testing, setTesting] = useState<string | null>(null);
    const [saving, setSaving] = useState<string | null>(null);
    const [testResults, setTestResults] = useState<Record<string, TestResult | null>>({});
    const [activeTab, setActiveTab] = useState<'credentials' | 'test'>('credentials');
    const [loading, setLoading] = useState(true);

    // Load credentials and statuses on mount
    useEffect(() => {
        const loadData = async () => {
            try {
                const [credentialsRes, statusesRes] = await Promise.all([
                    axios.get('/api/v1/admin/notification-credentials'),
                    axios.get('/api/v1/admin/notification-credentials/statuses'),
                ]);

                if (credentialsRes.data.success && credentialsRes.data.data) {
                    const loadedCredentials = { ...defaultCredentials };
                    credentialsRes.data.data.forEach((cred: any) => {
                        const channel = cred.channel as keyof ProviderCredentials;
                        if (loadedCredentials[channel]) {
                            loadedCredentials[channel] = { ...loadedCredentials[channel], ...cred.credentials, provider: cred.provider };
                        }
                    });
                    setCredentials(loadedCredentials);
                }

                if (statusesRes.data.success && statusesRes.data.data) {
                    const newStatuses: any = { smtp: 'not_configured', sms: 'not_configured', whatsapp: 'not_configured', push: 'not_configured' };
                    statusesRes.data.data.forEach((status: any) => {
                        newStatuses[status.channel] = status.is_configured ? 'configured' : 'not_configured';
                    });
                    setStatuses(newStatuses);
                }
            } catch (error) {
                console.error('Failed to load credentials:', error);
            } finally {
                setLoading(false);
            }
        };

        loadData();
    }, []);

    const updateCredential = <K extends keyof ProviderCredentials>(
        type: K,
        field: keyof ProviderCredentials[K],
        value: string
    ) => {
        setCredentials(prev => ({
            ...prev,
            [type]: {
                ...prev[type],
                [field]: value,
            },
        }));
    };

    const handleTest = async (type: keyof ProviderCredentials) => {
        setTesting(type);
        setTestResults(prev => ({ ...prev, [type]: null }));

        try {
            const channelName = type === 'smtp' ? 'smtp' : type;
            const response = await axios.post(`/api/v1/admin/notification-credentials/${channelName}/test`, credentials[type]);

            setTestResults(prev => ({
                ...prev,
                [type]: {
                    success: response.data.success,
                    message: response.data.message,
                    details: response.data.details,
                },
            }));

            if (response.data.success) {
                setStatuses(prev => ({ ...prev, [type]: 'configured' }));
            }
        } catch (error: any) {
            setTestResults(prev => ({
                ...prev,
                [type]: {
                    success: false,
                    message: 'Connection failed',
                    details: error.response?.data?.message || 'An error occurred while testing the connection.',
                },
            }));
        } finally {
            setTesting(null);
        }
    };

    const handleSave = async (type: keyof ProviderCredentials) => {
        setSaving(type);

        try {
            const channelName = type === 'smtp' ? 'smtp' : type;
            const response = await axios.post(`/api/v1/admin/notification-credentials/${channelName}`, credentials[type]);

            if (response.data.success) {
                setStatuses(prev => ({ ...prev, [type]: 'configured' }));
                alert(`${type.toUpperCase()} credentials saved successfully!`);
            } else {
                alert(response.data.message || 'Failed to save credentials.');
            }
        } catch (error: any) {
            const message = error.response?.data?.message || 'Failed to save credentials. Please try again.';
            alert(message);
        } finally {
            setSaving(null);
        }
    };

    return (
        <AdminLayout>
            <div className="max-w-5xl mx-auto">
                {/* Header */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">Notification Credentials</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        Configure your notification service providers and test connections
                    </p>
                </div>

                {/* Tabs */}
                <div className="bg-white rounded-lg shadow mb-6">
                    <div className="border-b border-gray-200">
                        <nav className="flex -mb-px">
                            <button
                                onClick={() => setActiveTab('credentials')}
                                className={`px-6 py-4 text-sm font-medium border-b-2 transition-colors ${
                                    activeTab === 'credentials'
                                        ? 'border-pink-500 text-pink-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                Service Credentials
                            </button>
                            <button
                                onClick={() => setActiveTab('test')}
                                className={`px-6 py-4 text-sm font-medium border-b-2 transition-colors ${
                                    activeTab === 'test'
                                        ? 'border-pink-500 text-pink-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                Test Notifications
                            </button>
                        </nav>
                    </div>
                </div>

                {activeTab === 'credentials' && (
                    <div className="space-y-6">
                        {/* SMTP / Email */}
                        <CredentialCard
                            title="Email (SMTP)"
                            icon={EmailIcon}
                            status={statuses.smtp}
                            onTest={() => handleTest('smtp')}
                            onSave={() => handleSave('smtp')}
                            isTesting={testing === 'smtp'}
                            isSaving={saving === 'smtp'}
                            testResult={testResults.smtp || null}
                        >
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">SMTP Host</label>
                                    <input
                                        type="text"
                                        value={credentials.smtp.host}
                                        onChange={(e) => updateCredential('smtp', 'host', e.target.value)}
                                        placeholder="smtp.gmail.com"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Port</label>
                                    <input
                                        type="text"
                                        value={credentials.smtp.port}
                                        onChange={(e) => updateCredential('smtp', 'port', e.target.value)}
                                        placeholder="587"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                    <input
                                        type="text"
                                        value={credentials.smtp.username}
                                        onChange={(e) => updateCredential('smtp', 'username', e.target.value)}
                                        placeholder="your-email@gmail.com"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                    <PasswordInput
                                        value={credentials.smtp.password}
                                        onChange={(value) => updateCredential('smtp', 'password', value)}
                                        placeholder="App password or SMTP password"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Encryption</label>
                                    <select
                                        value={credentials.smtp.encryption}
                                        onChange={(e) => updateCredential('smtp', 'encryption', e.target.value)}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    >
                                        <option value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">From Address</label>
                                    <input
                                        type="email"
                                        value={credentials.smtp.from_address}
                                        onChange={(e) => updateCredential('smtp', 'from_address', e.target.value)}
                                        placeholder="noreply@yourapp.com"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    />
                                </div>
                                <div className="col-span-2">
                                    <label className="block text-sm font-medium text-gray-700 mb-1">From Name</label>
                                    <input
                                        type="text"
                                        value={credentials.smtp.from_name}
                                        onChange={(e) => updateCredential('smtp', 'from_name', e.target.value)}
                                        placeholder="Your App Name"
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    />
                                </div>
                            </div>
                        </CredentialCard>

                        {/* SMS */}
                        <CredentialCard
                            title="SMS"
                            icon={SmsIcon}
                            status={statuses.sms}
                            onTest={() => handleTest('sms')}
                            onSave={() => handleSave('sms')}
                            isTesting={testing === 'sms'}
                            isSaving={saving === 'sms'}
                            testResult={testResults.sms || null}
                        >
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                                    <select
                                        value={credentials.sms.provider}
                                        onChange={(e) => updateCredential('sms', 'provider', e.target.value)}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    >
                                        <option value="none">Select Provider</option>
                                        <option value="twilio">Twilio</option>
                                        <option value="nexmo">Vonage (Nexmo)</option>
                                        <option value="messagebird">MessageBird</option>
                                    </select>
                                </div>

                                {credentials.sms.provider !== 'none' && (
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                {credentials.sms.provider === 'twilio' ? 'Account SID' : 'API Key'}
                                            </label>
                                            <input
                                                type="text"
                                                value={credentials.sms.account_sid}
                                                onChange={(e) => updateCredential('sms', 'account_sid', e.target.value)}
                                                placeholder={credentials.sms.provider === 'twilio' ? 'ACxxxxxxxx' : 'API Key'}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                {credentials.sms.provider === 'twilio' ? 'Auth Token' : 'API Secret'}
                                            </label>
                                            <PasswordInput
                                                value={credentials.sms.auth_token}
                                                onChange={(value) => updateCredential('sms', 'auth_token', value)}
                                                placeholder="Your auth token"
                                            />
                                        </div>
                                        <div className="col-span-2">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">From Number</label>
                                            <input
                                                type="text"
                                                value={credentials.sms.from_number}
                                                onChange={(e) => updateCredential('sms', 'from_number', e.target.value)}
                                                placeholder="+1234567890"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                            />
                                        </div>
                                    </div>
                                )}
                            </div>
                        </CredentialCard>

                        {/* WhatsApp */}
                        <CredentialCard
                            title="WhatsApp"
                            icon={WhatsAppIcon}
                            status={statuses.whatsapp}
                            onTest={() => handleTest('whatsapp')}
                            onSave={() => handleSave('whatsapp')}
                            isTesting={testing === 'whatsapp'}
                            isSaving={saving === 'whatsapp'}
                            testResult={testResults.whatsapp || null}
                        >
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                                    <select
                                        value={credentials.whatsapp.provider}
                                        onChange={(e) => updateCredential('whatsapp', 'provider', e.target.value)}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    >
                                        <option value="none">Select Provider</option>
                                        <option value="twilio">Twilio WhatsApp</option>
                                        <option value="meta">Meta WhatsApp Business API</option>
                                    </select>
                                </div>

                                {credentials.whatsapp.provider === 'twilio' && (
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Account SID</label>
                                            <input
                                                type="text"
                                                value={credentials.whatsapp.account_sid}
                                                onChange={(e) => updateCredential('whatsapp', 'account_sid', e.target.value)}
                                                placeholder="ACxxxxxxxx"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Auth Token</label>
                                            <PasswordInput
                                                value={credentials.whatsapp.auth_token}
                                                onChange={(value) => updateCredential('whatsapp', 'auth_token', value)}
                                                placeholder="Your auth token"
                                            />
                                        </div>
                                        <div className="col-span-2">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">WhatsApp Number</label>
                                            <input
                                                type="text"
                                                value={credentials.whatsapp.from_number}
                                                onChange={(e) => updateCredential('whatsapp', 'from_number', e.target.value)}
                                                placeholder="+14155238886"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                            />
                                        </div>
                                    </div>
                                )}

                                {credentials.whatsapp.provider === 'meta' && (
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Business ID</label>
                                            <input
                                                type="text"
                                                value={credentials.whatsapp.business_id}
                                                onChange={(e) => updateCredential('whatsapp', 'business_id', e.target.value)}
                                                placeholder="Your Business ID"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Phone Number ID</label>
                                            <input
                                                type="text"
                                                value={credentials.whatsapp.from_number}
                                                onChange={(e) => updateCredential('whatsapp', 'from_number', e.target.value)}
                                                placeholder="Phone Number ID"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                            />
                                        </div>
                                        <div className="col-span-2">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Access Token</label>
                                            <PasswordInput
                                                value={credentials.whatsapp.access_token}
                                                onChange={(value) => updateCredential('whatsapp', 'access_token', value)}
                                                placeholder="Your permanent access token"
                                            />
                                        </div>
                                    </div>
                                )}
                            </div>
                        </CredentialCard>

                        {/* Push Notifications */}
                        <CredentialCard
                            title="Push Notifications (Firebase)"
                            icon={PushIcon}
                            status={statuses.push}
                            onTest={() => handleTest('push')}
                            onSave={() => handleSave('push')}
                            isTesting={testing === 'push'}
                            isSaving={saving === 'push'}
                            testResult={testResults.push || null}
                        >
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                                    <select
                                        value={credentials.push.provider}
                                        onChange={(e) => updateCredential('push', 'provider', e.target.value)}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                    >
                                        <option value="none">Select Provider</option>
                                        <option value="firebase">Firebase Cloud Messaging (FCM)</option>
                                    </select>
                                </div>

                                {credentials.push.provider === 'firebase' && (
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Project ID</label>
                                            <input
                                                type="text"
                                                value={credentials.push.project_id}
                                                onChange={(e) => updateCredential('push', 'project_id', e.target.value)}
                                                placeholder="your-project-id"
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Server Key (Legacy)</label>
                                            <PasswordInput
                                                value={credentials.push.server_key}
                                                onChange={(value) => updateCredential('push', 'server_key', value)}
                                                placeholder="Your FCM server key"
                                            />
                                            <p className="mt-1 text-xs text-gray-500">Find this in Firebase Console &gt; Project Settings &gt; Cloud Messaging</p>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Service Account JSON</label>
                                            <textarea
                                                value={credentials.push.service_account_json}
                                                onChange={(e) => updateCredential('push', 'service_account_json', e.target.value)}
                                                placeholder='{"type": "service_account", ...}'
                                                rows={4}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent font-mono text-sm"
                                            />
                                            <p className="mt-1 text-xs text-gray-500">Paste the contents of your service account JSON file</p>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </CredentialCard>
                    </div>
                )}

                {activeTab === 'test' && (
                    <TestNotificationPanel />
                )}
            </div>
        </AdminLayout>
    );
}

// Test Notification Panel Component
function TestNotificationPanel() {
    const [selectedEvent, setSelectedEvent] = useState('');
    const [selectedChannel, setSelectedChannel] = useState<'email' | 'sms' | 'whatsapp' | 'push'>('email');
    const [selectedRecipientType, setSelectedRecipientType] = useState<'admin' | 'retailer' | 'customer'>('customer');
    const [selectedRecipient, setSelectedRecipient] = useState('');
    const [recipients, setRecipients] = useState<Array<{ id: string; name: string; email: string; phone?: string }>>([]);
    const [previewData, setPreviewData] = useState<Record<string, string>>({});
    const [sending, setSending] = useState(false);
    const [sendResult, setSendResult] = useState<{ success: boolean; message: string } | null>(null);

    // Mock events list
    const events = [
        { id: 'phone_verification', name: 'Phone Verification OTP', category: 'Authentication' },
        { id: 'new_customer', name: 'New Customer', category: 'Customer' },
        { id: 'nearby_shop', name: 'Nearby Shop', category: 'Customer' },
        { id: 'new_retailer', name: 'New Retailer', category: 'Retailer' },
        { id: 'retailer_approved', name: 'Retailer Approved', category: 'Retailer' },
        { id: 'retailer_rejected', name: 'Retailer Rejected', category: 'Retailer' },
        { id: 'retailer_changes_requested', name: 'Request Changes', category: 'Retailer' },
        { id: 'subscription_success', name: 'Success Subscription', category: 'Subscription' },
        { id: 'subscription_cancelled', name: 'Cancel Subscription', category: 'Subscription' },
        { id: 'subscription_expiring', name: 'Subscription Expiring', category: 'Subscription' },
        { id: 'branch_published', name: 'Published Branch', category: 'Branch' },
    ];

    // Mock placeholders by event
    const placeholdersByEvent: Record<string, string[]> = {
        phone_verification: ['otp_code', 'expiry_minutes', 'app_name'],
        new_customer: ['customer_name', 'customer_email', 'app_name', 'registration_date'],
        nearby_shop: ['customer_name', 'shop_name', 'distance', 'offer_count'],
        new_retailer: ['retailer_name', 'retailer_email', 'business_name', 'app_name', 'submission_date'],
        retailer_approved: ['retailer_name', 'app_name'],
        retailer_rejected: ['retailer_name', 'app_name', 'admin_message'],
        retailer_changes_requested: ['retailer_name', 'app_name', 'admin_message'],
        subscription_success: ['retailer_name', 'plan_name', 'amount', 'expiry_date', 'app_name'],
        subscription_cancelled: ['retailer_name', 'plan_name', 'end_date', 'app_name'],
        subscription_expiring: ['retailer_name', 'plan_name', 'expiry_date', 'days_left', 'app_name'],
        branch_published: ['retailer_name', 'branch_name', 'branch_address', 'app_name'],
    };

    // Load recipients when type changes
    useEffect(() => {
        const loadRecipients = async () => {
            try {
                const response = await axios.get(`/api/v1/admin/notification-test/recipients/${selectedRecipientType}`);
                if (response.data.success) {
                    setRecipients(response.data.data || []);
                }
            } catch (error) {
                console.error('Failed to load recipients:', error);
                setRecipients([]);
            }
        };

        loadRecipients();
        setSelectedRecipient('');
    }, [selectedRecipientType]);

    // Initialize preview data when event changes
    useEffect(() => {
        if (selectedEvent) {
            const placeholders = placeholdersByEvent[selectedEvent] || [];
            const initialData: Record<string, string> = {};
            placeholders.forEach(p => {
                // Set default test values
                switch (p) {
                    case 'otp_code':
                        initialData[p] = '123456';
                        break;
                    case 'expiry_minutes':
                        initialData[p] = '5';
                        break;
                    case 'customer_name':
                    case 'retailer_name':
                        initialData[p] = 'Test User';
                        break;
                    case 'app_name':
                        initialData[p] = 'TrendPin';
                        break;
                    case 'shop_name':
                    case 'branch_name':
                        initialData[p] = 'Test Shop';
                        break;
                    case 'distance':
                        initialData[p] = '500m';
                        break;
                    case 'offer_count':
                        initialData[p] = '5';
                        break;
                    case 'plan_name':
                        initialData[p] = 'Premium Plan';
                        break;
                    case 'amount':
                        initialData[p] = '$99.00';
                        break;
                    case 'days_left':
                        initialData[p] = '7';
                        break;
                    case 'admin_message':
                        initialData[p] = 'This is a test message from admin.';
                        break;
                    default:
                        initialData[p] = `[${p}]`;
                }
            });
            setPreviewData(initialData);
        }
    }, [selectedEvent]);

    const handleSendTest = async () => {
        if (!selectedEvent || !selectedRecipient) {
            alert('Please select an event and recipient');
            return;
        }

        setSending(true);
        setSendResult(null);

        try {
            const response = await axios.post('/api/v1/admin/notification-test/send', {
                channel: selectedChannel,
                recipient_type: selectedRecipientType,
                recipient_id: selectedRecipient,
                event_id: selectedEvent,
                placeholders: previewData,
            });

            setSendResult({
                success: response.data.success,
                message: response.data.message || `Test ${selectedChannel} notification sent successfully!`,
            });
        } catch (error: any) {
            setSendResult({
                success: false,
                message: error.response?.data?.message || 'Failed to send test notification. Please check your credentials.',
            });
        } finally {
            setSending(false);
        }
    };

    const currentPlaceholders = selectedEvent ? placeholdersByEvent[selectedEvent] || [] : [];

    return (
        <div className="bg-white rounded-lg shadow">
            <div className="px-6 py-4 border-b border-gray-200">
                <h3 className="text-lg font-semibold text-gray-900">Test Notifications</h3>
                <p className="text-sm text-gray-500 mt-1">Send test notifications to verify your templates and credentials</p>
            </div>

            <div className="p-6">
                <div className="grid grid-cols-2 gap-6">
                    {/* Left Column - Configuration */}
                    <div className="space-y-4">
                        {/* Event Selection */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Notification Event</label>
                            <select
                                value={selectedEvent}
                                onChange={(e) => setSelectedEvent(e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            >
                                <option value="">Select an event</option>
                                {events.map(event => (
                                    <option key={event.id} value={event.id}>
                                        {event.category} - {event.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Channel Selection */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Channel</label>
                            <div className="flex gap-2">
                                {(['email', 'sms', 'whatsapp', 'push'] as const).map(channel => (
                                    <button
                                        key={channel}
                                        onClick={() => setSelectedChannel(channel)}
                                        className={`flex-1 px-3 py-2 text-sm font-medium rounded-lg border transition-colors ${
                                            selectedChannel === channel
                                                ? 'bg-pink-500 text-white border-pink-500'
                                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                        }`}
                                    >
                                        {channel.toUpperCase()}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Recipient Type */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Recipient Type</label>
                            <div className="flex gap-2">
                                {(['admin', 'retailer', 'customer'] as const).map(type => (
                                    <button
                                        key={type}
                                        onClick={() => setSelectedRecipientType(type)}
                                        className={`flex-1 px-3 py-2 text-sm font-medium rounded-lg border transition-colors capitalize ${
                                            selectedRecipientType === type
                                                ? type === 'admin' ? 'bg-purple-500 text-white border-purple-500' :
                                                  type === 'retailer' ? 'bg-blue-500 text-white border-blue-500' :
                                                  'bg-green-500 text-white border-green-500'
                                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                        }`}
                                    >
                                        {type}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Recipient Selection */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Select Recipient</label>
                            <select
                                value={selectedRecipient}
                                onChange={(e) => setSelectedRecipient(e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            >
                                <option value="">Select a recipient</option>
                                {recipients.map(recipient => (
                                    <option key={recipient.id} value={recipient.id}>
                                        {recipient.name} ({selectedChannel === 'email' ? recipient.email : recipient.phone})
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Placeholder Values */}
                        {currentPlaceholders.length > 0 && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">Template Variables</label>
                                <div className="space-y-2 max-h-48 overflow-y-auto">
                                    {currentPlaceholders.map(placeholder => (
                                        <div key={placeholder} className="flex items-center gap-2">
                                            <code className="text-xs bg-gray-100 px-2 py-1 rounded text-pink-600 whitespace-nowrap">
                                                {`{{${placeholder}}}`}
                                            </code>
                                            <input
                                                type="text"
                                                value={previewData[placeholder] || ''}
                                                onChange={(e) => setPreviewData(prev => ({ ...prev, [placeholder]: e.target.value }))}
                                                className="flex-1 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                                            />
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Right Column - Preview */}
                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Message Preview</label>
                            <div className="border border-gray-200 rounded-lg overflow-hidden">
                                <div className="px-4 py-2 bg-gray-50 border-b border-gray-200 text-sm font-medium text-gray-700">
                                    {selectedChannel === 'email' ? 'Email Preview' :
                                     selectedChannel === 'sms' ? 'SMS Preview' :
                                     selectedChannel === 'whatsapp' ? 'WhatsApp Preview' : 'Push Notification Preview'}
                                </div>
                                <div className="p-4 bg-white min-h-[200px]">
                                    {selectedEvent ? (
                                        <div className="space-y-2">
                                            {selectedChannel === 'email' && (
                                                <>
                                                    <div className="text-sm">
                                                        <span className="font-medium text-gray-500">Subject: </span>
                                                        <span className="text-gray-900">Welcome to {previewData.app_name || 'TrendPin'}!</span>
                                                    </div>
                                                    <hr className="my-2" />
                                                </>
                                            )}
                                            {selectedChannel === 'push' && (
                                                <div className="bg-gray-100 rounded-lg p-3 shadow-sm">
                                                    <div className="font-medium text-gray-900 text-sm">
                                                        {previewData.app_name || 'TrendPin'}
                                                    </div>
                                                    <div className="text-gray-700 text-sm mt-1">
                                                        Test notification for {previewData.customer_name || previewData.retailer_name || 'User'}
                                                    </div>
                                                </div>
                                            )}
                                            <div className="text-sm text-gray-700 whitespace-pre-wrap">
                                                {selectedChannel === 'sms' ? (
                                                    `Hi ${previewData.customer_name || previewData.retailer_name || 'User'}, welcome to ${previewData.app_name || 'TrendPin'}!`
                                                ) : selectedChannel === 'whatsapp' ? (
                                                    `Hi ${previewData.customer_name || previewData.retailer_name || 'User'},\n\nWelcome to ${previewData.app_name || 'TrendPin'}! We're excited to have you.`
                                                ) : selectedChannel === 'push' ? null : (
                                                    `Hi ${previewData.customer_name || previewData.retailer_name || 'User'},\n\nWelcome to ${previewData.app_name || 'TrendPin'}! We're excited to have you on board.\n\nBest regards,\nThe ${previewData.app_name || 'TrendPin'} Team`
                                                )}
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="text-gray-400 text-center py-8">
                                            Select an event to preview the message
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Send Result */}
                        {sendResult && (
                            <div className={`p-4 rounded-lg ${sendResult.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}`}>
                                <div className="flex items-center gap-2">
                                    {sendResult.success ? <CheckIcon /> : <XIcon />}
                                    <span className={`font-medium ${sendResult.success ? 'text-green-800' : 'text-red-800'}`}>
                                        {sendResult.message}
                                    </span>
                                </div>
                            </div>
                        )}

                        {/* Send Button */}
                        <button
                            onClick={handleSendTest}
                            disabled={sending || !selectedEvent || !selectedRecipient}
                            className="w-full flex items-center justify-center gap-2 px-4 py-3 bg-pink-500 text-white rounded-lg hover:bg-pink-600 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                        >
                            {sending ? <LoadingSpinner /> : null}
                            {sending ? 'Sending...' : 'Send Test Notification'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
