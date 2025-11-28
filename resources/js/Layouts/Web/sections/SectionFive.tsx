export default function SectionFive() {
  return (
    <div className="w-full bg-[#1A1B380A] rounded-2xl opacity-100 py-14 px-6 md:px-16">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        
        {/* Left Content */}
        <div className="flex flex-col space-y-4">
          <span className="text-[#E8347E] text-sm md:text-base">DOWNLOAD APP</span>

          <h1 className="font-inter font-bold text-[32px] md:text-[46px] leading-[42px] md:leading-[60px]">
            Get Started With <br />
            Trendpin Today!
          </h1>

          <p className="text-gray-700">
            Discover food wherever and whenever and get <br className="hidden md:block" />
            your food delivered quickly.
          </p>

          <button className="w-fit bg-[#E8347E] text-white rounded-full py-3 px-6">
            Get The App
          </button>
        </div>

        {/* Right Image */}
        <div className="flex justify-center md:justify-end">
          <img
            src="/images/Group 83@2xz.PNG"
            alt="Trendpin App"
            className="w-3/4 md:w-full max-w-xs"
          />
        </div>

      </div>
    </div>
  );
}
