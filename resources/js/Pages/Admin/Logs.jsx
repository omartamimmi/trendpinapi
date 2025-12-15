import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Components/Pagination';

export default function Logs({ logs, stats, filters: initialFilters, filterOptions }) {
    const [filters, setFilters] = useState({
        level: initialFilters?.level || '',
        channel: initialFilters?.channel || '',
        search: initialFilters?.search || '',
        from_date: initialFilters?.from_date || '',
        to_date: initialFilters?.to_date || '',
        has_exception: initialFilters?.has_exception || false,
    });
    const [selectedLog, setSelectedLog] = useState(null);
    const [period, setPeriod] = useState('today');

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

    const handleFilterChange = (key, value) => {
        setFilters(prev => ({ ...prev, [key]: value }));
    };

    const applyFilters = () => {
        const activeFilters = Object.fromEntries(
            Object.entries(filters).filter(([_, v]) => v !== '' && v !== false)
        );
        router.get('/admin/logs', activeFilters, { preserveState: true });
    };

    const clearFilters = () => {
        setFilters({
            level: '',
            channel: '',
            search: '',
            from_date: '',
            to_date: '',
            has_exception: false,
        });
        router.get('/admin/logs', {}, { preserveState: true });
    };

    const handlePeriodChange = (newPeriod) => {
        setPeriod(newPeriod);
        router.reload({ data: { stats_period: newPeriod }, only: ['stats'] });
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleString();
    };

    const truncateMessage = (message, maxLength = 80) => {
        if (!message) return '-';
        return message.length > maxLength ? message.substring(0, maxLength) + '...' : message;
    };

    return (
        <AdminLayout>
            <div className="px-4 py-6 sm:px-0">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-semibold text-gray-900">Activity Logs</h1>
                    <div className="flex gap-2">
                        <select
                            value={period}
                            onChange={(e) => handlePeriodChange(e.target.value)}
                            className="rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm"
                        >
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                    </div>
                </div>

                {/* Stats Summary */}
                {stats && (
                    <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
                        <div className="bg-white rounded-xl shadow-sm p-4">
                            <p className="text-sm text-gray-500">Total Logs</p>
                            <p className="text-2xl font-bold text-gray-900">{stats.summary?.total || 0}</p>
                        </div>
                        <div className="bg-white rounded-xl shadow-sm p-4">
                            <p className="text-sm text-gray-500">Errors</p>
                            <p className="text-2xl font-bold text-red-600">{stats.summary?.errors || 0}</p>
                        </div>
                        <div className="bg-white rounded-xl shadow-sm p-4">
                            <p className="text-sm text-gray-500">Warnings</p>
                            <p className="text-2xl font-bold text-yellow-600">{stats.summary?.warnings || 0}</p>
                        </div>
                        <div className="bg-white rounded-xl shadow-sm p-4">
                            <p className="text-sm text-gray-500">Info</p>
                            <p className="text-2xl font-bold text-green-600">{stats.summary?.info || 0}</p>
                        </div>
                        <div className="bg-white rounded-xl shadow-sm p-4">
                            <p className="text-sm text-gray-500">Unique Users</p>
                            <p className="text-2xl font-bold text-blue-600">{stats.summary?.unique_users || 0}</p>
                        </div>
                        <div className="bg-white rounded-xl shadow-sm p-4">
                            <p className="text-sm text-gray-500">Exceptions</p>
                            <p className="text-2xl font-bold text-orange-600">{stats.summary?.with_exceptions || 0}</p>
                        </div>
                    </div>
                )}

                {/* Filters */}
                <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
                    <div className="flex items-center gap-2 mb-4">
                        <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <h3 className="text-sm font-semibold text-gray-700">Filters</h3>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        {/* Search */}
                        <div className="lg:col-span-2">
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg className="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    placeholder="Search in messages..."
                                    value={filters.search}
                                    onChange={(e) => handleFilterChange('search', e.target.value)}
                                    className="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                                />
                            </div>
                        </div>

                        {/* Level */}
                        <div>
                            <select
                                value={filters.level}
                                onChange={(e) => handleFilterChange('level', e.target.value)}
                                className="w-full py-2.5 px-4 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all cursor-pointer"
                            >
                                <option value="">All Levels</option>
                                {filterOptions?.levels?.map(level => (
                                    <option key={level} value={level}>{level.charAt(0).toUpperCase() + level.slice(1)}</option>
                                ))}
                            </select>
                        </div>

                        {/* Channel */}
                        <div>
                            <select
                                value={filters.channel}
                                onChange={(e) => handleFilterChange('channel', e.target.value)}
                                className="w-full py-2.5 px-4 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all cursor-pointer"
                            >
                                <option value="">All Channels</option>
                                {filterOptions?.channels?.map(channel => (
                                    <option key={channel} value={channel}>{channel}</option>
                                ))}
                            </select>
                        </div>

                        {/* Date From */}
                        <div>
                            <label className="block text-xs font-medium text-gray-400 mb-1.5 ml-1">From Date</label>
                            <input
                                type="datetime-local"
                                value={filters.from_date}
                                onChange={(e) => handleFilterChange('from_date', e.target.value)}
                                className="w-full py-2 px-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                            />
                        </div>

                        {/* Date To */}
                        <div>
                            <label className="block text-xs font-medium text-gray-400 mb-1.5 ml-1">To Date</label>
                            <input
                                type="datetime-local"
                                value={filters.to_date}
                                onChange={(e) => handleFilterChange('to_date', e.target.value)}
                                className="w-full py-2 px-3 bg-gray-50 border-0 rounded-xl text-sm text-gray-700 focus:bg-white focus:ring-2 focus:ring-pink-500/20 transition-all"
                            />
                        </div>

                        {/* Exception Filter & Actions */}
                        <div className="lg:col-span-2 flex items-end justify-between gap-4">
                            <label className="flex items-center gap-3 cursor-pointer group">
                                <div className="relative">
                                    <input
                                        type="checkbox"
                                        checked={filters.has_exception}
                                        onChange={(e) => handleFilterChange('has_exception', e.target.checked)}
                                        className="sr-only peer"
                                    />
                                    <div className="w-10 h-6 bg-gray-200 rounded-full peer-checked:bg-pink-500 transition-colors"></div>
                                    <div className="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow-sm peer-checked:translate-x-4 transition-transform"></div>
                                </div>
                                <span className="text-sm text-gray-600 group-hover:text-gray-800 transition-colors">Errors Only</span>
                            </label>

                            <div className="flex gap-2">
                                <button
                                    onClick={clearFilters}
                                    className="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 hover:text-gray-800 transition-all"
                                >
                                    Clear All
                                </button>
                                <button
                                    onClick={applyFilters}
                                    className="px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-pink-500 to-pink-600 rounded-xl hover:from-pink-600 hover:to-pink-700 shadow-sm hover:shadow transition-all"
                                >
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Logs Table */}
                <div className="bg-white shadow overflow-hidden sm:rounded-xl">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                                    <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {logs?.data && logs.data.length > 0 ? logs.data.map((log) => (
                                    <tr
                                        key={log.id}
                                        className={`hover:bg-gray-50 cursor-pointer ${log.exception ? 'bg-red-50' : ''}`}
                                        onClick={() => setSelectedLog(log)}
                                    >
                                        <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {log.logged_at_human || formatDate(log.logged_at)}
                                        </td>
                                        <td className="px-4 py-3 whitespace-nowrap">
                                            <span className={`px-2 py-1 text-xs font-medium rounded-full ${levelColors[log.level] || 'bg-gray-500 text-white'}`}>
                                                {log.level?.toUpperCase()}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {log.channel}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-900 max-w-md">
                                            <div className="truncate" title={log.message}>
                                                {truncateMessage(log.message)}
                                            </div>
                                            {log.exception && (
                                                <div className="text-xs text-red-600 mt-1 truncate">
                                                    {log.exception.class}
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {log.user ? (
                                                <div>
                                                    <div className="font-medium text-gray-900">{log.user.name}</div>
                                                    <div className="text-xs text-gray-500">{log.user_type}</div>
                                                </div>
                                            ) : '-'}
                                        </td>
                                        <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {log.request?.ip_address || '-'}
                                        </td>
                                        <td className="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                            <button
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    router.visit(`/admin/logs/${log.id}`);
                                                }}
                                                className="text-pink-600 hover:text-pink-900"
                                            >
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                )) : (
                                    <tr>
                                        <td colSpan="7" className="px-6 py-8 text-center text-sm text-gray-500">
                                            No logs found
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    {logs?.links && Array.isArray(logs.links) && <Pagination data={logs} />}
                </div>

                {/* Quick View Modal */}
                {selectedLog && (
                    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" onClick={() => setSelectedLog(null)}>
                        <div className="bg-white rounded-xl p-6 w-full max-w-3xl max-h-[80vh] overflow-y-auto shadow-xl" onClick={(e) => e.stopPropagation()}>
                            <div className="flex justify-between items-start mb-4">
                                <div>
                                    <span className={`px-2 py-1 text-xs font-medium rounded-full ${levelColors[selectedLog.level] || 'bg-gray-500 text-white'}`}>
                                        {selectedLog.level?.toUpperCase()}
                                    </span>
                                    <span className="ml-2 text-sm text-gray-500">{selectedLog.channel}</span>
                                </div>
                                <button onClick={() => setSelectedLog(null)} className="text-gray-400 hover:text-gray-600">
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div className="space-y-4">
                                <div>
                                    <h4 className="text-sm font-medium text-gray-500">Message</h4>
                                    <p className="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-lg break-words">
                                        {selectedLog.message}
                                    </p>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <h4 className="text-sm font-medium text-gray-500">Time</h4>
                                        <p className="mt-1 text-sm text-gray-900">{formatDate(selectedLog.logged_at)}</p>
                                    </div>
                                    <div>
                                        <h4 className="text-sm font-medium text-gray-500">User</h4>
                                        <p className="mt-1 text-sm text-gray-900">
                                            {selectedLog.user ? `${selectedLog.user.name} (${selectedLog.user.email})` : 'Guest'}
                                        </p>
                                    </div>
                                </div>

                                {selectedLog.request && (
                                    <div>
                                        <h4 className="text-sm font-medium text-gray-500">Request</h4>
                                        <div className="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">
                                            <p><strong>Method:</strong> {selectedLog.request.method}</p>
                                            <p><strong>URL:</strong> {selectedLog.request.url}</p>
                                            <p><strong>IP:</strong> {selectedLog.request.ip_address}</p>
                                            {selectedLog.request.request_id && (
                                                <p><strong>Request ID:</strong> {selectedLog.request.request_id}</p>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {selectedLog.performance && (selectedLog.performance.duration_ms || selectedLog.performance.memory) && (
                                    <div>
                                        <h4 className="text-sm font-medium text-gray-500">Performance</h4>
                                        <div className="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">
                                            {selectedLog.performance.duration_ms && (
                                                <p><strong>Duration:</strong> {selectedLog.performance.duration_ms}ms</p>
                                            )}
                                            {selectedLog.performance.memory && (
                                                <p><strong>Memory:</strong> {selectedLog.performance.memory}</p>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {selectedLog.exception && (
                                    <div>
                                        <h4 className="text-sm font-medium text-red-500">Exception</h4>
                                        <div className="mt-1 text-sm text-gray-900 bg-red-50 p-3 rounded-lg">
                                            <p className="font-medium text-red-700">{selectedLog.exception.class}</p>
                                            <p className="mt-1">{selectedLog.exception.message}</p>
                                            <p className="text-xs text-gray-500 mt-1">
                                                {selectedLog.exception.file}:{selectedLog.exception.line}
                                            </p>
                                            {selectedLog.exception.trace && (
                                                <details className="mt-2">
                                                    <summary className="cursor-pointer text-xs text-gray-500 hover:text-gray-700">Show Stack Trace</summary>
                                                    <pre className="mt-2 text-xs bg-gray-800 text-gray-200 p-3 rounded overflow-x-auto max-h-60">
                                                        {selectedLog.exception.trace}
                                                    </pre>
                                                </details>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {selectedLog.context && Object.keys(selectedLog.context).length > 0 && (
                                    <div>
                                        <h4 className="text-sm font-medium text-gray-500">Context</h4>
                                        <pre className="mt-1 text-sm bg-gray-50 p-3 rounded-lg overflow-x-auto">
                                            {JSON.stringify(selectedLog.context, null, 2)}
                                        </pre>
                                    </div>
                                )}
                            </div>

                            <div className="mt-6 flex justify-end gap-2">
                                <button
                                    onClick={() => setSelectedLog(null)}
                                    className="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
                                >
                                    Close
                                </button>
                                <button
                                    onClick={() => router.visit(`/admin/logs/${selectedLog.id}`)}
                                    className="px-4 py-2 text-sm text-white bg-pink-600 rounded-md hover:bg-pink-700"
                                >
                                    View Full Details
                                </button>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
