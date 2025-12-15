import { useMemo, useState } from "react";
import { PhoneInput } from "react-international-phone";
import "react-international-phone/style.css";

interface Props {
  data: {
    paymentMethod: string[];
    bankName: string;
    iban: string;
    cliqNumber: string;
    countryCode?: string;
  };
  onChange: <K extends keyof Props["data"]>(
    field: K,
    value: Props["data"][K]
  ) => void;
}

export default function Step2BankAccount({ data, onChange }: Props) {
  const [country, setCountry] = useState("jo");

  const bankOptions = useMemo(
    () => [
      { label: "Arab Bank", value: "arab" },
      { label: "Housing Bank", value: "housing" },
      { label: "Cairo Amman Bank", value: "cairo" },
      { label: "Jordan Kuwait Bank", value: "jkb" },
      { label: "Bank of Jordan", value: "bojo" },
    ],
    []
  );

  const toggleMethod = (method: string) => {
    const updated = data.paymentMethod.includes(method)
      ? data.paymentMethod.filter((m) => m !== method)
      : [...data.paymentMethod, method];
    onChange("paymentMethod", updated);
  };

  return (
    <div className="space-y-8">
      {/* Header Section */}
      <div className="text-center">
        <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 mb-4">
          <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
        </div>
        <h3 className="text-2xl font-bold text-gray-800">Payment Details</h3>
        <p className="text-gray-500 mt-2">Choose your preferred payment method for receiving payments</p>
      </div>

      {/* Payment Method Selection Card */}
      <div className="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div className="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-white rounded-lg shadow-sm">
              <svg className="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
            </div>
            <div>
              <h4 className="font-semibold text-gray-800">Select Payment Method</h4>
              <p className="text-sm text-gray-500">You can select multiple methods</p>
            </div>
          </div>
        </div>

        <div className="p-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Bank Account Option */}
            <label
              className={`relative flex items-center p-5 rounded-xl border-2 cursor-pointer transition-all duration-200 ${
                data.paymentMethod.includes("bank")
                  ? "border-pink-500 bg-pink-50 shadow-md"
                  : "border-gray-200 hover:border-pink-300 hover:bg-gray-50"
              }`}
            >
              <input
                type="checkbox"
                checked={data.paymentMethod.includes("bank")}
                onChange={() => toggleMethod("bank")}
                className="sr-only"
              />
              <div className={`flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center ${
                data.paymentMethod.includes("bank") ? "bg-pink-500" : "bg-gray-100"
              }`}>
                <svg className={`w-6 h-6 ${data.paymentMethod.includes("bank") ? "text-white" : "text-gray-400"}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div className="ml-4 flex-1">
                <span className={`block font-semibold ${data.paymentMethod.includes("bank") ? "text-pink-700" : "text-gray-700"}`}>
                  Bank Account
                </span>
                <span className="text-sm text-gray-500">Receive payments via bank transfer</span>
              </div>
              {data.paymentMethod.includes("bank") && (
                <div className="absolute top-3 right-3">
                  <svg className="w-6 h-6 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                  </svg>
                </div>
              )}
            </label>

            {/* Cliq Account Option */}
            <label
              className={`relative flex items-center p-5 rounded-xl border-2 cursor-pointer transition-all duration-200 ${
                data.paymentMethod.includes("cliq")
                  ? "border-pink-500 bg-pink-50 shadow-md"
                  : "border-gray-200 hover:border-pink-300 hover:bg-gray-50"
              }`}
            >
              <input
                type="checkbox"
                checked={data.paymentMethod.includes("cliq")}
                onChange={() => toggleMethod("cliq")}
                className="sr-only"
              />
              <div className={`flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center ${
                data.paymentMethod.includes("cliq") ? "bg-pink-500" : "bg-gray-100"
              }`}>
                <svg className={`w-6 h-6 ${data.paymentMethod.includes("cliq") ? "text-white" : "text-gray-400"}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <div className="ml-4 flex-1">
                <span className={`block font-semibold ${data.paymentMethod.includes("cliq") ? "text-pink-700" : "text-gray-700"}`}>
                  CliQ Account
                </span>
                <span className="text-sm text-gray-500">Instant payments via CliQ</span>
              </div>
              {data.paymentMethod.includes("cliq") && (
                <div className="absolute top-3 right-3">
                  <svg className="w-6 h-6 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                  </svg>
                </div>
              )}
            </label>
          </div>
        </div>
      </div>

      {/* Bank Account Details Card */}
      {data.paymentMethod.includes("bank") && (
        <div className="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden animate-fadeIn">
          <div className="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
            <div className="flex items-center space-x-3">
              <div className="p-2 bg-white rounded-lg shadow-sm">
                <svg className="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <h4 className="font-semibold text-gray-800">Bank Account Information</h4>
                <p className="text-sm text-gray-500">Enter your bank account details</p>
              </div>
            </div>
          </div>

          <div className="p-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {/* Bank Name */}
              <div className="space-y-2">
                <label className="flex items-center text-sm font-medium text-gray-700">
                  <svg className="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  Bank Name
                </label>
                <div className="relative">
                  <select
                    value={data.bankName}
                    onChange={(e) => onChange("bankName", e.target.value)}
                    className="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-200 appearance-none cursor-pointer"
                  >
                    <option value="">Select Bank</option>
                    {bankOptions.map((bank) => (
                      <option key={bank.value} value={bank.value}>
                        {bank.label}
                      </option>
                    ))}
                  </select>
                  <div className="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                    <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                </div>
              </div>

              {/* IBAN */}
              <div className="space-y-2">
                <label className="flex items-center text-sm font-medium text-gray-700">
                  <svg className="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                  </svg>
                  IBAN Number
                </label>
                <input
                  type="text"
                  value={data.iban}
                  onChange={(e) => onChange("iban", e.target.value.toUpperCase())}
                  placeholder="JO94CBJO0010000000000131000302"
                  className="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-200 font-mono"
                />
                <p className="text-xs text-gray-400 mt-1">Enter your 30-character IBAN number</p>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Cliq Account Details Card */}
      {data.paymentMethod.includes("cliq") && (
        <div className="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden animate-fadeIn">
          <div className="bg-gradient-to-r from-green-50 to-teal-50 px-6 py-4 border-b border-gray-200">
            <div className="flex items-center space-x-3">
              <div className="p-2 bg-white rounded-lg shadow-sm">
                <svg className="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <h4 className="font-semibold text-gray-800">CliQ Account Information</h4>
                <p className="text-sm text-gray-500">Enter your CliQ phone number</p>
              </div>
            </div>
          </div>

          <div className="p-6">
            <div className="max-w-md">
              <div className="space-y-2">
                <label className="flex items-center text-sm font-medium text-gray-700">
                  <svg className="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  CliQ Phone Number
                </label>
                <div className="cliq-phone-wrapper">
                  <PhoneInput
                    defaultCountry={country}
                    value={data.cliqNumber || ""}
                    onChange={(value, meta) => {
                      setCountry(meta.country.iso2);
                      onChange("cliqNumber", value);
                      onChange("countryCode", `+${meta.country.dialCode}`);
                    }}
                    inputStyle={{
                      width: "100%",
                      height: "52px",
                      borderRadius: "12px",
                      border: "1px solid #e5e7eb",
                      backgroundColor: "#f9fafb",
                      fontSize: "14px",
                      paddingLeft: "60px",
                    }}
                    countrySelectorStyleProps={{
                      buttonStyle: {
                        border: "none",
                        backgroundColor: "transparent",
                        paddingLeft: "12px",
                        height: "52px",
                      },
                    }}
                  />
                </div>
                <p className="text-xs text-gray-400 mt-1">This number will be used for CliQ payments</p>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Info Note */}
      {data.paymentMethod.length === 0 && (
        <div className="bg-amber-50 border border-amber-200 rounded-xl p-4">
          <div className="flex items-start space-x-3">
            <div className="flex-shrink-0">
              <svg className="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
              </svg>
            </div>
            <div>
              <h4 className="text-sm font-medium text-amber-800">Payment Method Required</h4>
              <p className="text-sm text-amber-600 mt-1">Please select at least one payment method to continue with the onboarding process.</p>
            </div>
          </div>
        </div>
      )}

      <style>{`
        @keyframes fadeIn {
          from { opacity: 0; transform: translateY(-10px); }
          to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
          animation: fadeIn 0.3s ease-out;
        }
        .cliq-phone-wrapper .react-international-phone-input:focus {
          outline: none;
          box-shadow: 0 0 0 2px #ec4899;
          border-color: transparent;
        }
      `}</style>
    </div>
  );
}
