import { useForm, Link } from '@inertiajs/react';
import { useState } from 'react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });
    const [showPassword, setShowPassword] = useState(false);

    function handleSubmit(e) {
        e.preventDefault();
        post('/login');
    }

    return (
        <div className="min-h-screen flex" style={{ backgroundColor: '#2D2A4A' }}>
            {/* Left side - Phone mockup */}
            <div className="hidden lg:flex lg:w-1/3 items-center justify-center p-8">
                <div className="relative">
                    <img
                        src="/images/landing/phone-mockups.png"
                        alt="Trendpin App"
                        className="w-64"
                    />
                </div>
            </div>

            {/* Center - Login Form */}
            <div className="flex-1 flex items-center justify-center px-4 py-12">
                <div className="w-full max-w-md">
                    {/* Logo */}
                    <div className="flex items-center justify-center mb-8">
                        <Link href="/" className="flex items-center">
                            <svg className="w-10 h-10 mr-2" viewBox="0 0 40 40" fill="none">
                                <path d="M20 0C12.268 0 6 6.268 6 14c0 10.5 14 26 14 26s14-15.5 14-26c0-7.732-6.268-14-14-14zm0 19c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.761 5-5 5z" fill="#E91E8C"/>
                                <circle cx="20" cy="14" r="3" fill="white"/>
                            </svg>
                            <span className="text-3xl font-semibold text-white">Trendpin</span>
                        </Link>
                    </div>

                    {/* Login Card */}
                    <div className="bg-white rounded-2xl shadow-xl p-8">
                        <h2 className="text-2xl font-bold text-gray-900 mb-1 text-center">
                            Welcome Back!
                        </h2>
                        <p className="text-gray-500 text-sm mb-6 text-center">
                            Sign in to your account
                        </p>

                        {/* Google Sign In */}
                        <button
                            type="button"
                            className="w-full flex items-center justify-center px-4 py-3 border border-gray-200 rounded-lg mb-4 hover:bg-gray-50 transition-colors"
                        >
                            <svg className="w-5 h-5 mr-2" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span className="text-gray-600 text-sm font-medium">Sign in with Google</span>
                        </button>

                        <div className="relative mb-4">
                            <div className="absolute inset-0 flex items-center">
                                <div className="w-full border-t border-gray-200"></div>
                            </div>
                            <div className="relative flex justify-center text-sm">
                                <span className="px-2 bg-white text-gray-500">or</span>
                            </div>
                        </div>

                        <form onSubmit={handleSubmit}>
                            <div className="mb-4">
                                <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                                    Email
                                </label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    autoComplete="email"
                                    required
                                    className="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent text-gray-900 placeholder-gray-400"
                                    placeholder="Enter your email"
                                    value={data.email}
                                    onChange={e => setData('email', e.target.value)}
                                />
                                {errors.email && (
                                    <p className="mt-1 text-sm text-red-500">{errors.email}</p>
                                )}
                            </div>

                            <div className="mb-6">
                                <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
                                    Password
                                </label>
                                <div className="relative">
                                    <input
                                        id="password"
                                        name="password"
                                        type={showPassword ? 'text' : 'password'}
                                        autoComplete="current-password"
                                        required
                                        className="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent text-gray-900 placeholder-gray-400 pr-12"
                                        placeholder="Enter your password"
                                        value={data.password}
                                        onChange={e => setData('password', e.target.value)}
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword(!showPassword)}
                                        className="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    >
                                        {showPassword ? (
                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        ) : (
                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        )}
                                    </button>
                                </div>
                                {errors.password && (
                                    <p className="mt-1 text-sm text-red-500">{errors.password}</p>
                                )}
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full py-3 px-4 rounded-full text-white font-medium transition-all duration-200 disabled:opacity-50"
                                style={{ backgroundColor: '#E91E8C' }}
                            >
                                {processing ? 'Signing in...' : 'Sign In'}
                            </button>
                        </form>

                        <p className="mt-6 text-center text-sm text-gray-500">
                            Don't have an account?{' '}
                            <Link href="/retailer/register" className="font-medium" style={{ color: '#E91E8C' }}>
                                Register as Retailer
                            </Link>
                        </p>
                    </div>
                </div>
            </div>

            {/* Right side - Bank logos */}
            <div className="hidden lg:flex lg:w-1/3 items-center justify-center p-8">
                <div className="space-y-6">
                    <div className="flex justify-center">
                        <img src="/images/landing/nbk.png" alt="NBK" className="h-12 object-contain bg-white p-2 rounded" />
                    </div>
                    <div className="flex justify-center">
                        <img src="/images/landing/cairo-amman.png" alt="Cairo Amman Bank" className="h-12 object-contain bg-white p-2 rounded" />
                    </div>
                    <div className="flex justify-center">
                        <img src="/images/landing/capital-bank.png" alt="Capital Bank" className="h-12 object-contain bg-white p-2 rounded" />
                    </div>
                </div>
            </div>
        </div>
    );
}
