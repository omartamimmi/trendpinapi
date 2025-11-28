export default function SectionThree() {
  return (
<div className="p-10">
  <div className="grid grid-cols-2 gap-3">
      <div>
        <img src="/images/Group 83@2xz.PNG" alt="" className="mask-radial-farthest-corner mask-radial-from-100% mask-radial-at-[30%_30%] w-3/4" />
    </div>
    <div className="flex flex-col items-left justify-center space-y-4">
      <div>
        <h1 className="font-inter font-bold text-[46px] leading-[60px]">
         Enjoy <span className="text-[#E8347E]">10% OFF</span>  with
         <br/>
          participating bank credit
          <br/>
           cards
        </h1>
      </div>
      <div>
        <p>
       We work with many leading banks. See the full list<br/> of participating banks and eligible cards on our<br/> Partner Banks page.
        </p>
      </div>
      <div>
<button className="w-3/10 h-4/3 bg-[#E8347E] text-white items-center justify-center rounded-[50px] opacity-100 flex justify-between py-[11px] px-[20px]">
         View All Banks
          </button>
      </div>
    </div>

  
  </div>
</div>

  );
}
