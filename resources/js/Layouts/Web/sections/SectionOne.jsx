export default function SectionOne() {
  return (
    <div className="w-full py-12 px-6 md:px-16 bg-white relative overflow-hidden">
      {/* Decorative background elements */}
      <div className="absolute top-20 right-10 w-3 h-3 bg-pink-300 rounded-full opacity-40"></div>
      <div className="absolute bottom-40 left-20 w-2 h-2 bg-gray-800 rounded-full opacity-30"></div>

      <div className="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        {/* Left Section - MEDIUM SIZE */}
        <div className="space-y-6 text-left relative z-10">
          <h1 className="font-bold text-4xl md:text-5xl lg:text-6xl leading-tight">
            Claim Best Offer
            <span className="inline-block w-2.5 h-2.5 rounded-full bg-[#F2C94C] ml-2 mb-1.5"></span>
            <br />
            on Fast <span className="text-[#E8347E] italic">Food</span> &
            <br />
            <span className="text-[#E8347E] italic">Restaurants</span>
          </h1>

          <p className="text-gray-600 text-base md:text-lg leading-relaxed">
            Our job is to filling your tummy with delicious food
            <br />
            and with fast and free delivery
          </p>

          <button className="bg-[#E8347E] hover:bg-[#d81b60] text-white rounded-full py-3.5 px-7 text-base font-medium shadow-lg transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1">
            Download App
          </button>

          {/* Customer Reviews Section - MEDIUM SIZE */}
          <div className="flex items-center mt-6 bg-white rounded-xl p-3.5 shadow-md max-w-sm">
            <div className="flex -space-x-2.5">
              <img
                src="/images/customer1.jpg"
                alt="Customer"
                className="w-10 h-10 rounded-full border-2 border-white object-cover"
              />
              <img
                src="/images/customer2.jpg"
                alt="Customer"
                className="w-10 h-10 rounded-full border-2 border-white object-cover"
              />
              <img
                src="/images/customer3.jpg"
                alt="Customer"
                className="w-10 h-10 rounded-full border-2 border-white object-cover"
              />
            </div>
            <div className="ml-3.5">
              <p className="text-sm font-bold text-gray-900">Our Happy Customer</p>
              <div className="flex items-center">
                <svg className="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span className="text-sm font-semibold text-gray-700 ml-1.5">
                  4.8 <span className="text-gray-500 font-normal">(12.5k Review)</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Right Section - MEDIUM Image with floating elements */}
        <div className="relative flex justify-center items-center lg:justify-end">
          {/* Main circular image container - MEDIUM SIZE */}
          <div className="relative w-[320px] h-[320px] md:w-[400px] md:h-[400px] lg:w-[450px] lg:h-[450px]">
            {/* Background gradient circle */}
            <div className="absolute inset-0 bg-gradient-to-br from-pink-100 to-blue-50 rounded-full opacity-50 blur-2xl"></div>

            {/* Main image circle */}
            <div className="relative w-full h-full rounded-full overflow-hidden shadow-2xl">
              <img
                src="/images/374cc0c425409aef266cb83aa9f18c6c05e7fa0f (2)copy.PNG"
                alt="Payment terminal with food delivery"
                className="w-full h-full object-cover"
              />
            </div>

            {/* Floating Icon - Top Left (Clock/Time) - MEDIUM */}
            <div className="absolute -top-5 -left-5 lg:-top-6 lg:-left-6 bg-[#E8347E] rounded-xl p-2.5 lg:p-3.5 shadow-xl animate-float">
              <img
                src="/images/Frame 29.png"
                alt="Fast delivery"
                className="w-9 h-9 lg:w-11 lg:h-11"
              />
            </div>

            {/* Floating Icon - Top Right (Cursor/Click) - MEDIUM */}
            <div className="absolute -top-3.5 -right-3.5 lg:-top-5 lg:-right-5 animate-float-delayed">
              <img
                src="/images/Group 53.png"
                alt="Easy ordering"
                className="w-12 h-12 lg:w-14 lg:h-14 drop-shadow-lg"
              />
            </div>

            {/* Floating Card - Bottom Right (Pizza offer) - MEDIUM */}
            <div className="absolute -bottom-7 -right-7 lg:-bottom-9 lg:-right-9 bg-white rounded-xl shadow-xl p-2.5 lg:p-3.5 animate-float">
              <img
                src="/images/Frame 10.png"
                alt="Italian Pizza offer"
                className="w-24 h-24 lg:w-28 lg:h-28"
              />
            </div>

            {/* Small decorative dot - Bottom Left - MEDIUM */}
            <div className="absolute bottom-7 left-7 lg:bottom-9 lg:left-9">
              <img
                src="/images/Rectangle 8.png"
                alt=""
                className="w-2.5 h-2.5 lg:w-3.5 lg:h-3.5 opacity-70"
              />
            </div>

            {/* Additional decorative elements - MEDIUM */}
            <div className="absolute top-1/4 -left-3.5 w-7 h-7 bg-yellow-400 rounded-full opacity-20 blur-sm"></div>
            <div className="absolute bottom-1/4 -right-3.5 w-5.5 h-5.5 bg-pink-400 rounded-full opacity-20 blur-sm"></div>
          </div>
        </div>
      </div>

      {/* Custom animations */}
      <style jsx>{`
        @keyframes float {
          0%, 100% {
            transform: translateY(0px);
          }
          50% {
            transform: translateY(-18px);
          }
        }

        .animate-float {
          animation: float 3s ease-in-out infinite;
        }

        .animate-float-delayed {
          animation: float 3s ease-in-out infinite;
          animation-delay: 1s;
        }
      `}</style>
    </div>
  );
}
