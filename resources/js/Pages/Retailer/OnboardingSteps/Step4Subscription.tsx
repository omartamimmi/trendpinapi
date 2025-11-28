import CardRetailerSubscriptionInformation from "../../../Components/Cards/CardRetailerSubscriptionInformation";

interface Step4Data {
  subscription: string;
}
interface Step4Props {
  data: Step4Data;
  onChange: <K extends keyof Step4Data>(
    field: K,
    value: Step4Data[K]
  ) => void;
}


export default function Step4Subscription({ data, onChange }: Step4Props) {
  const subscriptions = [
    {
      img: "/images/Frame 1000000724.png",
      header: "Trendpin Blue 35 Offers",
      title: "Per month",
      description:
        "Lorem ipsum dolor sit amet consectetur. Condimentum id semper lacinia dignissim at a condimentum.",
      offer: "3 Month Free",
      validity: "Membership valid until 10/08/2025",
      value: "blue35",
    },
    {
      img: "/images/Frame 1000000724.png",
      header: "Trendpin Green 20 Offers",
      title: "Per month",
      description:
        "Phasellus facilisis metus nec turpis consequat, non facilisis orci gravida.",
      offer: "1 Month Free",
      validity: "Membership valid until 05/09/2025",
      value: "green20",
    },
    {
      img: "/images/Frame 1000000724.png",
      header: "Trendpin Red 50 Offers",
      title: "Per month",
      description:
        "Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere.",
      offer: "6 Month Free",
      validity: "Membership valid until 12/12/2025",
      value: "red50",
    },
  ];

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
