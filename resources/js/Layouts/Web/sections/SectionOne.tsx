export default function SectionOne() {
  return (
    <div className="p-6 md:p-10">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        {/* Left Section */}
        <div className="space-y-4 text-center md:text-left">
          <h1 className="font-inter font-bold text-4xl md:text-5xl leading-tight">
            Claim Best Offer{" "}
            <span className="inline-block w-2 h-2 rounded-full bg-[#F2C94C]"></span>{" "}
            <br /> on Fast
            <span className="text-[#E8347E]">Food</span> & <br />
            <span className="text-[#E8347E]">Restaurants</span>
          </h1>

          <p className="text-gray-600">
            Our job is to fill your tummy with delicious food <br />
            and deliver it fast — for free!
          </p>

          <button className="bg-[#E8347E] text-white rounded-full py-3 px-6">
            Download App
          </button>
        </div>

        <div className="flex justify-center md:justify-end relative">
          <div className="relative">
            {/* Main Image */}
            <img
              src="/images/374cc0c425409aef266cb83aa9f18c6c05e7fa0f (2)copy.PNG"
              alt="Rounded Fast Food"
              className="w-52 h-52 sm:w-64 sm:h-64 md:w-80 md:h-80 lg:w-96 lg:h-96 rounded-full object-cover"
            />

            {/* Floating Icon - Top Left */}
            <img
              src="/images/Frame 29.png"
              alt=""
              className="absolute  w-12 top-0 left-0 sm:w-16 sm:top-2 sm:left-2 md:w-20 md:top-0 md:left-0"
            />

            {/* Floating Icon - Top Right */}
            <img
              src="/images/Group 53.png"
              alt=""
              className="absolute w-10 top-3 right-4  sm:w-12 sm:top-2 sm:right-6  md:w-14 md:top-0 md:right-8  lg:w-16 lg:top-0 lg:right-10"
            />

            {/* Floating Icon - Bottom Right */}
            <img
              src="/images/Frame 10.png"
              alt=""
              className="
        absolute
        w-28
        sm:w-36
        md:w-40
        -bottom-8 -right-4
        sm:-bottom-10 sm:-right-6
      "
            />

            {/* Small Dot - Bottom Left */}
            <img
              src="/images/Rectangle 8.png"
              alt=""
              className="
        absolute 
        w-3 h-3 
        sm:w-4 sm:h-4
        bottom-4 left-4
        sm:bottom-6 sm:left-6
        md:bottom-8 md:left-10
      "
            />
          </div>
        </div>
      </div>
    </div>
  );
}
