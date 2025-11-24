import { useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });
    const [showPassword, setShowPassword] = useState(false);

    function handleSubmit(e) {
        e.preventDefault();
        post('/admin/login');
    }

    return (
        <div className="min-h-screen flex items-center justify-center px-4" style={{ backgroundColor: '#2D2A4A' }}>
            <div className="w-full max-w-md">
                {/* Logo */}
                <div className="flex items-center justify-center mb-8">
                    <svg className="w-10 h-10 mr-2" viewBox="0 0 40 40" fill="none">
                        <path d="M20 0C12.268 0 6 6.268 6 14c0 10.5 14 26 14 26s14-15.5 14-26c0-7.732-6.268-14-14-14zm0 19c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.761 5-5 5z" fill="#E91E8C"/>
                        <circle cx="20" cy="14" r="3" fill="white"/>
                    </svg>
                    <span className="text-white text-3xl font-semibold">Trendpin</span>
                </div>

                {/* Login Card */}
                <div className="bg-white rounded-2xl shadow-xl p-8">
                    <h2 className="text-2xl font-bold text-gray-900 mb-1">
                        Admin Login
                    </h2>
                    <p className="text-gray-500 text-sm mb-6">
                        Enter details to access admin dashboard
                    </p>

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
                            className="w-full py-3 px-4 rounded-full text-white font-medium transition-all duration-200 disabled:opacity-50"
                            style={{ backgroundColor: '#E91E8C' }}
                        >
                            {processing ? 'Signing in...' : 'Login'}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}
