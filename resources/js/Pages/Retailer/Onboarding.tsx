import { useState, FormEvent } from "react";
import { router } from "@inertiajs/react";
import Step1RetailerDetails from "./OnboardingSteps/Step1RetailerDetails";
import Step2BankAccount from "./OnboardingSteps/Step2BankAccount";
import Step3BrandInformation from "./OnboardingSteps/Step3BrandInformation";
import Step4Subscription from "./OnboardingSteps/Step4Subscription";
import Step5Payment from "./OnboardingSteps/Step5Payment";

type StepId = 1 | 2 | 3 | 4 | 5;

type Step1Data = {
  retailerName: string;
  phoneNumber?: string;
  countryCode?: string;
  city: string;
  category: string;
  logoFile: File | null;
  licenseFile: File | null;
};

type Step2Data = {
  paymentMethod: string[];
  bankName: string;
  iban: string;
  cliqNumber: string;
  countryCode?: string;
};

type GroupData = {
  brandName: string;
  description: string;
  position: { lat: number; lng: number };
};

type Step3Data = {
  groups: GroupData[];
};

type Step4Data = {
  subscription: string;
};

type Step5Data = {
  paymentOption: "" | "cliq" | "cash" | "card";
};

type FormDataType = {
  step1: Step1Data;
  step2: Step2Data;
  step3: Step3Data;
  step4: Step4Data;
  step5: Step5Data;
};

interface OnboardingProps {
  onboarding: {
    id: number;
    user_id: number;
    current_step: string;
    status: string;
    city?: string;
    category?: string;
    logo_path?: string;
    license_path?: string;
    logo_url?: string;
    license_url?: string;
  };
  user?: {
    name: string;
    phone: string;
  };
  currentStep: number;
  plans: any[];
  csrf_token?: string;
  existingPaymentMethods?: any[];
  existingBrands?: any[];
  existingSubscription?: any;
  isAdminEdit?: boolean;
}

