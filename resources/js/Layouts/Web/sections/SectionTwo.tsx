export default function SectionTwo() {
  return (
    <div className="w-full py-16 px-6 md:px-16">
      {/* Title Section */}
      <div className="text-center mb-12">
        <p className="text-[#E8347E] font-medium">WHAT WE DO</p>
        <h2 className="font-inter font-bold text-[32px] md:text-[46px] leading-[42px] md:leading-[60px]">
          Your Favourite <span className="text-[#E8347E]">Restaurants</span> &{" "}
          <br className="hidden md:block" />
          <span className="text-[#E8347E]">Stores</span> with Best Offers
        </h2>
      </div>

      {/* Cards Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
        
        {/* Card 1 */}
        <div className="text-center p-5">
          <img
            src="/images/003f3b06c6e6d570ba46667bbcf5774d580a7903 (1).png"
            alt="Browse Restaurants"
            className="w-32 h-32 mx-auto rounded-full object-cover"
          />
          <h3 className="mt-4 font-inter font-bold text-lg">
            Browse Your Restaurants <br /> & Claim{" "}
            <span className="text-[#E8347E]">Offers Directly</span>
          </h3>
          <p className="mt-2 text-gray-600">
            Fast and easy reviewing between the stores and restaurants.
          </p>
        </div>

        {/* Card 2 */}
        <div className="text-center p-5">
          <img
            src="/images/2a6b40297addec9f7a91bea5f6a17f209c7ff5cc.png"
            alt="Nearest Offers"
            className="w-32 h-32 mx-auto rounded-full object-cover"
          />
          <h3 className="mt-4 font-inter font-bold text-lg">
            Browse nearest offers <br /> around your{" "}
            <span className="text-[#E8347E]">Location</span>
          </h3>
          <p className="mt-2 text-gray-600">
            Select your location to explore nearby stores and discover your offers.
          </p>
        </div>

        {/* Card 3 */}
        <div className="text-center p-5">
          <img
            src="/images/5b4db1cb2ce811507965803f8332891318711269.png"
            alt="Buy 1 Get 1"
            className="w-32 h-32 mx-auto rounded-full object-cover"
          />
          <h3 className="mt-4 font-inter font-bold text-lg">
            Save more & Enjoy <br /> buy 1 get{" "}
            <span className="text-[#E8347E]">1 free</span>
          </h3>
          <p className="mt-2 text-gray-600">
            Choose your bank to discover exclusive discounts available at your favorite stores.
          </p>
        </div>

        {/* Card 4 */}
        <div className="text-center p-5">
          <img
            src="/images/55c7effa34c2fd8ad9838be4d95ca959087f2580.png"
            alt="10% Off"
            className="w-32 h-32 mx-auto rounded-full object-cover"
          />
          <h3 className="mt-4 font-inter font-bold text-lg">
            Use your credit card <br /> and get{" "}
            <span className="text-[#E8347E]">10% discounts</span>
          </h3>
          <p className="mt-2 text-gray-600">
            Browse your favorite stores to discover available offers.
          </p>
        </div>
      </div>
    </div>
  );
}
