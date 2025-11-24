import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function RetailerLayout({ children }) {
    const { auth } = usePage().props;
    const currentPath = usePage().url;
    const [offersOpen, setOffersOpen] = useState(false);

    const navigation = [
        {
            name: 'Dashboard',
            href: '/retailer/dashboard',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            )
        },
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
            name: 'Settings',
            href: '/retailer/settings',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            )
        }
    ];

    const isActive = (href) => currentPath.startsWith(href);

    return (
        <div className="min-h-screen flex">
            {/* Sidebar */}
            <div className="w-64 fixed h-full" style={{ backgroundColor: '#2D2A4A' }}>
                {/* Logo */}
                <div className="flex items-center px-6 py-6">
                    <svg className="w-8 h-8 mr-2" viewBox="0 0 40 40" fill="none">
                        <path d="M20 0C12.268 0 6 6.268 6 14c0 10.5 14 26 14 26s14-15.5 14-26c0-7.732-6.268-14-14-14zm0 19c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.761 5-5 5z" fill="#E91E8C"/>
                        <circle cx="20" cy="14" r="3" fill="white"/>
                    </svg>
                    <span className="text-white text-xl font-semibold">Trenpin</span>
                </div>

                {/* Navigation */}
                <nav className="mt-6 px-4">
                    {navigation.map((item) => (
                        <Link
                            key={item.name}
                            href={item.href}
                            className={`flex items-center px-4 py-3 mb-2 rounded-lg text-sm font-medium transition-colors ${
                                isActive(item.href)
                                    ? 'text-pink-500'
                                    : 'text-gray-300 hover:text-white'
                            }`}
                        >
                            <span className={isActive(item.href) ? 'text-pink-500' : ''}>{item.icon}</span>
                            <span className="ml-3">{item.name}</span>
                        </Link>
                    ))}

                    {/* Offers and Discounts Dropdown */}
                    <div>
                        <button
                            onClick={() => setOffersOpen(!offersOpen)}
                            className="flex items-center justify-between w-full px-4 py-3 mb-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white transition-colors"
                        >
                            <div className="flex items-center">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <span className="ml-3">Offers and Discounts</span>
                            </div>
                            <svg className={`w-4 h-4 transition-transform ${offersOpen ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        {offersOpen && (
                            <div className="ml-6 space-y-1">
                                <Link href="/retailer/offers" className="block px-4 py-2 text-sm text-gray-400 hover:text-white">
                                    My Offers
                                </Link>
                                <Link href="/retailer/offers/create" className="block px-4 py-2 text-sm text-gray-400 hover:text-white">
                                    Create Offer
                                </Link>
                            </div>
                        )}
                    </div>
                </nav>

                {/* User Info at Bottom */}
                <div className="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center">
                            <div className="w-8 h-8 rounded-full bg-pink-500 flex items-center justify-center text-white text-sm font-medium">
                                {auth?.user?.name?.charAt(0).toUpperCase()}
                            </div>
                            <span className="ml-3 text-white text-sm truncate">{auth?.user?.name}</span>
                        </div>
                        <Link
                            href="/retailer/logout"
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
                {/* Top Bar */}
                <header className="bg-white border-b border-gray-200 px-8 py-4">
                    <div className="flex items-center justify-end">
                        <div className="flex items-center space-x-4">
                            <button className="relative text-gray-400 hover:text-gray-600">
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span className="absolute top-0 right-0 w-2 h-2 bg-blue-500 rounded-full"></span>
                            </button>
                            <div className="flex items-center">
                                <div className="w-8 h-8 rounded-full bg-gray-300"></div>
                                <div className="ml-2">
                                    <p className="text-sm font-medium text-gray-900">{auth?.user?.name}</p>
                                    <p className="text-xs text-gray-500">Retailer</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <main className="p-8 bg-gray-50 min-h-screen">
                    {children}
                </main>
            </div>
        </div>
    );
}
