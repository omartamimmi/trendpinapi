import RetailerLayout from '@/Layouts/RetailerLayout';

export default function Dashboard({ stats }) {
    const statCards = [
        {
            name: 'Total Offers',
            value: stats?.offers?.total || 0,
            subLabel: 'Active',
            subValue: stats?.offers?.active || 0,
            icon: (
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            ),
            color: 'pink'
        },
        {
            name: 'Total Claims',
            value: stats?.claims?.total || 0,
            subLabel: 'This month',
            subValue: stats?.claims?.this_month || 0,
            icon: (
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            ),
            color: 'green'
        },
        {
            name: 'Total Views',
            value: stats?.views?.total || 0,
            subLabel: 'This week',
            subValue: stats?.views?.this_week || 0,
            icon: (
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            ),
            color: 'blue'
        },
        {
            name: 'Subscription',
            value: stats?.subscription?.status || 'Active',
            subLabel: 'Expires',
            subValue: stats?.subscription?.expires_at || 'N/A',
            icon: (
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
            ),
            color: 'purple'
        }
    ];

    const getIconBgColor = (color) => {
        const colors = {
            pink: 'bg-pink-100',
            green: 'bg-green-100',
            blue: 'bg-blue-100',
            purple: 'bg-purple-100'
        };
        return colors[color] || 'bg-gray-100';
    };

    const getIconColor = (color) => {
        const colors = {
            pink: 'text-pink-600',
            green: 'text-green-600',
            blue: 'text-blue-600',
            purple: 'text-purple-600'
        };
        return colors[color] || 'text-gray-600';
    };

    return (
        <RetailerLayout>
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

                {/* Recent Offers */}
                <div className="bg-white rounded-2xl shadow-sm p-6">
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-lg font-semibold text-gray-900">Recent Offers</h2>
                        <a href="/retailer/offers" className="text-sm font-medium" style={{ color: '#E91E8C' }}>
                            View All
                        </a>
                    </div>

                    {stats?.recent_offers?.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="text-left text-sm text-gray-500">
                                        <th className="pb-3 font-medium">Offer Name</th>
                                        <th className="pb-3 font-medium">Type</th>
                                        <th className="pb-3 font-medium">Claims</th>
                                        <th className="pb-3 font-medium">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="text-sm">
                                    {stats.recent_offers.map((offer, index) => (
                                        <tr key={index} className="border-t border-gray-100">
                                            <td className="py-3 font-medium text-gray-900">{offer.name}</td>
                                            <td className="py-3 text-gray-500">{offer.type}</td>
                                            <td className="py-3 text-gray-500">{offer.claims}</td>
                                            <td className="py-3">
                                                <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                                    offer.status === 'active'
                                                        ? 'bg-green-100 text-green-600'
                                                        : 'bg-gray-100 text-gray-600'
                                                }`}>
                                                    {offer.status}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="text-center py-8">
                            <svg className="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <p className="text-gray-500 mb-4">No offers yet</p>
                            <a
                                href="/retailer/offers/create"
                                className="inline-flex items-center px-4 py-2 rounded-full text-white text-sm font-medium"
                                style={{ backgroundColor: '#E91E8C' }}
                            >
                                Create Your First Offer
                            </a>
                        </div>
                    )}
                </div>
            </div>
        </RetailerLayout>
    );
}
