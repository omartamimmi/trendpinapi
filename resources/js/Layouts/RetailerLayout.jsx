import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

const scrollbarStyles = `
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }
`;

export default function RetailerLayout({ children }) {
    const { auth } = usePage().props;
    const currentPath = usePage().url;
    const [offersOpen, setOffersOpen] = useState(currentPath.includes('/retailer/offers'));

    const navigation = [
        // Main
        {
            name: 'Dashboard',
            href: '/retailer/dashboard',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            )
        },
        // Section divider
        { type: 'divider', label: 'Business' },
        {
            name: 'My Brands',
            href: '/retailer/brands',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            )
        },
        {
            name: 'Offers & Discounts',
            href: '/retailer/offers',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            ),
            submenu: [
                { name: 'All Offers', href: '/retailer/offers' },
                { name: 'Create Offer', href: '/retailer/offers/create' },
            ]
        },
        // Section divider
        { type: 'divider', label: 'Account' },
        {
            name: 'My Plan',
            href: '/retailer/plan',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
            )
        },
        {
            name: 'Billing',
            href: '/retailer/billing',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            )
        },
        {
            name: 'Settings',
            href: '/retailer/settings',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            )
        },
    ];

    const isActive = (href) => currentPath === href || (href !== '/retailer/dashboard' && currentPath.startsWith(href));

    return (
        <div className="min-h-screen flex">
            <style>{scrollbarStyles}</style>
            {/* Sidebar */}
            <div className="w-64 fixed h-full flex flex-col" style={{ backgroundColor: '#2D2A4A' }}>
                {/* Logo - Fixed at top */}
                <div className="flex items-center justify-center px-6 py-6 border-b border-white/10">
                    <img src="/images/logo.png" alt="Trendpin" className="h-8" />
                    <span className="text-white text-xl font-semibold ml-2">Trendpin</span>
                </div>

                {/* Navigation - Scrollable */}
                <nav className="flex-1 overflow-y-auto mt-2 px-4 py-2 custom-scrollbar">
                    {navigation.map((item, index) => {
                        // Render divider
                        if (item.type === 'divider') {
                            return (
                                <div key={index} className="mt-6 mb-3 px-4">
                                    <span className="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                        {item.label}
                                    </span>
                                </div>
                            );
                        }

                        // Render nav item with submenu
                        if (item.submenu) {
                            const isSubmenuActive = item.submenu.some(sub => isActive(sub.href));
                            return (
                                <div key={item.name}>
                                    <button
                                        onClick={() => setOffersOpen(!offersOpen)}
                                        className={`flex items-center justify-between w-full px-4 py-3 mb-1 rounded-lg text-sm font-medium transition-all ${
                                            isSubmenuActive
                                                ? 'bg-pink-500/20 text-white border-l-4 border-pink-500'
                                                : 'text-gray-300 hover:bg-white/5 hover:text-white'
                                        }`}
                                    >
                                        <div className="flex items-center">
                                            <span className={isSubmenuActive ? 'text-pink-400' : ''}>
                                                {item.icon}
                                            </span>
                                            <span className="ml-3">{item.name}</span>
                                        </div>
                                        <svg className={`w-4 h-4 transition-transform ${offersOpen ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    {offersOpen && (
                                        <div className="ml-4 mt-1 mb-2 space-y-1 border-l-2 border-gray-600">
                                            {item.submenu.map((sub) => (
                                                <Link
                                                    key={sub.name}
                                                    href={sub.href}
                                                    className={`block pl-6 pr-4 py-2 text-sm rounded-r-lg transition-all ${
                                                        isActive(sub.href)
                                                            ? 'text-pink-400 bg-pink-500/10 border-l-2 border-pink-500 -ml-[2px]'
                                                            : 'text-gray-400 hover:text-white hover:bg-white/5'
                                                    }`}
                                                >
                                                    {sub.name}
                                                </Link>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            );
                        }

                        // Render regular nav item
                        return (
                            <Link
                                key={item.name}
                                href={item.href}
                                className={`flex items-center px-4 py-3 mb-1 rounded-lg text-sm font-medium transition-all ${
                                    isActive(item.href)
                                        ? 'bg-pink-500/20 text-white border-l-4 border-pink-500'
                                        : 'text-gray-300 hover:bg-white/5 hover:text-white'
                                }`}
                            >
                                <span className={isActive(item.href) ? 'text-pink-400' : ''}>
                                    {item.icon}
                                </span>
                                <span className="ml-3">{item.name}</span>
                            </Link>
                        );
                    })}
                </nav>

                {/* User Info at Bottom - Fixed */}
                <div className="p-4 border-t border-white/10">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center">
                            <div className="w-8 h-8 rounded-full bg-pink-500 flex items-center justify-center text-white text-sm font-medium">
                                {auth?.user?.name?.charAt(0).toUpperCase()}
                            </div>
                            <span className="ml-3 text-white text-sm truncate">{auth?.user?.name}</span>
                        </div>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            className="text-gray-400 hover:text-white"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>

            {/* Main Content */}
            <div className="flex-1 ml-64">
                <main className="p-8 bg-gray-50 min-h-screen">
                    {children}
                </main>
            </div>
        </div>
    );
}
