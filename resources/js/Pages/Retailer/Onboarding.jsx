import RetailerLayout from '@/Layouts/RetailerLayout';
import { useState, useRef } from 'react';

export default function Onboarding({ onboarding, currentStep: initialStep, plans }) {
    const [currentStep, setCurrentStep] = useState(initialStep || 1);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [onboardingData, setOnboardingData] = useState(onboarding);

    // Step 1: Phone verification
    const [phoneNumber, setPhoneNumber] = useState('');
    const [otp, setOtp] = useState(['', '', '', '', '', '']);
    const [otpSent, setOtpSent] = useState(false);
    const otpRefs = [useRef(), useRef(), useRef(), useRef(), useRef(), useRef()];

    // Step 2: Payment details
    const [paymentMethods, setPaymentMethods] = useState({
        bank: false,
        cliq: false
    });
    const [bankName, setBankName] = useState('');
    const [iban, setIban] = useState('');
    const [cliqNumber, setCliqNumber] = useState('');

    // Step 3: Brand information
    const [brandType, setBrandType] = useState('single');
    const [brands, setBrands] = useState([{ name: '' }]);

    // Step 4: Subscription
    const [selectedPlan, setSelectedPlan] = useState(null);
    const [subscription, setSubscription] = useState(null);

    // Step 5: Payment
    const [paymentMethod, setPaymentMethod] = useState('card');

    const steps = [
        { id: 1, name: 'Retailer Details' },
        { id: 2, name: 'Payment Details' },
        { id: 3, name: 'Brand Information' },
        { id: 4, name: 'Subscription' },
        { id: 5, name: 'Pay' }
    ];

    const handleOtpChange = (index, value) => {
        if (value.length <= 1) {
            const newOtp = [...otp];
            newOtp[index] = value;
            setOtp(newOtp);

            if (value && index < 5) {
                otpRefs[index + 1].current.focus();
            }
        }
    };

    const handleOtpKeyDown = (index, e) => {
        if (e.key === 'Backspace' && !otp[index] && index > 0) {
            otpRefs[index - 1].current.focus();
        }
    };

    const getHeaders = () => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token || '',
        };
    };

    const sendOtp = async () => {
        setLoading(true);
        setError('');
        try {
            const response = await fetch('/retailer/onboarding/phone/send-otp', {
                method: 'POST',
                headers: getHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify({ phone_number: phoneNumber })
            });
            const data = await response.json();
            if (data.success) {
                setOtpSent(true);
            } else {
                setError(data.message || 'Failed to send OTP');
            }
        } catch (err) {
            setError('Failed to send OTP');
        }
        setLoading(false);
    };

    const verifyOtp = async () => {
        setLoading(true);
        setError('');
        try {
            const response = await fetch('/retailer/onboarding/phone/verify', {
                method: 'POST',
                headers: getHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify({
                    phone_number: phoneNumber,
                    code: otp.join('')
                })
            });
            const data = await response.json();
            if (data.success) {
                setCurrentStep(2);
            } else {
                setError(data.message || 'Failed to verify OTP');
            }
        } catch (err) {
            setError('Failed to verify OTP');
        }
        setLoading(false);
    };

    const savePaymentDetails = async () => {
        setLoading(true);
        setError('');
        try {
            const methods = [];
            if (paymentMethods.bank) {
                methods.push({ type: 'bank', bank_name: bankName, iban: iban });
            }
            if (paymentMethods.cliq) {
                methods.push({ type: 'cliq', cliq_number: cliqNumber });
            }

            const response = await fetch('/retailer/onboarding/payment-methods', {
                method: 'POST',
                headers: getHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify({ payment_methods: methods })
            });
            const data = await response.json();
            if (data.success) {
                setCurrentStep(3);
            } else {
                setError(data.message || 'Failed to save payment details');
            }
        } catch (err) {
            setError('Failed to save payment details');
        }
        setLoading(false);
    };

    const saveBrandInfo = async () => {
        setLoading(true);
        setError('');
        try {
            const response = await fetch('/retailer/onboarding/brands', {
                method: 'POST',
                headers: getHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify({ brand_type: brandType, brands: brands })
            });
            const data = await response.json();
            if (data.success) {
                setCurrentStep(4);
            } else {
                setError(data.message || 'Failed to save brand information');
            }
        } catch (err) {
            setError('Failed to save brand information');
        }
        setLoading(false);
    };

    const selectSubscription = async () => {
        setLoading(true);
        setError('');
        try {
            const response = await fetch('/retailer/onboarding/plans/select', {
                method: 'POST',
                headers: getHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify({ plan_id: selectedPlan })
            });
            const data = await response.json();
            if (data.success) {
                setSubscription(data.data?.subscription || data.data);
                setCurrentStep(5);
            } else {
                setError(data.message || 'Failed to select plan');
            }
        } catch (err) {
            setError('Failed to select plan');
        }
        setLoading(false);
    };

    const processPayment = async () => {
        setLoading(true);
        setError('');
        try {
            const response = await fetch('/retailer/onboarding/payment', {
                method: 'POST',
                headers: getHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify({
                    subscription_id: subscription?.id,
                    payment_method: paymentMethod
                })
            });
            const data = await response.json();
            if (data.success) {
                window.location.href = '/retailer/dashboard';
            } else {
                setError(data.message || 'Failed to process payment');
            }
        } catch (err) {
            setError('Failed to process payment');
        }
        setLoading(false);
    };

    const handleNext = () => {
        switch (currentStep) {
            case 1:
                if (otpSent) {
                    verifyOtp();
                } else {
                    sendOtp();
                }
                break;
            case 2:
                savePaymentDetails();
                break;
            case 3:
                saveBrandInfo();
                break;
            case 4:
                selectSubscription();
                break;
            case 5:
                processPayment();
                break;
        }
    };

    const handleBack = () => {
        if (currentStep > 1) {
            setCurrentStep(currentStep - 1);
        }
    };

    return (
        <RetailerLayout>
            <div className="max-w-4xl mx-auto">
                {/* Breadcrumb */}
                <div className="mb-6 text-sm">
                    <span className="text-gray-900 font-medium">New Retailer</span>
                    <span className="mx-2 text-gray-400">&gt;</span>
                    <span className="text-gray-400">Onboarding</span>
                </div>

                {/* Stepper */}
                <div className="mb-8">
                    <div className="flex items-center justify-between">
                        {steps.map((step, index) => (
                            <div key={step.id} className="flex items-center">
                                <div className="flex flex-col items-center">
                                    <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium ${
                                        currentStep > step.id
                                            ? 'bg-pink-500 text-white'
                                            : currentStep === step.id
                                                ? 'bg-pink-500 text-white'
                                                : 'bg-gray-200 text-gray-500'
                                    }`}>
                                        {currentStep > step.id ? (
                                            <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                        ) : step.id}
                                    </div>
                                    <span className={`mt-2 text-xs ${
                                        currentStep >= step.id ? 'text-pink-500 font-medium' : 'text-gray-500'
                                    }`}>
                                        {step.name}
                                    </span>
                                </div>
                                {index < steps.length - 1 && (
                                    <div className={`w-24 h-0.5 mx-2 ${
                                        currentStep > step.id ? 'bg-pink-500' : 'bg-gray-200'
                                    }`} />
                                )}
                            </div>
                        ))}
                    </div>
                </div>

                {/* Content */}
                <div className="bg-white rounded-2xl shadow-sm p-8">
                    {error && (
                        <div className="mb-4 p-3 bg-red-50 text-red-600 rounded-lg text-sm">
                            {error}
                        </div>
                    )}

                    {/* Step 1: Phone Verification */}
                    {currentStep === 1 && (
                        <div className="text-center">
                            <h2 className="text-xl font-bold text-gray-900 mb-6">Verify Phone Details</h2>

                            {!otpSent ? (
                                <div className="max-w-sm mx-auto">
                                    <label className="block text-sm font-medium text-gray-700 mb-2 text-left">
                                        Phone Number
                                    </label>
                                    <input
                                        type="tel"
                                        value={phoneNumber}
                                        onChange={(e) => setPhoneNumber(e.target.value)}
                                        className="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                        placeholder="+962 7XX XXX XXX"
                                    />
                                </div>
                            ) : (
                                <div>
                                    <p className="text-gray-600 mb-2">
                                        We send a <span className="font-semibold">OTP</span> on your Phone
                                    </p>
                                    <p className="text-gray-500 mb-6">{phoneNumber}</p>

                                    <div className="flex justify-center space-x-3 mb-6">
                                        {otp.map((digit, index) => (
                                            <input
                                                key={index}
                                                ref={otpRefs[index]}
                                                type="text"
                                                maxLength="1"
                                                value={digit}
                                                onChange={(e) => handleOtpChange(index, e.target.value)}
                                                onKeyDown={(e) => handleOtpKeyDown(index, e)}
                                                className={`w-14 h-14 text-center text-xl font-semibold rounded-lg border-2 focus:outline-none focus:ring-2 focus:ring-pink-500 ${
                                                    digit ? 'bg-pink-500 text-white border-pink-500' : 'border-gray-200'
                                                }`}
                                            />
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Step 2: Payment Details */}
                    {currentStep === 2 && (
                        <div>
                            <h2 className="text-xl font-bold text-gray-900 mb-6 text-center">Payment Details</h2>
                            <h3 className="text-lg font-medium text-gray-900 mb-4 text-center">Select Payment Method</h3>

                            <div className="grid grid-cols-2 gap-6 mb-6">
                                <div>
                                    <p className="text-sm text-gray-500 mb-2">Bank Payment</p>
                                    <button
                                        onClick={() => setPaymentMethods({...paymentMethods, bank: !paymentMethods.bank})}
                                        className={`w-full p-4 rounded-lg border-2 text-left flex items-center justify-between ${
                                            paymentMethods.bank ? 'border-pink-500' : 'border-gray-200'
                                        }`}
                                    >
                                        <span>Bank Account</span>
                                        <div className={`w-4 h-4 rounded ${paymentMethods.bank ? 'bg-pink-500' : 'bg-gray-200'}`} />
                                    </button>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-500 mb-2">Mobile Payment</p>
                                    <button
                                        onClick={() => setPaymentMethods({...paymentMethods, cliq: !paymentMethods.cliq})}
                                        className={`w-full p-4 rounded-lg border-2 text-left flex items-center justify-between ${
                                            paymentMethods.cliq ? 'border-pink-500' : 'border-gray-200'
                                        }`}
                                    >
                                        <span>Cliq Account</span>
                                        <div className={`w-4 h-4 rounded ${paymentMethods.cliq ? 'bg-pink-500' : 'bg-gray-200'}`} />
                                    </button>
                                </div>
                            </div>

                            <div className="grid grid-cols-3 gap-4">
                                {paymentMethods.bank && (
                                    <>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Bank Name *
                                            </label>
                                            <select
                                                value={bankName}
                                                onChange={(e) => setBankName(e.target.value)}
                                                className="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                            >
                                                <option value="">Select bank</option>
                                                <option value="arab_bank">Arab Bank</option>
                                                <option value="housing_bank">Housing Bank</option>
                                                <option value="jordan_bank">Bank of Jordan</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                IBAN No *
                                            </label>
                                            <input
                                                type="text"
                                                value={iban}
                                                onChange={(e) => setIban(e.target.value)}
                                                className="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                                placeholder="123456789567432"
                                            />
                                        </div>
                                    </>
                                )}
                                {paymentMethods.cliq && (
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Cliq Number
                                        </label>
                                        <input
                                            type="tel"
                                            value={cliqNumber}
                                            onChange={(e) => setCliqNumber(e.target.value)}
                                            className="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                            placeholder="Jordan (+962)"
                                        />
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Step 3: Brand Information */}
                    {currentStep === 3 && (
                        <div>
                            <h2 className="text-xl font-bold text-gray-900 mb-6 text-center">Brand Information</h2>

                            <div className="mb-6">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Brand Type
                                </label>
                                <div className="flex space-x-4">
                                    <button
                                        onClick={() => setBrandType('single')}
                                        className={`px-4 py-2 rounded-lg border-2 ${
                                            brandType === 'single' ? 'border-pink-500 text-pink-500' : 'border-gray-200'
                                        }`}
                                    >
                                        Single Brand
                                    </button>
                                    <button
                                        onClick={() => setBrandType('group')}
                                        className={`px-4 py-2 rounded-lg border-2 ${
                                            brandType === 'group' ? 'border-pink-500 text-pink-500' : 'border-gray-200'
                                        }`}
                                    >
                                        Group of Brands
                                    </button>
                                </div>
                            </div>

                            {brands.map((brand, index) => (
                                <div key={index} className="mb-4">
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Brand Name {index + 1}
                                    </label>
                                    <input
                                        type="text"
                                        value={brand.name}
                                        onChange={(e) => {
                                            const newBrands = [...brands];
                                            newBrands[index].name = e.target.value;
                                            setBrands(newBrands);
                                        }}
                                        className="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-500"
                                        placeholder="Enter brand name"
                                    />
                                </div>
                            ))}

                            {brandType === 'group' && (
                                <button
                                    onClick={() => setBrands([...brands, { name: '' }])}
                                    className="text-pink-500 text-sm font-medium"
                                >
                                    + Add Another Brand
                                </button>
                            )}
                        </div>
                    )}

                    {/* Step 4: Subscription */}
                    {currentStep === 4 && (
                        <div>
                            <h2 className="text-xl font-bold text-gray-900 mb-6 text-center">Select Subscription</h2>

                            <div className="grid grid-cols-3 gap-4">
                                {(plans || []).map((plan) => (
                                    <button
                                        key={plan.id}
                                        onClick={() => setSelectedPlan(plan.id)}
                                        className={`p-6 rounded-xl border-2 text-left ${
                                            selectedPlan === plan.id ? 'border-pink-500' : 'border-gray-200'
                                        }`}
                                    >
                                        <h3 className="font-semibold text-gray-900 mb-2">{plan.name}</h3>
                                        <p className="text-2xl font-bold text-gray-900 mb-2">
                                            {plan.price} <span className="text-sm font-normal">JD/{plan.duration_months}mo</span>
                                        </p>
                                        <p className="text-sm text-gray-500">{plan.description}</p>
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Step 5: Payment */}
                    {currentStep === 5 && (
                        <div>
                            <h2 className="text-xl font-bold text-gray-900 mb-6 text-center">Complete Payment</h2>

                            <div className="max-w-md mx-auto">
                                <label className="block text-sm font-medium text-gray-700 mb-4">
                                    Select Payment Method
                                </label>
                                <div className="space-y-3">
                                    {['card', 'cash', 'cliq'].map((method) => (
                                        <button
                                            key={method}
                                            onClick={() => setPaymentMethod(method)}
                                            className={`w-full p-4 rounded-lg border-2 text-left flex items-center justify-between ${
                                                paymentMethod === method ? 'border-pink-500' : 'border-gray-200'
                                            }`}
                                        >
                                            <span className="capitalize">{method === 'card' ? 'Credit/Debit Card' : method === 'cliq' ? 'Cliq' : 'Cash'}</span>
                                            <div className={`w-4 h-4 rounded-full border-2 ${
                                                paymentMethod === method ? 'border-pink-500 bg-pink-500' : 'border-gray-300'
                                            }`} />
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Navigation Buttons */}
                <div className="flex justify-end space-x-4 mt-6">
                    <button
                        onClick={handleBack}
                        disabled={currentStep === 1}
                        className="px-8 py-3 rounded-lg bg-gray-200 text-gray-600 font-medium disabled:opacity-50"
                    >
                        Back
                    </button>
                    <button
                        onClick={handleNext}
                        disabled={loading}
                        className="px-8 py-3 rounded-lg text-white font-medium disabled:opacity-50"
                        style={{ backgroundColor: '#E91E8C' }}
                    >
                        {loading ? 'Processing...' : currentStep === 5 ? 'Complete' : 'Next'}
                    </button>
                </div>
            </div>
        </RetailerLayout>
    );
}
