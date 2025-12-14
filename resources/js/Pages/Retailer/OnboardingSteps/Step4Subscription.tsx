import CardRetailerSubscriptionInformation from "../../../Components/Cards/CardRetailerSubscriptionInformation";

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
  // Transform database plans to match component format
  const subscriptions = plans.map((plan) => ({
    img: "/images/Frame 1000000724.png",
    header: plan.name,
    title: `${plan.price > 0 ? `$${plan.price}` : 'Free'} / ${plan.duration_months} month${plan.duration_months > 1 ? 's' : ''}`,
    description: plan.description || "No description available",
    offer: plan.trial_days ? `${plan.trial_days} Days Free Trial` : "No trial",
    validity: `Duration: ${plan.duration_months} month${plan.duration_months > 1 ? 's' : ''}`,
    value: String(plan.id), // Use plan ID as value
  }));

  return (
    <div className="space-y-8">
      <div className="text-center mb-4">
        <h3 className="text-[#152C5B] text-lg font-semibold">
          Retailer Subscription Information
        </h3>
      </div>

      <div className="w-full flex">
        <div className="mt-4 p-4 w-full grid grid-cols-1 sm:grid-cols-3 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-y-8 lg:gap-6">
          {subscriptions.map((sub) => (
            <div key={sub.value} className="w-full">
              <CardRetailerSubscriptionInformation
                img={sub.img}
                header={sub.header}
                title={sub.title}
                description={sub.description}
                offer={sub.offer}
                validity={sub.validity}
                name="subscription"
                checked={data.subscription === sub.value}
                onChange={() => onChange("subscription", sub.value)}
              />
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
