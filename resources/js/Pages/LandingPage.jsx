import { Link } from '@inertiajs/react';
import { useState } from 'react';

export default function LandingPage() {
    const [activeCategory, setActiveCategory] = useState('restaurants');

    const features = [
        {
            title: 'Browse Your Restaurants & Claim Offers Directly',
            description: 'Fast and Easy browsing between the stores and restaurants',
            icon: (
                <div className="w-12 h-12 rounded-xl bg-pink-100 flex items-center justify-center">
                    <svg className="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            )
        },
        {
            title: 'Browse Nearest Offers Around Your Location',
            description: 'Select your location to explore nearby offers and discover your offers',
            icon: (
                <div className="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg className="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            )
        },
        {
            title: 'Save More & Enjoy Buy 1 Get 1 Free',
            description: 'Choose your items to explore exclusive discounts available at your favorite store',
            icon: (
                <div className="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                    <svg className="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            )
        },
        {
            title: 'Use Your Credit Card Bank And Get 10% Discounts',
            description: 'Unlock exclusive bank partner discounts on the offers available to you',
            icon: (
                <div className="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center">
                    <svg className="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            )
        }
    ];

    const offers = [
        {
            name: 'Hamada',
            image: '/images/landing/food-image.png',
            badge: 'Buy 1 Get 1',
            discount: '10% off'
        },
        {
            name: 'Al Mousalli',
            image: '/images/landing/food-image.png',
            badge: 'Buy 1 Get 1',
            discount: '10% off'
        }
    ];

    const banks = [
        { name: 'NBK', logo: '/images/landing/nbk.png' },
        { name: 'Cairo Amman Bank', logo: '/images/landing/cairo-amman.png' },
        { name: 'Capital Bank', logo: '/images/landing/capital-bank.png' },
        { name: 'Housing Bank', logo: '/images/landing/housing-bank.png' },
        { name: 'Kuwait Finance', logo: '/images/landing/kuwait-finance.png' },
    ];

    return (
        <div className="min-h-screen bg-white">
            {/* Header */}
            <header className="py-4 px-6 lg:px-12">
                <div className="max-w-7xl mx-auto flex items-center justify-between">
                    <div className="flex items-center">
                        <svg className="w-8 h-8 mr-2" viewBox="0 0 40 40" fill="none">
                            <path d="M20 0C12.268 0 6 6.268 6 14c0 10.5 14 26 14 26s14-15.5 14-26c0-7.732-6.268-14-14-14zm0 19c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.761 5-5 5z" fill="#E91E8C"/>
                            <circle cx="20" cy="14" r="3" fill="white"/>
                        </svg>
                        <span className="text-xl font-semibold text-gray-900">Trendpin</span>
                    </div>
                    <Link
                        href="/retailer/login"
                        className="px-6 py-2.5 rounded-full text-sm font-medium text-white"
                        style={{ backgroundColor: '#E91E8C' }}
                    >
                        Retailer portal
                    </Link>
                </div>
            </header>

            {/* Hero Section */}
            <section className="py-12 px-6 lg:px-12">
                <div className="max-w-7xl mx-auto">
                    <div className="grid lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <h1 className="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                                Claim Best Offer<br />
                                on Fast <span style={{ color: '#E91E8C' }}>Food</span> &<br />
                                <span style={{ color: '#E91E8C' }}>Restaurants</span>
                            </h1>
                            <p className="text-gray-600 mb-6">
                                Our job is to filling your tummy with delicious food<br />
                                and with fast and free delivery
                            </p>
                            <button
                                className="px-6 py-3 rounded-full text-white font-medium"
                                style={{ backgroundColor: '#E91E8C' }}
                            >
                                Download App
                            </button>

                            {/* Customer Reviews */}
                            <div className="flex items-center mt-8">
                                <div className="flex -space-x-2">
                                    {[1, 2, 3, 4].map((i) => (
                                        <div key={i} className="w-8 h-8 rounded-full bg-gray-300 border-2 border-white" />
                                    ))}
                                </div>
                                <div className="ml-3">
                                    <p className="text-sm font-medium text-gray-900">Our Happy Customer</p>
                                    <div className="flex items-center">
                                        <svg className="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <span className="text-sm text-gray-600 ml-1">4.8 (12.5k Review)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="relative">
                            {/* Hero Image with phone and payment terminal */}
                            <div className="relative">
                                <img
                                    src="/images/landing/hero-phone.png"
                                    alt="Trendpin App"
                                    className="w-full max-w-md mx-auto"
                                />

                                {/* Offer Badge */}
                                <div className="absolute bottom-8 right-8 bg-white rounded-xl shadow-lg p-3">
                                    <div className="flex items-center">
                                        <div className="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center mr-3">
                                            <span className="text-xl">🍕</span>
                                        </div>
                                        <div>
                                            <p className="text-sm font-medium text-gray-900">Italian Pizza</p>
                                            <p className="text-xs" style={{ color: '#E91E8C' }}>10% Off</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* What We Do Section */}
            <section className="py-16 px-6 lg:px-12 bg-gray-50">
                <div className="max-w-7xl mx-auto">
                    <div className="text-center mb-12">
                        <p className="text-sm font-medium tracking-wider mb-2" style={{ color: '#E91E8C' }}>WHAT WE DO</p>
                        <h2 className="text-3xl font-bold text-gray-900">
                            Your Favourites <span style={{ color: '#E91E8C' }}>Restaurants</span> &<br />
                            <span style={{ color: '#E91E8C' }}>Stores</span> with best offers
                        </h2>
                    </div>

                    <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {features.map((feature, index) => (
                            <div key={index} className="bg-white rounded-2xl p-6 shadow-sm">
                                <div className="mb-4">{feature.icon}</div>
                                <h3 className="font-semibold text-gray-900 mb-2 text-sm">{feature.title}</h3>
                                <p className="text-xs text-gray-500">{feature.description}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Offers Section */}
            <section className="py-16 px-6 lg:px-12">
                <div className="max-w-7xl mx-auto">
                    <div className="flex items-start justify-between mb-8">
                        <div>
                            <h2 className="text-3xl font-bold text-gray-900 mb-6">
                                Offers Always Makes<br />
                                You Fall In Love
                            </h2>

                            {/* Category Tabs */}
                            <div className="space-y-2">
                                {['Restaurants', 'Stores', 'Hotels'].map((category) => (
                                    <button
                                        key={category}
                                        onClick={() => setActiveCategory(category.toLowerCase())}
                                        className={`flex items-center px-4 py-2 rounded-full text-sm font-medium w-full ${
                                            activeCategory === category.toLowerCase()
                                                ? 'text-white'
                                                : 'text-gray-600 hover:bg-gray-100'
                                        }`}
                                        style={activeCategory === category.toLowerCase() ? { backgroundColor: '#E91E8C' } : {}}
                                    >
                                        <span className="mr-2">
                                            {category === 'Restaurants' && '🍽️'}
                                            {category === 'Stores' && '🏪'}
                                            {category === 'Hotels' && '🏨'}
                                        </span>
                                        {category}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Navigation Arrows */}
                        <div className="flex space-x-2">
                            <button className="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center hover:bg-gray-50">
                                <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button className="w-10 h-10 rounded-full flex items-center justify-center text-white" style={{ backgroundColor: '#E91E8C' }}>
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {/* Offer Cards */}
                    <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 ml-0 lg:ml-48">
                        {offers.map((offer, index) => (
                            <div key={index} className="bg-white rounded-2xl shadow-lg overflow-hidden">
                                <div className="aspect-video bg-gray-200 relative">
                                    <img
                                        src={offer.image}
                                        alt={offer.name}
                                        className="w-full h-full object-cover"
                                    />
                                </div>
                                <div className="p-4">
                                    <h3 className="font-semibold text-gray-900 mb-2">{offer.name}</h3>
                                    <div className="flex items-center space-x-2">
                                        <span className="px-2 py-1 rounded-full text-xs font-medium text-white" style={{ backgroundColor: '#E91E8C' }}>
                                            {offer.badge}
                                        </span>
                                        <span className="text-xs text-gray-500">{offer.discount}</span>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Bank Partners Section */}
            <section className="py-16 px-6 lg:px-12 bg-gray-50">
                <div className="max-w-7xl mx-auto">
                    <div className="grid lg:grid-cols-2 gap-12 items-center">
                        {/* Bank Logos */}
                        <div className="grid grid-cols-3 gap-6">
                            {banks.map((bank, index) => (
                                <div key={index} className="bg-white rounded-xl p-4 shadow-sm flex items-center justify-center h-20">
                                    <img
                                        src={bank.logo}
                                        alt={bank.name}
                                        className="max-h-12 max-w-full object-contain"
                                    />
                                </div>
                            ))}
                            <div className="bg-white rounded-xl p-4 shadow-sm flex items-center justify-center h-20">
                                <span className="text-gray-400 text-sm">+ More</span>
                            </div>
                        </div>

                        {/* Text Content */}
                        <div>
                            <h2 className="text-3xl font-bold text-gray-900 mb-4">
                                Enjoy <span style={{ color: '#E91E8C' }}>10% OFF</span> with<br />
                                participating bank credit<br />
                                cards
                            </h2>
                            <p className="text-gray-600 mb-6">
                                We work with many leading banks. See the full list<br />
                                of participating banks and eligible cards on our<br />
                                Partner Banks page.
                            </p>
                            <button className="px-6 py-3 rounded-full border-2 font-medium" style={{ borderColor: '#E91E8C', color: '#E91E8C' }}>
                                View All Banks
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            {/* Download App Section */}
            <section className="py-16 px-6 lg:px-12" style={{ backgroundColor: '#2D2A4A' }}>
                <div className="max-w-7xl mx-auto">
                    <div className="grid lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <p className="text-sm font-medium tracking-wider mb-2" style={{ color: '#E91E8C' }}>DOWNLOAD APP</p>
                            <h2 className="text-3xl font-bold text-white mb-4">
                                Get Started With<br />
                                Trendpin Today!
                            </h2>
                            <p className="text-gray-300 mb-6">
                                Discover food wherever and whenever and get<br />
                                your food delivered quickly.
                            </p>
                            <button
                                className="px-6 py-3 rounded-full text-white font-medium"
                                style={{ backgroundColor: '#E91E8C' }}
                            >
                                Get The App
                            </button>
                        </div>

                        {/* Phone Mockups */}
                        <div className="flex justify-center">
                            <img
                                src="/images/landing/phone-mockups.png"
                                alt="Trendpin App Screenshots"
                                className="max-w-md"
                            />
                        </div>
                    </div>
                </div>
            </section>

            {/* Footer */}
            <footer className="py-12 px-6 lg:px-12 bg-white border-t border-gray-100">
                <div className="max-w-7xl mx-auto">
                    <div className="grid md:grid-cols-4 gap-8">
                        {/* Logo & Description */}
                        <div>
                            <div className="flex items-center mb-4">
                                <svg className="w-8 h-8 mr-2" viewBox="0 0 40 40" fill="none">
                                    <path d="M20 0C12.268 0 6 6.268 6 14c0 10.5 14 26 14 26s14-15.5 14-26c0-7.732-6.268-14-14-14zm0 19c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.761 5-5 5z" fill="#E91E8C"/>
                                    <circle cx="20" cy="14" r="3" fill="white"/>
                                </svg>
                                <span className="text-xl font-semibold text-gray-900">Trendpin</span>
                            </div>
                            <p className="text-sm text-gray-500 mb-4">
                                Unlock a wallet full of exclusive<br />
                                offers and discounts with only a few<br />
                                clicks.
                            </p>
                            {/* Social Icons */}
                            <div className="flex space-x-4">
                                <a href="#" className="text-gray-400 hover:text-gray-600">
                                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </a>
                                <a href="#" className="text-gray-400 hover:text-gray-600">
                                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                    </svg>
                                </a>
                                <a href="#" className="text-gray-400 hover:text-gray-600">
                                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        {/* About */}
                        <div>
                            <h3 className="font-semibold text-gray-900 mb-4">About</h3>
                            <ul className="space-y-2">
                                <li><a href="#" className="text-sm text-gray-500 hover:text-gray-700">About Us</a></li>
                                <li><a href="#" className="text-sm text-gray-500 hover:text-gray-700">Features</a></li>
                            </ul>
                        </div>

                        {/* Company */}
                        <div>
                            <h3 className="font-semibold text-gray-900 mb-4">Company</h3>
                            <ul className="space-y-2">
                                <li><a href="#" className="text-sm text-gray-500 hover:text-gray-700">Why Trendpin?</a></li>
                                <li><a href="#" className="text-sm text-gray-500 hover:text-gray-700">Become a retailer</a></li>
                            </ul>
                        </div>

                        {/* Support */}
                        <div>
                            <h3 className="font-semibold text-gray-900 mb-4">Support</h3>
                            <ul className="space-y-2">
                                <li><a href="#" className="text-sm text-gray-500 hover:text-gray-700">Contact Us</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
}
