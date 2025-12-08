export default function SectionTwo() {
  const features = [
    {
      id: 1,
      title: "Browse Your Restaurants",
      subtitle: "& Claim",
      highlight: "Offers Directly",
      description: "Fast and easy reviewing between the stores and restaurants.",
      image: "/images/003f3b06c6e6d570ba46667bbcf5774d580a7903 (1).png",
      bgColor: "bg-blue-50",
      iconBg: "bg-white",
    },
    {
      id: 2,
      title: "Browse Nearest Offers",
      subtitle: "Around Your",
      highlight: "Location",
      description: "Select your location to explore nearby stores and discover your offers.",
      image: "/images/2a6b40297addec9f7a91bea5f6a17f209c7ff5cc.png",
      bgColor: "bg-blue-50",
      iconBg: "bg-white",
    },
    {
      id: 3,
      title: "Save More & Enjoy Buy 1 Get",
      subtitle: "",
      highlight: "1 Free",
      description: "Choose your bank to discover exclusive discounts available at your favorite store.",
      image: "/images/5b4db1cb2ce811507965803f8332891318711269.png",
      bgColor: "bg-pink-100",
      iconBg: "bg-[#E8347E]",
    },
    {
      id: 4,
      title: "Use Your Credit Card Bank",
      subtitle: "And Get",
      highlight: "10% Discounts",
      description: "Browse your favorite stores to discover the offers available to you.",
      image: "/images/55c7effa34c2fd8ad9838be4d95ca959087f2580.png",
      bgColor: "bg-gray-50",
      iconBg: "bg-white",
    },
  ];

  return (
    <div className="w-full py-16 px-6 md:px-16 bg-white relative overflow-hidden">
      {/* Decorative elements */}
      <div className="absolute top-10 right-20 w-3 h-3 bg-pink-400 rounded-full"></div>
      <div className="absolute top-32 left-10 w-2 h-2 bg-gray-800 rounded-full"></div>
      <div className="absolute bottom-20 right-32 w-3 h-3 bg-pink-400 rounded-full"></div>
      <div className="absolute bottom-40 left-20 w-2 h-2 bg-pink-300 rounded-full"></div>

      {/* Title Section */}
      <div className="text-center mb-16 relative">
        <p className="text-[#E8347E] font-semibold text-sm md:text-base tracking-wider mb-4">
          WHAT WE DO
        </p>
        <h2 className="font-bold text-3xl md:text-5xl lg:text-[52px] leading-tight">
          Your Favourites <span className="text-[#E8347E]">Restaurants</span> &{" "}
          <br className="hidden md:block" />
          <span className="text-[#E8347E]">Stores</span> with best offers
        </h2>
      </div>

      {/* Cards Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 max-w-7xl mx-auto">
        {features.map((feature) => (
          <div
            key={feature.id}
            className="group text-center p-6 transition-all duration-300 hover:-translate-y-2"
          >
            {/* Image Container with Background */}
            <div className="relative mb-6 inline-block">
              <div
                className={`w-40 h-40 ${feature.bgColor} rounded-full flex items-center justify-center mx-auto shadow-lg`}
              >
                <div
                  className={`w-32 h-32 ${feature.iconBg} rounded-full overflow-hidden shadow-md flex items-center justify-center p-4`}
                >
                  <img
                    src={feature.image}
                    alt={feature.title}
                    className="w-full h-full object-contain"
                  />
                </div>
              </div>

              {/* Decorative ring */}
              <div className="absolute inset-0 w-40 h-40 border-2 border-pink-200 rounded-full opacity-0 group-hover:opacity-100 group-hover:scale-110 transition-all duration-300 mx-auto"></div>
            </div>

            {/* Text Content */}
            <h3 className="font-bold text-lg md:text-xl leading-snug mb-3">
              {feature.title}{" "}
              {feature.subtitle && (
                <>
                  <br />
                  {feature.subtitle}{" "}
                </>
              )}
              <span className="text-[#E8347E]">{feature.highlight}</span>
            </h3>

            <p className="text-gray-600 text-sm leading-relaxed">
              {feature.description}
            </p>
          </div>
        ))}
      </div>
    </div>
  );
}