export default function Onboarding({
  onboarding,
  user,
  currentStep = 1,
  plans,
  csrf_token,
  existingPaymentMethods = [],
  existingBrands = [],
  existingSubscription,
  isAdminEdit = false
}: OnboardingProps) {
  const [activeStep, setActiveStep] = useState<StepId>(currentStep as StepId);
  const [processing, setProcessing] = useState(false);

  const steps = [
    { id: 1, title: "Retailer Details", icon: "M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" },
    { id: 2, title: "Payment Details", icon: "M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" },
    { id: 3, title: "Brand Info", icon: "M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" },
    { id: 4, title: "Subscription", icon: "M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" },
    { id: 5, title: "Payment", icon: "M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" },
  ];

  // Initialize form data with existing data
  const initializeFormData = (): FormDataType => {
    const step1 = {
      retailerName: user?.name || "",
      phoneNumber: user?.phone || "",
      countryCode: "",
      city: onboarding.city || "",
      category: onboarding.category || "",
      logoFile: null,
      licenseFile: null
    };

    const step2 = {
      paymentMethod: existingPaymentMethods.length > 0
        ? existingPaymentMethods.map(pm => pm.type)
        : [],
      bankName: existingPaymentMethods.find(pm => pm.type === 'bank')?.bank_name || "",
      iban: existingPaymentMethods.find(pm => pm.type === 'bank')?.iban || "",
      cliqNumber: existingPaymentMethods.find(pm => pm.type === 'cliq')?.cliq_number || "",
      countryCode: ""
    };

    const step3 = {
      groups: existingBrands.map(brand => {
        // Handle different location formats from backend
        let position = { lat: 31.963158, lng: 35.930359 }; // Default to Jordan

        if (brand.latitude && brand.longitude) {
          // Backend returns separate lat/lng fields
          position = {
            lat: parseFloat(brand.latitude),
            lng: parseFloat(brand.longitude)
          };
        } else if (brand.location) {
          // Backend returns location object or JSON string
          if (typeof brand.location === 'string') {
            try {
              const parsed = JSON.parse(brand.location);
              position = { lat: parsed.lat || 0, lng: parsed.lng || 0 };
            } catch {
              position = { lat: 0, lng: 0 };
            }
          } else if (brand.location.lat && brand.location.lng) {
            position = brand.location;
          }
        }

        return {
          brandName: brand.name || "",
          description: brand.description || "",
          position
        };
      })
    };

    const step4 = {
      subscription: existingSubscription?.subscription_plan_id?.toString() || ""
    };

    const step5 = {
      paymentOption: "" as "" | "cliq" | "cash" | "card"
    };

    return { step1, step2, step3, step4, step5 };
  };

  const [formData, setFormData] = useState<FormDataType>(initializeFormData());

  const handleChange = <
    K extends keyof FormDataType,
    F extends keyof FormDataType[K]
  >(
    step: K,
    field: F,
    value: FormDataType[K][F]
  ) => {
    setFormData((prev) => ({
      ...prev,
      [step]: { ...prev[step], [field]: value },
    }));
  };

  const handleNext = async () => {
    setProcessing(true);

    try {
      if (activeStep === 1) {
        if (!formData.step1.retailerName || !formData.step1.category || !formData.step1.city) {
          alert('Please fill in all required fields in Step 1');
          setProcessing(false);
          return;
        }

        const submitData = new FormData();
        submitData.append('retailer_name', formData.step1.retailerName);
        submitData.append('category', formData.step1.category);
        submitData.append('city', formData.step1.city);
        if (formData.step1.phoneNumber) submitData.append('phone_number', formData.step1.phoneNumber);
        if (formData.step1.countryCode) submitData.append('country_code', formData.step1.countryCode);
        if (formData.step1.logoFile) submitData.append('logo', formData.step1.logoFile);
        if (formData.step1.licenseFile) submitData.append('license', formData.step1.licenseFile);
        if (isAdminEdit && onboarding?.user_id) submitData.append('onboarding_user_id', String(onboarding.user_id));

        await router.post('/retailer/onboarding/retailer-details', submitData, {
          preserveState: !isAdminEdit,
          preserveScroll: true,
          forceFormData: true,
          onSuccess: () => {
            setProcessing(false);
            if (!isAdminEdit) {
              setActiveStep(2);
            }
          },
          onError: (errors) => {
            console.error('Error saving Step 1:', errors);
            setProcessing(false);
            alert('Failed to save Step 1 data. Please try again.');
          },
        });
      } else if (activeStep === 2) {
        const methods = (formData.step2.paymentMethod || []).map((type: string) => {
          if (type === 'cliq') {
            return {
              type: 'cliq',
              cliq_number: formData.step2.cliqNumber || '',
              bank_name: null,
              iban: null,
            };
          } else if (type === 'bank') {
            return {
              type: 'bank',
              cliq_number: null,
              bank_name: formData.step2.bankName || '',
              iban: formData.step2.iban || '',
            };
          }
        }).filter(Boolean);

        if (methods.length === 0) {
          setProcessing(false);
          setActiveStep(3);
          return;
        }

        const submitData: any = {
          payment_methods: methods,
        };
        if (isAdminEdit && onboarding?.user_id) {
          submitData.onboarding_user_id = onboarding.user_id;
        }

        await router.post('/retailer/onboarding/payment-methods', submitData, {
          preserveState: !isAdminEdit,
          preserveScroll: true,
          onSuccess: () => {
            setProcessing(false);
            if (!isAdminEdit) {
              setActiveStep(3);
            }
          },
          onError: (errors) => {
            console.error('Error saving Step 2:', errors);
            setProcessing(false);
            alert('Failed to save payment methods. Please try again.');
          },
        });
      } else if (activeStep === 3) {
        const groups = formData.step3.groups || [];

        if (groups.length === 0) {
          setProcessing(false);
          setActiveStep(4);
          return;
        }

        const brands = groups.map((group: any) => ({
          name: group.brandName,
          description: group.description || '',
          latitude: group.position?.lat || 0,
          longitude: group.position?.lng || 0,
        }));

        const submitData: any = {
          brand_type: brands.length > 1 ? 'group' : 'single',
          brands: brands,
        };
        if (isAdminEdit && onboarding?.user_id) {
          submitData.onboarding_user_id = onboarding.user_id;
        }

        await router.post('/retailer/onboarding/brands', submitData, {
          preserveState: !isAdminEdit,
          preserveScroll: true,
          onSuccess: () => {
            setProcessing(false);
            if (!isAdminEdit) {
              setActiveStep(4);
            }
          },
          onError: (errors) => {
            console.error('Error saving Step 3:', errors);
            setProcessing(false);
            alert('Failed to save brand information. Please try again.');
          },
        });
      } else if (activeStep === 4) {
        const submitData: any = {
          plan_id: formData.step4.subscription || null,
        };
        if (isAdminEdit && onboarding?.user_id) {
          submitData.onboarding_user_id = onboarding.user_id;
        }

        await router.post('/retailer/onboarding/plans/select', submitData, {
          preserveState: !isAdminEdit,
          preserveScroll: true,
          onSuccess: () => {
            setProcessing(false);
            if (!isAdminEdit) {
              setActiveStep(5);
            }
          },
          onError: (errors) => {
            console.error('Error saving Step 4:', errors);
            setProcessing(false);
            alert('Failed to save subscription plan. Please try again.');
          },
        });
      }
    } catch (error) {
      console.error('Error:', error);
      setProcessing(false);
      alert('An error occurred. Please try again.');
    }
  };

  const handleBack = () => {
    if (activeStep > 1) {
      setActiveStep((prev) => (prev - 1) as StepId);
    }
  };

  const handleSubmit = async (e?: FormEvent) => {
    if (e) e.preventDefault();

    setProcessing(true);

    try {
      const submitData: any = {
        payment_option: formData.step5.paymentOption || 'pending',
      };

      if (isAdminEdit && onboarding?.user_id) {
        submitData.onboarding_user_id = onboarding.user_id;
      }

      router.post('/retailer/onboarding/complete', submitData, {
        preserveState: !isAdminEdit,
        preserveScroll: false,
        forceFormData: true,
        onSuccess: () => {
          setProcessing(false);
        },
        onError: (errors) => {
          console.error('Onboarding completion error:', errors);
          setProcessing(false);

          if (errors && typeof errors === 'object') {
            const errorMessages = Object.values(errors).flat();
            if (errorMessages.length > 0) {
              alert('Validation errors:\n' + errorMessages.join('\n'));
              return;
            }
          }

          alert('Failed to submit onboarding. Please check all fields and try again.');
        },
      });
    } catch (error) {
      setProcessing(false);
      if (error instanceof Error) {
        alert(error.message);
      } else {
        alert('An error occurred. Please try again.');
      }
    }
  };

  const renderStep = () => {
    switch (activeStep) {
      case 1:
        return (
          <Step1RetailerDetails
            data={formData.step1}
            onChange={(f, v) => handleChange("step1", f, v)}
            existingLogoUrl={onboarding.logo_url}
            existingLicenseUrl={onboarding.license_url}
          />
        );
      case 2:
        return (
          <Step2BankAccount
            data={formData.step2}
            onChange={(f, v) => handleChange("step2", f, v)}
          />
        );
      case 3:
        return (
          <Step3BrandInformation
            data={formData.step3}
            onChange={(f, v) => handleChange("step3", f, v)}
          />
        );
      case 4:
        return (
          <Step4Subscription
            data={formData.step4}
            onChange={(f, v) => handleChange("step4", f, v)}
            plans={plans}
          />
        );
      case 5:
        return (
          <Step5Payment
            data={formData.step5}
            onChange={(f, v) => handleChange("step5", f, v)}
          />
        );
      default:
        return null;
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-pink-50">
      {/* Header */}
      <div className="bg-white border-b border-gray-100 shadow-sm">
        <div className="max-w-6xl mx-auto px-4 py-6">
          <div className="flex items-center justify-center gap-3">
            <div className="w-10 h-10 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl flex items-center justify-center">
              <svg className="w-6 h-6 text-white" viewBox="0 0 40 40" fill="none">
                <path d="M20 0C12.268 0 6 6.268 6 14c0 10.5 14 26 14 26s14-15.5 14-26c0-7.732-6.268-14-14-14zm0 19c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.761 5-5 5z" fill="currentColor"/>
              </svg>
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Retailer Onboarding</h1>
              <p className="text-sm text-gray-500">Complete your profile to get started</p>
            </div>
          </div>
        </div>
      </div>

      {/* Progress Bar */}
      <div className="bg-white border-b border-gray-100">
        <div className="max-w-4xl mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <span className="text-sm font-medium text-gray-600">Progress</span>
            <span className="text-sm font-semibold text-pink-600">{Math.round((activeStep / 5) * 100)}%</span>
          </div>
          <div className="mt-2 h-2 bg-gray-100 rounded-full overflow-hidden">
            <div
              className="h-full bg-gradient-to-r from-pink-500 to-pink-600 rounded-full transition-all duration-500 ease-out"
              style={{ width: `${(activeStep / 5) * 100}%` }}
            />
          </div>
        </div>
      </div>

      {/* Stepper */}
      <div className="bg-white shadow-sm">
        <div className="max-w-5xl mx-auto px-4 py-6">
          <div className="flex items-center justify-between">
            {steps.map((step, index) => {
              const isActive = step.id === activeStep;
              const isCompleted = step.id < activeStep;
              const isLast = index === steps.length - 1;

              return (
                <div key={step.id} className="flex items-center flex-1">
                  {/* Step Circle */}
                  <div className="flex flex-col items-center relative">
                    <button
                      type="button"
                      onClick={() => step.id <= activeStep && setActiveStep(step.id as StepId)}
                      disabled={step.id > activeStep}
                      className={`
                        relative z-10 flex items-center justify-center w-12 h-12 rounded-2xl
                        transition-all duration-300 transform
                        ${isCompleted
                          ? 'bg-gradient-to-br from-pink-500 to-pink-600 text-white shadow-lg shadow-pink-500/30 hover:scale-105'
                          : isActive
                            ? 'bg-gradient-to-br from-[#2F305A] to-[#1a1b3a] text-white shadow-lg shadow-gray-900/20 ring-4 ring-pink-500/20'
                            : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                        }
                      `}
                    >
                      {isCompleted ? (
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                      ) : (
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d={step.icon} />
                        </svg>
                      )}
                    </button>

                    {/* Step Label */}
                    <span className={`
                      mt-3 text-xs font-semibold text-center max-w-[80px] leading-tight
                      transition-colors duration-300
                      ${isCompleted ? 'text-pink-600' : isActive ? 'text-gray-900' : 'text-gray-400'}
                    `}>
                      {step.title}
                    </span>

                    {/* Active Indicator */}
                    {isActive && (
                      <div className="absolute -top-1 -right-1 w-3 h-3 bg-pink-500 rounded-full animate-pulse" />
                    )}
                  </div>

                  {/* Connector Line */}
                  {!isLast && (
                    <div className="flex-1 mx-3 h-1 rounded-full overflow-hidden bg-gray-100">
                      <div
                        className={`h-full rounded-full transition-all duration-500 ${
                          isCompleted ? 'bg-gradient-to-r from-pink-500 to-pink-400 w-full' : 'w-0'
                        }`}
                      />
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="max-w-4xl mx-auto px-4 py-8">
        <div className="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
          {/* Step Content */}
          <div className="p-8">
            <form onSubmit={handleSubmit}>
              {renderStep()}

              {/* Navigation Buttons */}
              <div className="flex items-center justify-between mt-10 pt-8 border-t border-gray-100">
                <div>
                  {activeStep > 1 && (
                    <button
                      type="button"
                      onClick={handleBack}
                      disabled={processing}
                      className="
                        flex items-center gap-2 px-6 py-3
                        text-gray-600 font-medium rounded-xl
                        bg-gray-100 hover:bg-gray-200
                        transition-all duration-200
                        disabled:opacity-50 disabled:cursor-not-allowed
                      "
                    >
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                      </svg>
                      Back
                    </button>
                  )}
                </div>

                <div className="flex items-center gap-3">
                  {/* Step Indicator Pills */}
                  <div className="hidden sm:flex items-center gap-1.5 mr-4">
                    {steps.map((step) => (
                      <div
                        key={step.id}
                        className={`
                          w-2 h-2 rounded-full transition-all duration-300
                          ${step.id === activeStep ? 'w-6 bg-pink-500' : step.id < activeStep ? 'bg-pink-300' : 'bg-gray-200'}
                        `}
                      />
                    ))}
                  </div>

                  {activeStep < 5 ? (
                    <button
                      type="button"
                      onClick={handleNext}
                      disabled={processing}
                      className="
                        flex items-center gap-2 px-8 py-3
                        text-white font-semibold rounded-xl
                        bg-gradient-to-r from-pink-500 to-pink-600
                        hover:from-pink-600 hover:to-pink-700
                        shadow-lg shadow-pink-500/30
                        transition-all duration-200 transform hover:scale-[1.02]
                        disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none
                      "
                    >
                      {processing ? (
                        <>
                          <svg className="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                          </svg>
                          Saving...
                        </>
                      ) : (
                        <>
                          Continue
                          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                          </svg>
                        </>
                      )}
                    </button>
                  ) : (
                    <button
                      type="submit"
                      disabled={processing}
                      className="
                        flex items-center gap-2 px-8 py-3
                        text-white font-semibold rounded-xl
                        bg-gradient-to-r from-green-500 to-green-600
                        hover:from-green-600 hover:to-green-700
                        shadow-lg shadow-green-500/30
                        transition-all duration-200 transform hover:scale-[1.02]
                        disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none
                      "
                    >
                      {processing ? (
                        <>
                          <svg className="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                          </svg>
                          Submitting...
                        </>
                      ) : (
                        <>
                          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                          </svg>
                          Complete Onboarding
                        </>
                      )}
                    </button>
                  )}
                </div>
              </div>
            </form>
          </div>
        </div>

        {/* Help Text */}
        <p className="text-center text-sm text-gray-400 mt-6">
          Need help? Contact us at <a href="mailto:support@trendpin.com" className="text-pink-500 hover:underline">support@trendpin.com</a>
        </p>
      </div>
    </div>
  );
}
