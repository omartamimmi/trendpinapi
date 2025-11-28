export default function SectionFour() {
  return (
    <div className="w-full px-6 py-16 bg-white">
      <div className="max-w-screen-xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        
        {/* Left Image */}
        <div className="flex justify-center md:justify-start">
          <img
            src="/images/Group 83@2xz.PNG"
            alt="Bank Discount"
            className="w-full max-w-[400px] md:max-w-none rounded-full"
          />
        </div>

        {/* Right Content */}
        <div className="space-y-6">
          <h1 className="font-inter font-bold text-[36px] md:text-[46px] leading-tight">
            Enjoy <span className="text-[#E8347E]">10% OFF</span> with
            <br />
            participating bank credit
            <br />
            cards
          </h1>
          <p className="text-gray-600">
            We work with many leading banks. See the full list of participating
            banks and eligible cards on our Partner Banks page.
          </p>
          <button className="bg-[#E8347E] text-white rounded-full py-2 px-6 hover:bg-[#d12e70] transition">
            View All Banks
          </button>
        </div>
      </div>
    </div>
  );
}
