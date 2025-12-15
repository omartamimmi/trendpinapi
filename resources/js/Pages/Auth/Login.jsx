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
        <div className="h-screen flex overflow-hidden" style={{ backgroundColor: '#3D3A5C' }}>
            <div className="flex items-center w-full">
                {/* Left side - Logo and Login Form */}
                <div className="flex-1 flex flex-col items-center justify-center py-8 px-8 overflow-y-auto">
                    {/* Logo */}
                    <div className="flex items-center mb-6">
                        <Link href="/" className="flex items-center space-x-2">
                           <img src="/images/logo.png" alt="logo" className='w-1/4' />
                            <span className="text-4xl font-bold text-white">Trenpin</span>
                        </Link>
                    </div>

                    {/* Login Card */}
                    <div className="bg-white rounded-2xl shadow-xl p-12 w-full max-w-md">
                        <h2 className="text-2xl font-bold text-gray-900 mb-2 text-center">
                            Retailer Login
                        </h2>
                        <p className="text-gray-400 text-xs mb-8 text-center">
                            Enter details to create your account
                        </p>



                        <form onSubmit={handleSubmit}>
                            <div className="mb-5">
                                <label htmlFor="email" className="block text-sm font-medium text-gray-900 mb-2">
                                    Email
                                </label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    autoComplete="email"
                                    required
                                    className="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent text-sm text-gray-900 placeholder-gray-400"
                                    placeholder="Enter your name"
                                    value={data.email}
                                    onChange={e => setData('email', e.target.value)}
                                />
                                {errors.email && (
                                    <p className="mt-1 text-sm text-red-500">{errors.email}</p>
                                )}
                            </div>

                            <div className="mb-6">
                                <label htmlFor="password" className="block text-sm font-medium text-gray-900 mb-2">
                                    Password
                                </label>
                                <div className="relative">
                                    <input
                                        id="password"
                                        name="password"
                                        type={showPassword ? 'text' : 'password'}
                                        autoComplete="current-password"
                                        required
                                        className="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent text-sm text-gray-900 placeholder-gray-400 pr-12"
                                        placeholder="Enter your Password"
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
                                className="w-full py-3.5 px-4 rounded-lg text-white font-medium text-base transition-all duration-200 disabled:opacity-50 hover:opacity-90"
                                style={{ backgroundColor: '#E91E8C' }}
                            >
                                {processing ? 'Signing in...' : 'Login'}
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
 