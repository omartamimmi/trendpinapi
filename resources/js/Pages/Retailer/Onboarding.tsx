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
  };
  currentStep: number;
  plans: any[];
  csrf_token?: string;
}

export default function Onboarding({ onboarding, currentStep = 1, plans, csrf_token }: OnboardingProps) {
  const [activeStep, setActiveStep] = useState<StepId>(currentStep as StepId);
  const [processing, setProcessing] = useState(false);

  const steps = [
    { id: 1, title: "Retailer Details" },
    { id: 2, title: "Payment Details" },
    { id: 3, title: "Brand Information" },
    { id: 4, title: "Subscription" },
    { id: 5, title: "Payment" },
  ];

  const [formData, setFormData] = useState<FormDataType>({
    step1: {
      retailerName: "",
      phoneNumber: "",
      countryCode: "",
      category: "",
      logoFile: null,
      licenseFile: null
    },
    step2: {
      paymentMethod: [],
      bankName: "",
      iban: "",
      cliqNumber: "",
      countryCode: ""
    },
    step3: { groups: [] },
    step4: { subscription: "" },
    step5: { paymentOption: "" },
  });

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

  const handleNext = () => {
    if (activeStep < 5) {
      setActiveStep((prev) => (prev + 1) as StepId);
    }
  };

  const handleBack = () => {
    if (activeStep > 1) {
      setActiveStep((prev) => (prev - 1) as StepId);
    }
  };

  const handleSubmit = async (e?: FormEvent) => {
    if (e) e.preventDefault();

    // Validation
    if (!formData.step1.retailerName || !formData.step1.category) {
      alert('Please fill in all required fields in Step 1');
      setActiveStep(1);
      return;
    }

    if (!formData.step4.subscription) {
      alert('Please select a subscription plan');
      setActiveStep(4);
      return;
    }

    if (!formData.step5.paymentOption) {
      alert('Please select a payment option');
      return;
    }

    setProcessing(true);

    try {
      // Get CSRF token from props or meta tag
      const csrfToken = csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      if (!csrfToken) {
        throw new Error('CSRF token not found. Please refresh the page.');
      }

      // Prepare form data
      const submitData = new FormData();

      // Add CSRF token
      submitData.append('_token', csrfToken);

      // Step 1 data
      submitData.append('retailer_name', formData.step1.retailerName);
      submitData.append('category', formData.step1.category);
      submitData.append('phone_number', formData.step1.phoneNumber || '');
      submitData.append('country_code', formData.step1.countryCode || '');
      if (formData.step1.logoFile) submitData.append('logo', formData.step1.logoFile);
      if (formData.step1.licenseFile) submitData.append('license', formData.step1.licenseFile);

      // Step 2 data
      submitData.append('payment_methods', JSON.stringify(formData.step2.paymentMethod || []));
      submitData.append('bank_name', formData.step2.bankName || '');
      submitData.append('iban', formData.step2.iban || '');
      submitData.append('cliq_number', formData.step2.cliqNumber || '');

      // Step 3 data
      submitData.append('brands', JSON.stringify(formData.step3.groups || []));

      // Step 4 data
      submitData.append('subscription_plan', formData.step4.subscription);

      // Step 5 data
      submitData.append('payment_option', formData.step5.paymentOption);

      // Submit to backend
      router.post('/retailer/onboarding/complete', submitData, {
        onSuccess: () => {
          // The backend will redirect automatically
          setProcessing(false);
        },
        onError: (errors) => {
          console.error('Onboarding submission error:', errors);
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

            <div
              className={`z-10 flex items-center justify-center w-10 h-10 rounded-full border transition-all duration-300 ${
                isCompleted
                  ? "bg-pink-500 text-white border-pink-500"
                  : isActive
                  ? "bg-[#2F305A] text-white border-[#2F305A]"
                  : "bg-gray-100 border-gray-300 text-gray-500"
              }`}
            >
              {isCompleted ? <FaCheck /> : step.id}
            </div>

            <span
              className={`mt-2 text-xs text-center ${
                isCompleted
                  ? "text-pink-500"
                  : isActive
                  ? "text-[#2F305A]"
                  : "text-gray-400"
              }`}
            >
              {step.title}
            </span>
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
