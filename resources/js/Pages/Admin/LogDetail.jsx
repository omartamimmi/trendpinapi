import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function LogDetail({ log, relatedLogs }) {
    const levelColors = {
        emergency: 'bg-red-600 text-white',
        alert: 'bg-red-600 text-white',
        critical: 'bg-red-600 text-white',
        error: 'bg-orange-500 text-white',
        warning: 'bg-yellow-500 text-white',
        notice: 'bg-blue-500 text-white',
        info: 'bg-green-500 text-white',
        debug: 'bg-gray-500 text-white',
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleString();
    };

    return (
        <AdminLayout>
            <div className="px-4 py-6 sm:px-0">
                {/* Header */}
                <div className="flex items-center justify-between mb-6">
                    <div className="flex items-center gap-4">
                        <Link
                            href="/admin/logs"
                            className="text-gray-500 hover:text-gray-700"
                        >
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </Link>
                        <h1 className="text-2xl font-semibold text-gray-900">Log Details</h1>
                    </div>
                    <span className={`px-3 py-1 text-sm font-medium rounded-full ${levelColors[log?.level] || 'bg-gray-500 text-white'}`}>
                        {log?.level?.toUpperCase()}
                    </span>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Message */}
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <h2 className="text-lg font-medium text-gray-900 mb-4">Message</h2>
                            <p className="text-gray-700 bg-gray-50 p-4 rounded-lg break-words whitespace-pre-wrap">
                                {log?.message}
                            </p>
                        </div>

                        {/* Exception Details */}
                        {log?.exception && (
                            <div className="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                                <h2 className="text-lg font-medium text-red-600 mb-4">Exception</h2>
                                <div className="space-y-4">
                                    <div>
                                        <h4 className="text-sm font-medium text-gray-500">Class</h4>
                                        <p className="mt-1 text-sm font-mono text-red-700">{log.exception.class}</p>
                                    </div>
                                    <div>
                                        <h4 className="text-sm font-medium text-gray-500">Message</h4>
                                        <p className="mt-1 text-sm text-gray-900">{log.exception.message}</p>
                                    </div>
                                    <div>
                                        <h4 className="text-sm font-medium text-gray-500">File</h4>
                                        <p className="mt-1 text-sm font-mono text-gray-700">
                                            {log.exception.file}:{log.exception.line}
                                        </p>
                                    </div>
                                    {log.exception.trace && (
                                        <div>
                                            <h4 className="text-sm font-medium text-gray-500 mb-2">Stack Trace</h4>
                                            <pre className="text-xs bg-gray-800 text-gray-200 p-4 rounded-lg overflow-x-auto max-h-96">
                                                {log.exception.trace}
                                            </pre>
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}

                        {/* Context */}
                        {log?.context && Object.keys(log.context).length > 0 && (
                            <div className="bg-white rounded-xl shadow-sm p-6">
                                <h2 className="text-lg font-medium text-gray-900 mb-4">Context Data</h2>
                                <pre className="text-sm bg-gray-50 p-4 rounded-lg overflow-x-auto">
                                    {JSON.stringify(log.context, null, 2)}
                                </pre>
                            </div>
                        )}

                        {/* Extra Data */}
                        {log?.extra && Object.keys(log.extra).length > 0 && (
                            <div className="bg-white rounded-xl shadow-sm p-6">
                                <h2 className="text-lg font-medium text-gray-900 mb-4">Extra Data</h2>
                                <pre className="text-sm bg-gray-50 p-4 rounded-lg overflow-x-auto">
                                    {JSON.stringify(log.extra, null, 2)}
                                </pre>
                            </div>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Metadata */}
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <h2 className="text-lg font-medium text-gray-900 mb-4">Details</h2>
                            <dl className="space-y-4">
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">Log ID</dt>
                                    <dd className="mt-1 text-sm text-gray-900">#{log?.id}</dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">Channel</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{log?.channel}</dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">Logged At</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{formatDate(log?.logged_at)}</dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">Time Ago</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{log?.logged_at_human}</dd>
                                </div>
                            </dl>
                        </div>

                        {/* User Info */}
                        <div className="bg-white rounded-xl shadow-sm p-6">
                            <h2 className="text-lg font-medium text-gray-900 mb-4">User</h2>
                            {log?.user ? (
                                <dl className="space-y-4">
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500">Name</dt>
                                        <dd className="mt-1 text-sm text-gray-900">{log.user.name}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500">Email</dt>
                                        <dd className="mt-1 text-sm text-gray-900">{log.user.email}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500">Type</dt>
                                        <dd className="mt-1">
                                            <span className="px-2 py-1 text-xs font-medium rounded-full bg-pink-100 text-pink-700">
                                                {log.user_type}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <Link
                                            href={`/admin/logs?user_id=${log.user.id}`}
                                            className="text-sm text-pink-600 hover:text-pink-700"
                                        >
                                            View all logs from this user
                                        </Link>
                                    </div>
                                </dl>
                            ) : (
                                <p className="text-sm text-gray-500">Guest / Unauthenticated</p>
                            )}
                        </div>

                        {/* Request Info */}
                        {log?.request && (
                            <div className="bg-white rounded-xl shadow-sm p-6">
                                <h2 className="text-lg font-medium text-gray-900 mb-4">Request</h2>
                                <dl className="space-y-4">
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500">Method</dt>
                                        <dd className="mt-1">
                                            <span className={`px-2 py-1 text-xs font-medium rounded ${
                                                log.request.method === 'GET' ? 'bg-green-100 text-green-700' :
                                                log.request.method === 'POST' ? 'bg-blue-100 text-blue-700' :
                                                log.request.method === 'PUT' ? 'bg-yellow-100 text-yellow-700' :
                                                log.request.method === 'DELETE' ? 'bg-red-100 text-red-700' :
                                                'bg-gray-100 text-gray-700'
                                            }`}>
                                                {log.request.method}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500">URL</dt>
                                        <dd className="mt-1 text-sm text-gray-900 break-all">{log.request.url}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500">IP Address</dt>
                                        <dd className="mt-1 text-sm text-gray-900">
                                            {log.request.ip_address}
                                            <Link
                                                href={`/admin/logs?ip_address=${log.request.ip_address}`}
                                                className="ml-2 text-pink-600 hover:text-pink-700 text-xs"
                                            >
                                                (view all)
                                            </Link>
                                        </dd>
                                    </div>
                                    {log.request.request_id && (
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Request ID</dt>
                                            <dd className="mt-1 text-sm font-mono text-gray-900 break-all">
                                                {log.request.request_id}
                                            </dd>
                                        </div>
                                    )}
                                    {log.request.user_agent && (
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">User Agent</dt>
                                            <dd className="mt-1 text-xs text-gray-500 break-all">
                                                {log.request.user_agent}
                                            </dd>
                                        </div>
                                    )}
                                </dl>
                            </div>
                        )}

                        {/* Performance */}
                        {log?.performance && (log.performance.duration_ms || log.performance.memory) && (
                            <div className="bg-white rounded-xl shadow-sm p-6">
                                <h2 className="text-lg font-medium text-gray-900 mb-4">Performance</h2>
                                <dl className="space-y-4">
                                    {log.performance.duration_ms && (
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Duration</dt>
                                            <dd className="mt-1 text-sm text-gray-900">
                                                {log.performance.duration_ms}ms
                                            </dd>
                                        </div>
                                    )}
                                    {log.performance.memory && (
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Memory Usage</dt>
                                            <dd className="mt-1 text-sm text-gray-900">{log.performance.memory}</dd>
                                        </div>
                                    )}
                                </dl>
                            </div>
                        )}
                    </div>
                </div>

                {/* Related Logs (same request) */}
                {relatedLogs && relatedLogs.length > 1 && (
                    <div className="mt-6 bg-white rounded-xl shadow-sm p-6">
                        <h2 className="text-lg font-medium text-gray-900 mb-4">
                            Related Logs (Same Request)
                        </h2>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {relatedLogs.map((relatedLog) => (
                                        <tr
                                            key={relatedLog.id}
                                            className={`hover:bg-gray-50 ${relatedLog.id === log.id ? 'bg-pink-50' : ''}`}
                                        >
                                            <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                {formatDate(relatedLog.logged_at)}
                                            </td>
                                            <td className="px-4 py-3 whitespace-nowrap">
                                                <span className={`px-2 py-1 text-xs font-medium rounded-full ${levelColors[relatedLog.level] || 'bg-gray-500 text-white'}`}>
                                                    {relatedLog.level?.toUpperCase()}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {relatedLog.id === log.id ? (
                                                    <span className="font-medium">{relatedLog.message?.substring(0, 100)}...</span>
                                                ) : (
                                                    <Link
                                                        href={`/admin/logs/${relatedLog.id}`}
                                                        className="hover:text-pink-600"
                                                    >
                                                        {relatedLog.message?.substring(0, 100)}...
                                                    </Link>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
