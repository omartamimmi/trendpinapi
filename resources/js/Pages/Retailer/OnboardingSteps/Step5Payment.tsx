import { useMemo } from "react";
import CardRetailerSubscriptionInformation from "../../../Components/Cards/CardRetailerSubscriptionInformation";

interface Step5Data {
  paymentOption: "" | "cliq" | "cash" | "card";
}

interface Step5Props {
  data: Step5Data;
  onChange: <K extends keyof Step5Data>(
    field: K,
    value: Step5Data[K]
  ) => void;
}


export default function Step5Payment({ data, onChange }: Step5Props) {
  const selectedMethod = useMemo(() => data.paymentOption, [data.paymentOption]);

  return (
    <div className="space-y-8">
      <div>
        <h3 className="text-[#152C5B] text-center text-lg font-semibold">
          Payment
        </h3>
      </div>

      <div className="space-y-8 w-full flex justify-center">
        <div className="flex justify-center w-full sm:w-full md:w-3/4 lg:w-2/3 xl:w-1/3">
          <div className="mt-2 p-4 w-full">
            <div className="border border-[#E0E0EC] rounded-lg text-center p-4">
              <span className="font-medium text-sm">Payment Summary</span>

              {/* Summary Card */}
              <div className="w-full p-4">
                <CardRetailerSubscriptionInformation
                  img="/images/Frame 1000000724.png"
                  header="Trendpin Blue 35 Offers"
                  title="Per month"
                  description="Lorem ipsum dolor sit amet consectetur. Condimentum id semper lacinia dignissim at a condimentum."
                  offer="500.00 JD"
                  validity="Membership valid until 10/08/2025"
                  name="subscription"
                  checked={true}
                />
              </div>

              {/* Payment Method Buttons */}
              <div className="p-2 w-full lg:grid lg:grid-cols-2 lg:gap-4 space-y-3 lg:space-y-0">
                {/* Cash */}
                <div className="border flex justify-between items-center p-2 rounded-lg border-[#EEEEEE]">
                  <label htmlFor="cash" className="text-xs">
                    Cash Payment
                  </label>
                  <input
                    id="cash"
                    type="radio"
                    name="payment_method"
                    checked={selectedMethod === "cash"}
                    onChange={() => onChange("paymentOption", "cash")}
                  />
                </div>

                {/* Card */}
                <div className="border flex justify-between items-center p-2 rounded-lg border-[#EEEEEE]">
                  <label htmlFor="card" className="text-xs">
                    Card Payment
                  </label>
                  <input
                    id="card"
                    type="radio"
                    name="payment_method"
                    checked={selectedMethod === "card"}
                    onChange={() => onChange("paymentOption", "card")}
                  />
                </div>

                {/* Cliq */}
                <div className="border flex justify-between items-center p-2 rounded-lg border-[#EEEEEE]">
                  <label htmlFor="cliq" className="text-xs">
                    Cliq Payment
                  </label>
                  <input
                    id="cliq"
                    type="radio"
                    name="payment_method"
                    checked={selectedMethod === "cliq"}
                    onChange={() => onChange("paymentOption", "cliq")}
                  />
                </div>
              </div>

              {/* Conditional content */}
              {selectedMethod && (
                <div className="m-2 mt-4 text-left">
                  {selectedMethod === "cash" && (
                    <div>
                      <div className="flex justify-between">
                        <span className="text-sm text-[#949CA9]">
                          Total Discount :
                        </span>
                        <p className="font-bold text-xs line-through">250</p>
                      </div>
                      <div className="flex justify-between border-b border-gray-200 pb-1">
                        <span className="text-sm text-[#949CA9]">
                          Sub Discount :
                        </span>
                        <p className="font-bold text-sm">250 JD</p>
                      </div>
                      <div className="flex justify-between mt-1">
                        <span className="text-sm text-[#949CA9]">Total :</span>
                        <p className="font-bold text-sm">
                          <span className="text-xs text-[#949CA9] font-medium mr-1">
                            (item 1)
                          </span>
                          250 JD
                        </p>
                      </div>
                      <div>
                        <button className="w-full bg-[#F35895] text-white py-2 mt-3 rounded-lg hover:bg-[#f2478e] transition">
                          Confirm
                        </button>
                      </div>
                    </div>
                  )}

                  {selectedMethod === "card" && (
                    <div className="space-y-4">
                      <div>
                        <input
                          type="text"
                          placeholder="John Doe"
                          className="w-full px-4 py-2 border rounded-lg outline-none focus:outline-none focus:ring-0 border-[#C2C8D2]"
                        />
                      </div>

                      <div>
                        <input
                          type="text"
                          placeholder="1234 5678 9012 3456"
                          className="w-full px-4 py-2 border rounded-lg outline-none focus:outline-none focus:ring-0 border-[#C2C8D2]"
                        />
                      </div>

                      <div className="grid grid-cols-2 gap-4">
                        <div>
                          <input
                            type="text"
                            placeholder="123"
                            className="w-full px-4 py-2 border rounded-lg outline-none focus:outline-none focus:ring-0 border-[#C2C8D2]"
                          />
                        </div>

                        <div>
                          <input
                            type="text"
                            placeholder="08/26"
                            className="w-full px-4 py-2 border rounded-lg outline-none focus:outline-none focus:ring-0 border-[#C2C8D2]"
                          />
                        </div>
                      </div>

                      <button className="w-full bg-[#F35895] text-white py-2 rounded-lg hover:bg-[#f2478e] transition">
                        Pay Now
                      </button>
                    </div>
                  )}

                  {selectedMethod === "cliq" && (
                    <div>
                      <div className="mb-3">
                        <label className="block text-xs mb-1">Cliq Number</label>
                        <input
                          type="text"
                          placeholder="Jordan (+962)"
                          className="w-full px-4 py-2 border rounded-lg outline-none focus:outline-none focus:ring-0 border-[#C2C8D2] mb-3"
                        />
                      </div>

                      <div className="flex justify-between">
                        <span className="text-sm text-[#949CA9]">
                          Total Discount :
                        </span>
                        <p className="font-bold text-xs line-through">250</p>
                      </div>
                      <div className="flex justify-between border-b border-gray-200 pb-1">
                        <span className="text-sm text-[#949CA9]">
                          Sub Discount :
                        </span>
                        <p className="font-bold text-sm">250 JD</p>
                      </div>
                      <div className="flex justify-between mt-1">
                        <span className="text-sm text-[#949CA9]">Total :</span>
                        <p className="font-bold text-sm">
                          <span className="text-xs text-[#949CA9] font-medium mr-1">
                            (item 1)
                          </span>
                          250 JD
                        </p>
                      </div>
                      <div>
                        <button className="w-full bg-[#F35895] text-white py-2 mt-3 rounded-lg hover:bg-[#f2478e] transition">
                          Confirm
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
