export default function SectionFive() {
  return (
    <div className="w-full max-w-7xl mx-auto px-4 md:px-8 py-12 md:py-16">
      <div className="relative bg-gradient-to-br from-[#F8F4FF] to-[#FFF0F8] rounded-3xl overflow-hidden">
        <div className="grid md:grid-cols-2 items-center">

          {/* Left Content */}
          <div className="flex flex-col space-y-6 px-6 md:px-12 py-12 md:py-16 z-10">
            <span className="text-[#E8347E] text-sm md:text-base font-semibold tracking-wider">
              DOWNLOAD APP
            </span>

            <h1 className="font-bold text-4xl md:text-5xl lg:text-[56px] leading-tight">
              Get Started With <br />
              Trendpin Today!
            </h1>

            <p className="text-gray-600 text-base md:text-lg leading-relaxed">
              Discover food wherever and whenever and get <br className="hidden md:block" />
              your food delivered quickly.
            </p>

            <button className="w-fit bg-[#E8347E] hover:bg-[#d12e6f] transition-colors text-white rounded-full py-4 px-8 font-medium shadow-lg">
              Get The App
            </button>
          </div>

          {/* Right Image - Full without padding */}
          <div className="relative h-full flex items-center justify-center md:justify-end">
            <img
              src="/images/Group 83@2xz.PNG"
              alt="Trendpin App"
              className="w-full h-full object-contain md:object-cover"
            />

            {/* Decorative Elements */}
            <div className="absolute top-1/4 left-10 text-2xl">😋</div>
            <div className="absolute bottom-32 left-20">
              <svg width="30" height="30" viewBox="0 0 30 30" fill="none">
                <path d="M15 0L16.5 13.5L30 15L16.5 16.5L15 30L13.5 16.5L0 15L13.5 13.5L15 0Z" fill="#E8347E" opacity="0.4"/>
              </svg>
            </div>
            <div className="absolute top-16 left-32">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 0L13.2 10.8L24 12L13.2 13.2L12 24L10.8 13.2L0 12L10.8 10.8L12 0Z" fill="#FF6B9D" opacity="0.5"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
