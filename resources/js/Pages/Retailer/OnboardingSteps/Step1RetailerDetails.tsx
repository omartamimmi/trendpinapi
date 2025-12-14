import { InputText } from "primereact/inputtext";
import { Image } from "primereact/image";
import { FaFile, FaUpload } from "react-icons/fa";
import { useRef, useState } from "react";
import { PhoneInput } from "react-international-phone";
import "react-international-phone/style.css";
import { router } from '@inertiajs/react';

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
  const [processing, setProcessing] = useState(false);

  // State for image previews
  const [logoPreview, setLogoPreview] = useState<string | null>(existingLogoUrl || null);
  const [licensePreview, setLicensePreview] = useState<string | null>(existingLicenseUrl || null);

  // Default country (ISO2 format)
  const [country, setCountry] = useState("jo"); // 🇯🇴 Jordan by default

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    console.log('Submit button clicked! Form data:', data);

    // Validation
    if (!data.retailerName || !data.category || !data.city) {
      alert('Please fill in all required fields');
      return;
    }

    console.log('Validation passed, submitting...');
    setProcessing(true);

    // Prepare form data
    const formData = new FormData();
    formData.append('retailer_name', data.retailerName);
    formData.append('category', data.category);
    formData.append('city', data.city);
    if (data.phoneNumber) formData.append('phone_number', data.phoneNumber);
    if (data.countryCode) formData.append('country_code', data.countryCode);
    if (data.logoFile) formData.append('logo', data.logoFile);
    if (data.licenseFile) formData.append('license', data.licenseFile);

    router.post('/retailer/onboarding/retailer-details', formData, {
      onSuccess: () => {
        setProcessing(false);
      },
      onError: (errors) => {
        console.error('Error:', errors);
        setProcessing(false);
        alert('Failed to save. Please try again.');
      },
      forceFormData: true,
    });
  };

  const cities = [
    "Amman",
    "Zarqa",
    "Irbid",
    "Aqaba",
    "Madaba",
    "Jerash",
    "Ajloun",
    "Karak",
    "Tafilah",
    "Ma'an",
    "Salt",
    "Mafraq"
  ];

  const categories = [
    "Food & Beverages",
    "Fashion & Apparel",
    "Electronics",
    "Home & Furniture",
    "Health & Beauty",
    "Sports & Fitness",
    "Books & Stationery",
    "Toys & Games",
    "Automotive",
    "Jewelry & Accessories",
    "Pet Supplies",
    "Other"
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <h3 className="text-center text-2xl font-bold text-[#2F305A] mb-8">
        Retailer Details
      </h3>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Name */}
        <div>
          <label className="block font-medium mb-2 text-[#2F305A]">Name</label>
          <InputText
            className="w-full p-3 border border-gray-300 rounded-lg focus:border-[#E8347E] focus:ring-2 focus:ring-[#E8347E]/20"
            placeholder="Enter retailer name"
            value={data.retailerName}
            onChange={(e) => onChange("retailerName", e.target.value)}
            required
          />
        </div>

        {/* Phone Number */}
        <div>
          <label className="block font-medium mb-2 text-[#2F305A]">Phone</label>
          <div className="phone-input-wrapper">
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
                height: "48px",
                borderRadius: "8px",
                border: "1px solid #d1d5db",
                fontSize: "14px",
                paddingLeft: "52px"
              }}
              countrySelectorStyleProps={{
                buttonStyle: {
                  border: "none",
                  backgroundColor: "transparent",
                  paddingLeft: "12px",
                  position: "absolute",
                  left: "0",
                  top: "0",
                  height: "48px",
                  display: "flex",
                  alignItems: "center",
                  zIndex: 1
                }
              }}
            />
          </div>
          <style>{`
            .phone-input-wrapper {
              position: relative;
            }
            .phone-input-wrapper .react-international-phone-country-selector-button {
              position: absolute !important;
              left: 0 !important;
              top: 0 !important;
              height: 48px !important;
              display: flex !important;
              align-items: center !important;
              border: none !important;
              background: transparent !important;
              padding-left: 12px !important;
              z-index: 1 !important;
            }
            .phone-input-wrapper .react-international-phone-country-selector-button__button-content {
              display: flex !important;
              align-items: center !important;
            }
          `}</style>
        </div>

        {/* City */}
        <div>
          <label className="block font-medium mb-2 text-[#2F305A]">City</label>
          <select
            value={data.city || ""}
            onChange={(e) => onChange("city", e.target.value)}
            className="w-full p-3 border border-gray-300 rounded-lg text-[#2F305A] bg-white focus:border-[#E8347E] focus:ring-2 focus:ring-[#E8347E]/20 focus:outline-none"
            required
          >
            <option value="">Select city</option>
            {cities.map((city) => (
              <option key={city} value={city}>
                {city}
              </option>
            ))}
          </select>
        </div>

        {/* Retail Category */}
        <div>
          <label className="block font-medium mb-2 text-[#2F305A]">
            Retail category
          </label>
          <select
            value={data.category}
            onChange={(e) => onChange("category", e.target.value)}
            className="w-full p-3 border border-gray-300 rounded-lg text-[#2F305A] bg-white focus:border-[#E8347E] focus:ring-2 focus:ring-[#E8347E]/20 focus:outline-none"
            required
          >
            <option value="">Select</option>
            {categories.map((category) => (
              <option key={category} value={category}>
                {category}
              </option>
            ))}
          </select>
        </div>

        {/* Upload Logo */}
        <div>
          <label className="block font-medium mb-2 text-[#2F305A]">
            Upload logo
          </label>
          <div
            onClick={() => logoRef.current?.click()}
            className="border-2 border-dashed border-gray-300 rounded-lg p-8 flex flex-col items-center justify-center cursor-pointer hover:border-[#E8347E] hover:bg-[#E8347E]/5 transition-all"
          >
            {data.logoFile ? (
              <div className="flex flex-col items-center">
                <Image
                  src={URL.createObjectURL(data.logoFile)}
                  alt="Logo"
                  width="80"
                  preview
                  className="mb-2 rounded-md"
                />
                <span className="text-sm text-[#2F305A]">{data.logoFile.name}</span>
              </div>
            ) : logoPreview ? (
              <div className="flex flex-col items-center">
                <Image
                  src={logoPreview}
                  alt="Existing Logo"
                  width="80"
                  preview
                  className="mb-2 rounded-md"
                />
                <span className="text-xs text-gray-500 mt-1">
                  Click to change logo
                </span>
              </div>
            ) : (
              <>
                <div className="w-12 h-12 bg-[#2F305A]/10 rounded-full flex items-center justify-center mb-3">
                  <FaUpload className="text-xl text-[#2F305A]" />
                </div>
                <span className="text-sm text-[#2F305A] font-medium">
                  Click to upload logo
                </span>
                <span className="text-xs text-gray-500 mt-1">
                  PNG, JPG up to 5MB
                </span>
              </>
            )}
          </div>
          <input
            type="file"
            ref={logoRef}
            accept="image/*"
            className="hidden"
            onChange={(e) => onChange("logoFile", e.target.files?.[0] ?? null)}
          />
        </div>

        {/* Upload License */}
        <div>
          <label className="block font-medium mb-2 text-[#2F305A]">
            Upload license
          </label>
          <div
            onClick={() => licenseRef.current?.click()}
            className="border-2 border-dashed border-gray-300 rounded-lg p-8 flex flex-col items-center justify-center cursor-pointer hover:border-[#E8347E] hover:bg-[#E8347E]/5 transition-all"
          >
            {data.licenseFile ? (
              <div className="flex flex-col items-center">
                {data.licenseFile.type.startsWith('image/') ? (
                  <Image
                    src={URL.createObjectURL(data.licenseFile)}
                    alt="License"
                    width="80"
                    preview
                    className="mb-2 rounded-md"
                  />
                ) : (
                  <FaFile className="text-4xl text-[#2F305A] mb-2" />
                )}
                <span className="text-sm text-[#2F305A]">{data.licenseFile.name}</span>
              </div>
            ) : licensePreview ? (
              <div className="flex flex-col items-center">
                <Image
                  src={licensePreview}
                  alt="Existing License"
                  width="80"
                  preview
                  className="mb-2 rounded-md"
                />
                <span className="text-xs text-gray-500 mt-1">
                  Click to change license
                </span>
              </div>
            ) : (
              <>
                <div className="w-12 h-12 bg-[#2F305A]/10 rounded-full flex items-center justify-center mb-3">
                  <FaUpload className="text-xl text-[#2F305A]" />
                </div>
                <span className="text-sm text-[#2F305A] font-medium">
                  Click to upload license
                </span>
                <span className="text-xs text-gray-500 mt-1">
                  PNG, JPG, PDF up to 10MB
                </span>
              </>
            )}
          </div>
          <input
            type="file"
            ref={licenseRef}
            accept="image/*,application/pdf"
            className="hidden"
            onChange={(e) =>
              onChange("licenseFile", e.target.files?.[0] ?? null)
            }
          />
        </div>
      </div>
    </div>
  );
}
