import { useMemo } from "react";

interface Step5Data {
  paymentOption: "" | "cliq" | "cash" | "card";
}

interface Step5Props {
  data: Step5Data;
  onChange: <K extends keyof Step5Data>(
    field: K,
    value: Step5Data[K]
  ) => void;
  selectedPlan?: {
    name: string;
    price: number;
    duration_months: number;
  } | null;
}

export default function Step5Payment({ data, onChange, selectedPlan }: Step5Props) {
  const selectedMethod = useMemo(() => data.paymentOption, [data.paymentOption]);
  const price = selectedPlan?.price || 500;

  const paymentMethods = [
    { id: "card", name: "Card", icon: "💳" },
    { id: "cliq", name: "CliQ", icon: "📱" },
    { id: "cash", name: "Cash", icon: "💵" },
  ];

  return (
    <div className="space-y-6 max-w-xl mx-auto">
      {/* Header */}
      <div className="text-center">
        <div className="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 mb-3">
          <svg className="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
        <h3 className="text-xl font-bold text-gray-800">Complete Payment</h3>
        <p className="text-gray-500 text-sm mt-1">Choose your payment method</p>
      </div>

      {/* Order Summary */}
      <div className="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-4 text-white">
        <div className="flex justify-between items-center">
          <div>
            <p className="text-white/80 text-sm">Total Amount</p>
            <p className="text-2xl font-bold">{price}.00 JD</p>
          </div>
          <div className="text-right">
            <p className="text-white/80 text-sm">{selectedPlan?.name || "Plan"}</p>
            <p className="text-sm">{selectedPlan?.duration_months || 1} month(s)</p>
          </div>
        </div>
      </div>

      {/* Payment Methods */}
      <div className="grid grid-cols-3 gap-3">
        {paymentMethods.map((method) => (
          <button
            key={method.id}
            type="button"
            onClick={() => onChange("paymentOption", method.id as Step5Data["paymentOption"])}
            className={`p-4 rounded-xl border-2 transition-all text-center ${
              selectedMethod === method.id
                ? "border-pink-500 bg-pink-50"
                : "border-gray-200 hover:border-pink-300"
            }`}
          >
            <span className="text-2xl block mb-1">{method.icon}</span>
            <span className={`text-sm font-medium ${selectedMethod === method.id ? "text-pink-600" : "text-gray-700"}`}>
              {method.name}
            </span>
          </button>
        ))}
      </div>

      {/* Payment Form */}
      {selectedMethod === "card" && (
        <div className="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
          <input
            type="text"
            placeholder="Cardholder Name"
            className="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
          />
          <input
            type="text"
            placeholder="Card Number"
            className="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-pink-500"
          />
          <div className="grid grid-cols-2 gap-3">
            <input
              type="text"
              placeholder="MM/YY"
              className="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-pink-500"
            />
            <input
              type="text"
              placeholder="CVV"
              maxLength={4}
              className="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-pink-500"
            />
          </div>
        </div>
      )}

      {selectedMethod === "cliq" && (
        <div className="bg-white rounded-xl border border-gray-200 p-4">
          <label className="text-sm text-gray-600 mb-2 block">CliQ Phone Number</label>
          <div className="flex">
            <span className="px-3 py-2.5 bg-gray-100 border border-r-0 border-gray-200 rounded-l-lg text-sm text-gray-500">
              +962
            </span>
            <input
              type="text"
              placeholder="7X XXX XXXX"
              className="flex-1 px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-r-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
            />
          </div>
        </div>
      )}

      {selectedMethod === "cash" && (
        <div className="bg-blue-50 rounded-xl p-4 text-sm">
          <p className="text-blue-800 font-medium mb-2">Pay at our office:</p>
          <p className="text-blue-600">Amman, Jordan - 7th Circle</p>
          <p className="text-blue-600">Sun-Thu: 9AM - 5PM</p>
        </div>
      )}

      {/* Confirm Button */}
      {selectedMethod && (
        <button
          type="button"
          className="w-full py-3 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-xl font-semibold hover:from-pink-600 hover:to-purple-700 transition-all shadow-lg shadow-pink-500/25"
        >
          {selectedMethod === "card" ? `Pay ${price}.00 JD` : "Confirm Payment"}
        </button>
      )}

      {!selectedMethod && (
        <p className="text-center text-sm text-amber-600">Please select a payment method</p>
      )}
    </div>
  );
}
