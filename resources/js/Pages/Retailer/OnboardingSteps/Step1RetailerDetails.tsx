import { useRef, useState } from "react";
import { PhoneInput } from "react-international-phone";
import "react-international-phone/style.css";
import "react-international-phone/style.css";

interface Props {
  data: {
    retailerName: string;
    category: string;
    city: string;
    phoneNumber?: string;
    countryCode?: string;
    logoFile: File | null;
    licenseFile: File | null;
  };
  onChange: <K extends keyof Props["data"]>(
    field: K,
    value: Props["data"][K]
  ) => void;
  existingLogoUrl?: string;
  existingLicenseUrl?: string;
}

export default function Step1RetailerDetails({ data, onChange, existingLogoUrl, existingLicenseUrl }: Props) {
  const logoRef = useRef<HTMLInputElement | null>(null);
  const licenseRef = useRef<HTMLInputElement | null>(null);

  const [logoPreview, setLogoPreview] = useState<string | null>(existingLogoUrl || null);
  const [licensePreview, setLicensePreview] = useState<string | null>(existingLicenseUrl || null);
  const [country, setCountry] = useState("jo");

  const cities = [
    "Amman", "Zarqa", "Irbid", "Aqaba", "Madaba", "Jerash",
    "Ajloun", "Karak", "Tafilah", "Ma'an", "Salt", "Mafraq"
  ];

  const categories = [
    "Food & Beverages", "Fashion & Apparel", "Electronics", "Home & Furniture",
    "Health & Beauty", "Sports & Fitness", "Books & Stationery", "Toys & Games",
    "Automotive", "Jewelry & Accessories", "Pet Supplies", "Other"
  ];

  const handleLogoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      onChange("logoFile", file);
      setLogoPreview(URL.createObjectURL(file));
    }
  };

  const handleLicenseChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      onChange("licenseFile", file);
      if (file.type.startsWith('image/')) {
        setLicensePreview(URL.createObjectURL(file));
      } else {
        setLicensePreview(null);
      }
    }
  };

  return (
    <div className="space-y-8">
      {/* Section Header */}
      <div className="text-center">
        <div className="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-pink-100 to-pink-50 rounded-2xl mb-4">
          <svg className="w-8 h-8 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <h2 className="text-2xl font-bold text-gray-900">Retailer Details</h2>
        <p className="text-gray-500 mt-2">Tell us about your business</p>
      </div>

      {/* Basic Information Card */}
      <div className="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-6 border border-gray-100">
        <div className="flex items-center gap-3 mb-6">
          <div className="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center">
            <svg className="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <div>
            <h3 className="font-semibold text-gray-900">Basic Information</h3>
            <p className="text-sm text-gray-500">Your business identity</p>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          {/* Retailer Name */}
          <div className="md:col-span-2">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Business Name <span className="text-pink-500">*</span>
            </label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <input
                type="text"
                value={data.retailerName}
                onChange={(e) => onChange("retailerName", e.target.value)}
                className="w-full pl-12 pr-4 py-3.5 bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition-all"
                placeholder="Enter your business name"
                required
              />
            </div>
          </div>

          {/* Phone Number */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Phone Number
            </label>
            <div className="phone-input-modern">
              <PhoneInput
                defaultCountry={country}
                value={data.phoneNumber ?? ""}
                onChange={(value, meta) => {
                  setCountry(meta.country.iso2);
                  onChange("countryCode", `+${meta.country.dialCode}`);
                  onChange("phoneNumber", value);
                }}
                inputStyle={{
                  width: "100%",
                  height: "54px",
                  borderRadius: "12px",
                  border: "1px solid #e5e7eb",
                  fontSize: "15px",
                  paddingLeft: "56px",
                  backgroundColor: "white"
                }}
                countrySelectorStyleProps={{
                  buttonStyle: {
                    border: "none",
                    backgroundColor: "transparent",
                    paddingLeft: "16px",
                    position: "absolute",
                    left: "0",
                    top: "0",
                    height: "54px",
                    display: "flex",
                    alignItems: "center",
                    zIndex: 1
                  }
                }}
              />
            </div>
          </div>

          {/* City */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              City <span className="text-pink-500">*</span>
            </label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <select
                value={data.city || ""}
                onChange={(e) => onChange("city", e.target.value)}
                className="w-full pl-12 pr-10 py-3.5 bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition-all appearance-none cursor-pointer"
                required
              >
                <option value="">Select your city</option>
                {cities.map((city) => (
                  <option key={city} value={city}>{city}</option>
                ))}
              </select>
              <div className="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>

          {/* Category */}
          <div className="md:col-span-2">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Business Category <span className="text-pink-500">*</span>
            </label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
              </div>
              <select
                value={data.category}
                onChange={(e) => onChange("category", e.target.value)}
                className="w-full pl-12 pr-10 py-3.5 bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition-all appearance-none cursor-pointer"
                required
              >
                <option value="">Select your category</option>
                {categories.map((category) => (
                  <option key={category} value={category}>{category}</option>
                ))}
              </select>
              <div className="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Documents Card */}
      <div className="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-6 border border-gray-100">
        <div className="flex items-center gap-3 mb-6">
          <div className="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center">
            <svg className="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div>
            <h3 className="font-semibold text-gray-900">Business Documents</h3>
            <p className="text-sm text-gray-500">Upload your logo and license</p>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          {/* Logo Upload */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Business Logo
            </label>
            <div
              onClick={() => logoRef.current?.click()}
              className={`
                relative border-2 border-dashed rounded-2xl p-6
                flex flex-col items-center justify-center cursor-pointer
                transition-all duration-200 min-h-[180px]
                ${logoPreview || data.logoFile
                  ? 'border-pink-300 bg-pink-50/50'
                  : 'border-gray-200 hover:border-pink-400 hover:bg-pink-50/30'
                }
              `}
            >
              {data.logoFile ? (
                <div className="flex flex-col items-center">
                  <div className="w-20 h-20 rounded-2xl overflow-hidden shadow-lg mb-3">
                    <img
                      src={URL.createObjectURL(data.logoFile)}
                      alt="Logo"
                      className="w-full h-full object-cover"
                    />
                  </div>
                  <span className="text-sm font-medium text-gray-700 truncate max-w-full">{data.logoFile.name}</span>
                  <span className="text-xs text-pink-500 mt-1">Click to change</span>
                </div>
              ) : logoPreview ? (
                <div className="flex flex-col items-center">
                  <div className="w-20 h-20 rounded-2xl overflow-hidden shadow-lg mb-3">
                    <img
                      src={logoPreview}
                      alt="Existing Logo"
                      className="w-full h-full object-cover"
                    />
                  </div>
                  <span className="text-xs text-pink-500">Click to change logo</span>
                </div>
              ) : (
                <>
                  <div className="w-14 h-14 bg-gradient-to-br from-pink-100 to-pink-50 rounded-2xl flex items-center justify-center mb-3">
                    <svg className="w-7 h-7 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <span className="text-sm font-medium text-gray-700">Upload Logo</span>
                  <span className="text-xs text-gray-400 mt-1">PNG, JPG up to 5MB</span>
                </>
              )}
            </div>
            <input
              type="file"
              ref={logoRef}
              accept="image/*"
              className="hidden"
              onChange={handleLogoChange}
            />
          </div>

          {/* License Upload */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Business License
            </label>
            <div
              onClick={() => licenseRef.current?.click()}
              className={`
                relative border-2 border-dashed rounded-2xl p-6
                flex flex-col items-center justify-center cursor-pointer
                transition-all duration-200 min-h-[180px]
                ${licensePreview || data.licenseFile
                  ? 'border-pink-300 bg-pink-50/50'
                  : 'border-gray-200 hover:border-pink-400 hover:bg-pink-50/30'
                }
              `}
            >
              {data.licenseFile ? (
                <div className="flex flex-col items-center">
                  {data.licenseFile.type.startsWith('image/') ? (
                    <div className="w-20 h-20 rounded-2xl overflow-hidden shadow-lg mb-3">
                      <img
                        src={URL.createObjectURL(data.licenseFile)}
                        alt="License"
                        className="w-full h-full object-cover"
                      />
                    </div>
                  ) : (
                    <div className="w-14 h-14 bg-gradient-to-br from-blue-100 to-blue-50 rounded-2xl flex items-center justify-center mb-3">
                      <svg className="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                  )}
                  <span className="text-sm font-medium text-gray-700 truncate max-w-full">{data.licenseFile.name}</span>
                  <span className="text-xs text-pink-500 mt-1">Click to change</span>
                </div>
              ) : licensePreview ? (
                <div className="flex flex-col items-center">
                  <div className="w-20 h-20 rounded-2xl overflow-hidden shadow-lg mb-3">
                    <img
                      src={licensePreview}
                      alt="Existing License"
                      className="w-full h-full object-cover"
                    />
                  </div>
                  <span className="text-xs text-pink-500">Click to change license</span>
                </div>
              ) : (
                <>
                  <div className="w-14 h-14 bg-gradient-to-br from-blue-100 to-blue-50 rounded-2xl flex items-center justify-center mb-3">
                    <svg className="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                  <span className="text-sm font-medium text-gray-700">Upload License</span>
                  <span className="text-xs text-gray-400 mt-1">PNG, JPG, PDF up to 10MB</span>
                </>
              )}
            </div>
            <input
              type="file"
              ref={licenseRef}
              accept="image/*,application/pdf"
              className="hidden"
              onChange={handleLicenseChange}
            />
          </div>
        </div>
      </div>

      {/* Info Banner */}
      <div className="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-4 border border-blue-100">
        <div className="flex items-start gap-3">
          <div className="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h4 className="font-medium text-blue-900">Why we need this information</h4>
            <p className="text-sm text-blue-700 mt-1">
              This information helps us verify your business and provide you with a personalized experience. Your documents are securely stored and only used for verification purposes.
            </p>
          </div>
        </div>
      </div>

      <style>{`
        .phone-input-modern {
          position: relative;
        }
        .phone-input-modern .react-international-phone-country-selector-button {
          position: absolute !important;
          left: 0 !important;
          top: 0 !important;
          height: 54px !important;
          display: flex !important;
          align-items: center !important;
          border: none !important;
          background: transparent !important;
          padding-left: 16px !important;
          z-index: 1 !important;
        }
        .phone-input-modern .react-international-phone-input {
          transition: all 0.2s ease !important;
        }
        .phone-input-modern .react-international-phone-input:focus {
          border-color: #ec4899 !important;
          box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1) !important;
        }
      `}</style>
    </div>
  );
}
