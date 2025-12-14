import { useState, FormEvent } from "react";
import { router } from "@inertiajs/react";
import { FaCheck, FaChevronDown } from "react-icons/fa";
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
    { id: 1, title: "Retailer Details" },
    { id: 2, title: "Payment Details" },
    { id: 3, title: "Brand Information" },
    { id: 4, title: "Subscription" },
    { id: 5, title: "Payment" },
  ];

  // Initialize form data with existing data
  const initializeFormData = (): FormDataType => {
    // Step 1: Retailer Details from onboarding table and user
    const step1 = {
      retailerName: user?.name || "",
      phoneNumber: user?.phone || "",
      countryCode: "", // Extract from phone if needed
      city: onboarding.city || "",
      category: onboarding.category || "",
      logoFile: null, // Files can't be pre-filled, but we'll show preview
      licenseFile: null
    };

    // Step 2: Payment Methods
    const step2 = {
      paymentMethod: existingPaymentMethods.length > 0
        ? existingPaymentMethods.map(pm => pm.type)
        : [],
      bankName: existingPaymentMethods.find(pm => pm.type === 'bank')?.bank_name || "",
      iban: existingPaymentMethods.find(pm => pm.type === 'bank')?.iban || "",
      cliqNumber: existingPaymentMethods.find(pm => pm.type === 'cliq')?.cliq_number || "",
      countryCode: ""
    };

    // Step 3: Brands
    const step3 = {
      groups: existingBrands.map(brand => ({
        brandName: brand.name,
        description: brand.description || "",
        position: brand.location
          ? (typeof brand.location === 'string'
              ? JSON.parse(brand.location)
              : brand.location)
          : { lat: 0, lng: 0 }
      }))
    };

    // Step 4: Subscription
    const step4 = {
      subscription: existingSubscription?.subscription_plan_id?.toString() || ""
    };

    // Step 5: Payment
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
        // Step 1: Save retailer details
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
            console.log('Step 1 data saved successfully');
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
        // Step 2: Save payment methods - format data as array
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

        // Skip if no payment methods selected
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
            console.log('Step 2 data saved successfully');
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
        // Step 3: Save brand information
        const groups = formData.step3.groups || [];

        // Skip if no brands added
        if (groups.length === 0) {
          setProcessing(false);
          setActiveStep(4);
          return;
        }

        // Transform data to match backend expectations
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
            console.log('Step 3 data saved successfully');
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
        // Step 4: Save subscription plan (optional for now)
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
            console.log('Step 4 data saved successfully');
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

    // Since all steps are already saved, just mark as complete
    setProcessing(true);

    try {
      // Simply mark the onboarding as completed
      const submitData: any = {
        payment_option: formData.step5.paymentOption || 'pending',
      };

      // Add onboarding_user_id for admin edits
      if (isAdminEdit && onboarding?.user_id) {
        submitData.onboarding_user_id = onboarding.user_id;
      }

      // Submit to backend to mark as complete
      router.post('/retailer/onboarding/complete', submitData, {
        preserveState: !isAdminEdit,
        onSuccess: () => {
          setProcessing(false);
          // Backend will redirect to pending approval page
        },
        onError: (errors) => {
          console.error('Onboarding completion error:', errors);
          setProcessing(false);

          // Display validation errors
          if (errors && typeof errors === 'object') {
            const errorMessages = Object.values(errors).flat();
            if (errorMessages.length > 0) {
              alert('Validation errors:\n' + errorMessages.join('\n'));
              return;
            }
          }

          alert('Failed to submit onboarding. Please check all fields and try again.');
        },
        forceFormData: true,
        preserveState: false,
        preserveScroll: false
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

  const Stepper = () => (
    <div className="relative flex justify-between items-center mb-10 w-full max-w-4xl mx-auto">
      {steps.map((step, index) => {
        const isActive = step.id === activeStep;
        const isCompleted = step.id < activeStep;
        const isLast = index === steps.length - 1;

        return (
          <div key={step.id} className="relative flex-1 flex flex-col items-center">
            {isActive && (
              <FaChevronDown className="absolute -top-6 text-[#2F305A]" />
            )}

            {!isLast && (
              <div
                className={`absolute top-5 left-1/2 w-full h-[2px] -translate-y-1/2 ${
                  isCompleted ? "bg-pink-500" : "bg-gray-300"
                }`}
              ></div>
            )}

            <button
              type="button"
              onClick={() => setActiveStep(step.id)}
              className={`z-10 flex items-center justify-center w-10 h-10 rounded-full border transition-all duration-300 cursor-pointer hover:scale-110 ${
                isCompleted
                  ? "bg-pink-500 text-white border-pink-500 hover:bg-pink-600"
                  : isActive
                  ? "bg-[#2F305A] text-white border-[#2F305A]"
                  : "bg-gray-100 border-gray-300 text-gray-500 hover:bg-gray-200"
              }`}
            >
              {isCompleted ? <FaCheck /> : step.id}
            </button>

            <button
              type="button"
              onClick={() => setActiveStep(step.id)}
              className={`mt-2 text-xs text-center cursor-pointer hover:underline ${
                isCompleted
                  ? "text-pink-500"
                  : isActive
                  ? "text-[#2F305A]"
                  : "text-gray-400"
              }`}
            >
              {step.title}
            </button>
          </div>
        );
      })}
    </div>
  );

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="p-8 max-w-6xl mx-auto bg-white shadow rounded-lg">
        <Stepper />

        <form onSubmit={handleSubmit}>
          {renderStep()}

          <div className="flex justify-between mt-10">
            {activeStep > 1 && (
              <button
                type="button"
                onClick={handleBack}
                className="px-6 py-2 bg-gray-300 rounded hover:bg-gray-400 transition"
                disabled={processing}
              >
                Back
              </button>
            )}
            {activeStep < 5 ? (
              <button
                type="button"
                onClick={handleNext}
                className="px-6 py-2 bg-[#E8347E] text-white rounded hover:bg-[#d12e6e] transition ml-auto"
                disabled={processing}
              >
                Next
              </button>
            ) : (
              <button
                type="submit"
                className="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition ml-auto disabled:opacity-50"
                disabled={processing}
              >
                {processing ? 'Submitting...' : 'Submit All'}
              </button>
            )}
          </div>
        </form>
      </div>
    </div>
  );
}
