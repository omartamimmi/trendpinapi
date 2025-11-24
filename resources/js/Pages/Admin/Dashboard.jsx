import AdminLayout from '@/Layouts/AdminLayout';

export default function Dashboard({ stats }) {
    const statCards = [
        {
            name: 'Total Users',
            value: stats?.users?.total || 0,
            subLabel: 'This month',
            subValue: stats?.users?.this_month || 0,
            icon: (
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            ),
            color: 'pink'
        },
        {
            name: 'Active Subscriptions',
            value: stats?.subscriptions?.active || 0,
            subLabel: 'Pending',
            subValue: stats?.subscriptions?.pending || 0,
            icon: (
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            ),
            color: 'purple'
        },
        {
            name: 'Total Revenue',
            value: `${stats?.payments?.total_revenue || 0} JD`,
            subLabel: 'This month',
            subValue: `${stats?.payments?.this_month_revenue || 0} JD`,
            icon: (
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            ),
            color: 'green'
        },
        {
            name: 'Active Plans',
            value: stats?.plans?.active || 0,
            subLabel: 'Total',
            subValue: stats?.plans?.total || 0,
            icon: (
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            ),
            color: 'blue'
        }
    ];

    const getIconBgColor = (color) => {
        const colors = {
            pink: 'bg-pink-100',
            purple: 'bg-purple-100',
            green: 'bg-green-100',
            blue: 'bg-blue-100'
        };
        return colors[color] || 'bg-gray-100';
    };

    const getIconColor = (color) => {
        const colors = {
            pink: 'text-pink-600',
            purple: 'text-purple-600',
            green: 'text-green-600',
            blue: 'text-blue-600'
        };
        return colors[color] || 'text-gray-600';
    };

    return (
        <AdminLayout>
            <div>
                <h1 className="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    {statCards.map((card) => (
                        <div key={card.name} className="bg-white rounded-2xl shadow-sm p-6">
                            <div className="flex items-center justify-between mb-4">
                                <div className={`p-3 rounded-xl ${getIconBgColor(card.color)}`}>
                                    <span className={getIconColor(card.color)}>{card.icon}</span>
                                </div>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-gray-500 mb-1">{card.name}</p>
                                <p className="text-2xl font-bold text-gray-900">{card.value}</p>
                            </div>
                            <div className="mt-3 pt-3 border-t border-gray-100">
                                <span className="text-sm text-gray-500">{card.subLabel}: </span>
                                <span className="text-sm font-semibold" style={{ color: '#E91E8C' }}>{card.subValue}</span>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Onboardings Section */}
                <div>
                    <h2 className="text-lg font-semibold text-gray-900 mb-4">Onboarding Status</h2>
                    <div className="bg-white rounded-2xl shadow-sm p-6">
                        <div className="grid grid-cols-3 gap-6">
                            <div className="text-center p-4 rounded-xl bg-gray-50">
                                <p className="text-3xl font-bold text-gray-900 mb-1">{stats?.onboardings?.total || 0}</p>
                                <p className="text-sm font-medium text-gray-500">Total</p>
                            </div>
                            <div className="text-center p-4 rounded-xl bg-yellow-50">
                                <p className="text-3xl font-bold text-yellow-600 mb-1">{stats?.onboardings?.in_progress || 0}</p>
                                <p className="text-sm font-medium text-gray-500">In Progress</p>
                            </div>
                            <div className="text-center p-4 rounded-xl bg-green-50">
                                <p className="text-3xl font-bold text-green-600 mb-1">{stats?.onboardings?.completed || 0}</p>
                                <p className="text-sm font-medium text-gray-500">Completed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
