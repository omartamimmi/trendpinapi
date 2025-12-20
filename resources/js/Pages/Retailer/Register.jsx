import { useForm, Link } from '@inertiajs/react';
import { useState } from 'react';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });
    const [showPassword, setShowPassword] = useState(false);

    function handleSubmit(e) {
        e.preventDefault();
        post('/retailer/register');
    }

    return (
        <div className="h-screen flex overflow-hidden" style={{ backgroundColor: '#3D3A5C' }}>
            <div className="flex items-center w-full h-full">
                {/* Left side - Logo and Register Form */}
                <div className="flex-1 flex flex-col items-center justify-center py-8 px-8 overflow-y-auto">
                    <div className="w-full max-w-md flex flex-col">
                        {/* Logo */}
                        <div className="flex items-center justify-center mb-6">
                            <Link href="/" className="flex items-center space-x-2">
                                <img src="/images/logo.png" alt="logo" className='w-1/4' />
                                <span className="text-3xl font-bold text-white">Trenpin</span>
                            </Link>
                        </div>

                        {/* Register Card */}
                        <div className="bg-white rounded-2xl shadow-xl p-6 w-full">
                            <h2 className="text-xl font-bold text-gray-900 mb-1 text-center">
                                Retailer Registration
                            </h2>
                            <p className="text-gray-400 text-xs mb-4 text-center">
                                Creating your account is easy
                            </p>

                            <form onSubmit={handleSubmit}>
                                <div className="mb-2.5">
                                    <label htmlFor="name" className="block text-xs font-medium text-gray-900 mb-1">
                                        Full Name
                                    </label>
                                    <input
                                        id="name"
                                        name="name"
                                        type="text"
                                        autoComplete="name"
                                        required
                                        className="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent text-xs text-gray-900 placeholder-gray-400"
                                        placeholder="Enter your full name"
                                        value={data.name}
                                        onChange={e => setData('name', e.target.value)}
                                    />
                                    {errors.name && (
                                        <p className="mt-1 text-xs text-red-500">{errors.name}</p>
                                    )}
                                </div>

                                <div className="mb-2.5">
                                    <label htmlFor="email" className="block text-xs font-medium text-gray-900 mb-1">
                                        Email
                                    </label>
                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        autoComplete="email"
                                        required
                                        className="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent text-xs text-gray-900 placeholder-gray-400"
                                        placeholder="Enter your email"
                                        value={data.email}
                                        onChange={e => setData('email', e.target.value)}
                                    />
                                    {errors.email && (
                                        <p className="mt-1 text-xs text-red-500">{errors.email}</p>
                                    )}
                                </div>

                                <div className="mb-2.5">
                                    <label htmlFor="password" className="block text-xs font-medium text-gray-900 mb-1">
                                        Password
                                    </label>
                                    <div className="relative">
                                        <input
                                            id="password"
                                            name="password"
                                            type={showPassword ? 'text' : 'password'}
                                            autoComplete="new-password"
                                            required
                                            className="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent text-xs text-gray-900 placeholder-gray-400 pr-10"
                                            placeholder="Create a password"
                                            value={data.password}
                                            onChange={e => setData('password', e.target.value)}
                                        />
                                        <button
                                            type="button"
                                            onClick={() => setShowPassword(!showPassword)}
                                            className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                        >
                                            {showPassword ? (
                                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                </svg>
                                            ) : (
                                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            )}
                                        </button>
                                    </div>
                                    {errors.password && (
                                        <p className="mt-1 text-xs text-red-500">{errors.password}</p>
                                    )}
                                </div>

                                <div className="mb-4">
                                    <label htmlFor="password_confirmation" className="block text-xs font-medium text-gray-900 mb-1">
                                        Confirm Password
                                    </label>
                                    <input
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        autoComplete="new-password"
                                        required
                                        className="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent text-xs text-gray-900 placeholder-gray-400"
                                        placeholder="Confirm your password"
                                        value={data.password_confirmation}
                                        onChange={e => setData('password_confirmation', e.target.value)}
                                    />
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full py-2.5 px-4 rounded-lg text-white font-medium text-sm transition-all duration-200 disabled:opacity-50 hover:opacity-90"
                                    style={{ backgroundColor: '#E91E8C' }}
                                >
                                    {processing ? 'Creating account...' : 'Register'}
                                </button>
                            </form>

                            <p className="mt-4 text-center text-xs text-gray-500">
                                Already have an account?{' '}
                                <Link href="/retailer/login" className="font-medium" style={{ color: '#E91E8C' }}>
                                    Sign In
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>

                {/* Right side - Phone mockups */}
                <div className="hidden lg:block flex-1 h-screen overflow-hidden">
                    <img
                        src="/images/landing/phone-mockups.png"
                        alt="Trendpin App Mockups"
                        className="w-full h-full p-4"
                    />
                </div>
            </div>
        </div>
    );
}