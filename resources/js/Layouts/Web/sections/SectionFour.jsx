export default function SectionFour() {
  return (
    <section className="w-full px-6 md:px-16 py-16 bg-white relative overflow-hidden">
      {/* Decorative dots */}
      <div className="absolute top-20 left-10 w-3 h-3 bg-yellow-400 rounded-full opacity-50"></div>
      <div className="absolute top-1/3 right-1/4 w-2 h-2 bg-yellow-400 rounded-full opacity-50"></div>
      <div className="absolute bottom-1/3 left-1/4 w-2 h-2 bg-red-400 rounded-full opacity-50"></div>
      <div className="absolute bottom-1/4 left-1/3 w-2.5 h-2.5 bg-yellow-400 rounded-full opacity-50"></div>

      <div className="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">

        {/* Left Side - Bank Logos with exact positioning from image */}
        <div className="relative flex justify-center lg:justify-start">
          <div className="relative w-[500px] h-[450px]">

            {/* Top Row - Alrajhi Bank (Blue logo with Arabic text) */}
            <div className="absolute top-0 left-1/2 -translate-x-1/2">
              <img
                src="/images/alrajhi-bank.png"
                alt="Alrajhi Bank"
                className="h-16 w-auto object-contain"
              />
            </div>

            {/* Second Row Left - Arab Bank (gray text logo) */}
            <div className="absolute top-24 left-12">
              <img
                src="/images/arab-bank.png"
                alt="Arab Bank"
                className="h-14 w-auto object-contain"
              />
            </div>

            {/* Second Row Right - ABC Bank (three blue circles) */}
            <div className="absolute top-20 right-12">
              <img
                src="/images/abc-bank.png"
                alt="ABC Bank"
                className="h-16 w-auto object-contain"
              />
            </div>

            {/* Middle Row Left - Capital Bank (dark blue text) */}
            <div className="absolute top-48 left-8">
              <img
                src="/images/capital-bank.png"
                alt="Capital Bank"
                className="h-11 w-auto object-contain"
              />
            </div>

            {/* Middle Row Center - Cairo Amman Bank (green logo) */}
            <div className="absolute top-44 left-1/2 -translate-x-1/2">
              <img
                src="/images/cairo-amman-bank.png"
                alt="Cairo Amman Bank"
                className="h-24 w-auto object-contain"
              />
            </div>

            {/* Bottom Row - NBK (blue logo with camel) */}
            <div className="absolute bottom-12 left-16">
              <img
                src="/images/nbk-bank.png"
                alt="NBK"
                className="h-16 w-auto object-contain"
              />
            </div>

          </div>
        </div>

        {/* Right Side - Content */}
        <div className="space-y-6 text-left">
          <h1 className="font-bold text-5xl md:text-6xl leading-tight">
            Enjoy <span className="text-[#E8347E]">10% OFF</span> with
            <br />
            participating bank credit
            <br />
            cards
          </h1>

          <p className="text-gray-600 text-lg leading-relaxed">
            We work with many leading banks. See the full list<br />
            of participating banks and eligible cards on our<br />
            Partner Banks page.
          </p>

          <div className="pt-2">
            <button className="bg-[#E8347E] hover:bg-[#d81b60] text-white rounded-full py-4 px-10 text-base font-semibold shadow-lg hover:shadow-xl transition-all duration-300">
              View All Banks
            </button>
          </div>
        </div>
      </div>
    </section>
  );
}
