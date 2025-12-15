interface Step4Data {
  subscription: string;
}

interface Plan {
  id: number;
  name: string;
  description?: string;
  price: number;
  duration_months: number;
  trial_days?: number;
  features?: any;
}

interface Step4Props {
  data: Step4Data;
  onChange: <K extends keyof Step4Data>(
    field: K,
    value: Step4Data[K]
  ) => void;
  plans: Plan[];
}

export default function Step4Subscription({ data, onChange, plans = [] }: Step4Props) {
  // Parse features if it's a string
  const parseFeatures = (features: any): string[] => {
    if (!features) return [];
    if (Array.isArray(features)) return features;
    if (typeof features === 'string') {
      try {
        const parsed = JSON.parse(features);
        return Array.isArray(parsed) ? parsed : [];
      } catch {
        return features.split(',').map((f: string) => f.trim());
      }
    }
    return [];
  };

  // Get popular/recommended plan (middle one or first if only one)
  const popularPlanId = plans.length > 1
    ? plans[Math.floor(plans.length / 2)]?.id
    : plans[0]?.id;

  return (
    <div className="space-y-8">
      {/* Header Section */}
      <div className="text-center">
        <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 mb-4">
          <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
          </svg>
        </div>
        <h3 className="text-2xl font-bold text-gray-800">Choose Your Plan</h3>
        <p className="text-gray-500 mt-2 max-w-lg mx-auto">
          Select the subscription plan that best fits your business needs. All plans include access to our core features.
        </p>
      </div>

      {/* Plans Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {plans.map((plan) => {
          const isSelected = data.subscription === String(plan.id);
          const isPopular = plan.id === popularPlanId;
          const features = parseFeatures(plan.features);

          return (
            <div
              key={plan.id}
              onClick={() => onChange("subscription", String(plan.id))}
              className={`relative bg-white rounded-2xl overflow-hidden cursor-pointer transition-all duration-300 transform hover:scale-[1.02] ${
                isSelected
                  ? "ring-2 ring-pink-500 shadow-xl shadow-pink-500/20"
                  : "border border-gray-200 shadow-lg hover:shadow-xl"
              } ${isPopular && !isSelected ? "ring-1 ring-purple-300" : ""}`}
            >
              {/* Popular Badge */}
              {isPopular && (
                <div className="absolute top-0 right-0">
                  <div className="bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs font-bold px-4 py-1.5 rounded-bl-xl">
                    POPULAR
                  </div>
                </div>
              )}

              {/* Selected Check */}
              {isSelected && (
                <div className="absolute top-4 left-4">
                  <div className="w-8 h-8 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full flex items-center justify-center">
                    <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                    </svg>
                  </div>
                </div>
              )}

              {/* Card Content */}
              <div className="p-6 pt-10">
                {/* Plan Icon */}
                <div className={`w-14 h-14 rounded-xl flex items-center justify-center mb-4 ${
                  isSelected
                    ? "bg-gradient-to-br from-pink-500 to-purple-600"
                    : "bg-gray-100"
                }`}>
                  <svg
                    className={`w-7 h-7 ${isSelected ? "text-white" : "text-gray-500"}`}
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>

                {/* Plan Name */}
                <h4 className={`text-xl font-bold mb-2 ${isSelected ? "text-pink-600" : "text-gray-800"}`}>
                  {plan.name}
                </h4>

                {/* Price */}
                <div className="mb-4">
                  <div className="flex items-baseline">
                    <span className={`text-4xl font-bold ${isSelected ? "text-pink-600" : "text-gray-900"}`}>
                      {plan.price > 0 ? `$${plan.price}` : 'Free'}
                    </span>
                    {plan.price > 0 && (
                      <span className="text-gray-500 ml-2">
                        / {plan.duration_months} {plan.duration_months > 1 ? 'months' : 'month'}
                      </span>
                    )}
                  </div>
                </div>

                {/* Trial Badge */}
                {plan.trial_days && plan.trial_days > 0 && (
                  <div className="inline-flex items-center px-3 py-1.5 bg-green-50 text-green-700 rounded-lg text-sm font-medium mb-4">
                    <svg className="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {plan.trial_days} Days Free Trial
                  </div>
                )}

                {/* Description */}
                {plan.description && (
                  <p className="text-gray-500 text-sm mb-4 line-clamp-2">
                    {plan.description}
                  </p>
                )}

                {/* Features */}
                {features.length > 0 && (
                  <div className="space-y-3 mt-4 pt-4 border-t border-gray-100">
                    {features.slice(0, 5).map((feature, idx) => (
                      <div key={idx} className="flex items-start">
                        <div className={`flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center mr-3 ${
                          isSelected ? "bg-pink-100" : "bg-gray-100"
                        }`}>
                          <svg
                            className={`w-3 h-3 ${isSelected ? "text-pink-600" : "text-gray-500"}`}
                            fill="currentColor"
                            viewBox="0 0 20 20"
                          >
                            <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                          </svg>
                        </div>
                        <span className="text-sm text-gray-600">{feature}</span>
                      </div>
                    ))}
                    {features.length > 5 && (
                      <p className="text-sm text-gray-400 pl-8">
                        +{features.length - 5} more features
                      </p>
                    )}
                  </div>
                )}

                {/* Duration Info */}
                <div className="mt-4 pt-4 border-t border-gray-100">
                  <div className="flex items-center text-sm text-gray-500">
                    <svg className="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Duration: {plan.duration_months} {plan.duration_months > 1 ? 'months' : 'month'}
                  </div>
                </div>
              </div>

              {/* Select Button */}
              <div className="px-6 pb-6">
                <button
                  type="button"
                  className={`w-full py-3 rounded-xl font-semibold transition-all duration-200 ${
                    isSelected
                      ? "bg-gradient-to-r from-pink-500 to-purple-600 text-white shadow-lg shadow-pink-500/25"
                      : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                  }`}
                >
                  {isSelected ? (
                    <span className="flex items-center justify-center">
                      <svg className="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                      </svg>
                      Selected
                    </span>
                  ) : (
                    "Select Plan"
                  )}
                </button>
              </div>
            </div>
          );
        })}
      </div>

      {/* No Plans Message */}
      {plans.length === 0 && (
        <div className="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
          <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
            <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>
          </div>
          <h4 className="text-lg font-semibold text-gray-700 mb-2">No Plans Available</h4>
          <p className="text-gray-500">Subscription plans are currently unavailable. Please try again later.</p>
        </div>
      )}

      {/* Selection Info */}
      {!data.subscription && plans.length > 0 && (
        <div className="bg-amber-50 border border-amber-200 rounded-xl p-4">
          <div className="flex items-start space-x-3">
            <div className="flex-shrink-0">
              <svg className="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
              </svg>
            </div>
            <div>
              <h4 className="text-sm font-medium text-amber-800">Plan Selection Required</h4>
              <p className="text-sm text-amber-600 mt-1">Please select a subscription plan to continue with the onboarding process.</p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
