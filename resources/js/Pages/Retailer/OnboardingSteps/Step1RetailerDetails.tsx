import { InputText } from "primereact/inputtext";
import { Image } from "primereact/image";
import { FaFile } from "react-icons/fa";
import { useRef, useState } from "react";
import { PhoneInput } from "react-international-phone";
import "react-international-phone/style.css"; 

interface Props {
  data: {
    retailerName: string;
    category: string;
    phoneNumber?: string;
    countryCode?: string;
    logoFile: File | null;
    licenseFile: File | null;
  };
  onChange: <K extends keyof Props["data"]>(
    field: K,
    value: Props["data"][K]
  ) => void;
}

export default function Step1RetailerDetails({ data, onChange }: Props) {
  const logoRef = useRef<HTMLInputElement | null>(null);
  const licenseRef = useRef<HTMLInputElement | null>(null);

  // Default country (ISO2 format)
  const [country, setCountry] = useState("jo"); // 🇯🇴 Jordan by default

  return (
    <div className="space-y-6">
      {/* Header */}
      <h3 className="text-center text-lg font-semibold text-[#152C5B]">
        Retailer Details
      </h3>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Retailer Name */}
        <div>
          <label className="block font-medium mb-1">Retailer Name</label>
          <InputText
            className="w-full"
            value={data.retailerName}
            onChange={(e) => onChange("retailerName", e.target.value)}
          />
        </div>

        {/* 🌍 International Phone Input */}
        <div>
          <label className="block font-medium mb-1">Phone Number</label>
          <PhoneInput
            defaultCountry={country}
            value={data.phoneNumber ?? ""}
            onChange={(value, meta) => {
              // meta.country contains { iso2, dialCode, name }
              setCountry(meta.country.iso2);
              onChange("countryCode", `+${meta.country.dialCode}`);
              onChange("phoneNumber", value);
            }}
            inputStyle={{
              width: "100%",
              height: "42px",
              borderRadius: "6px",
              border: "1px solid #d1d5db",
              fontSize: "14px",
            }}
            countrySelectorStyleProps={{
              buttonStyle: {
                border: "none",
                backgroundColor: "transparent",
              },
            }}
          />
        </div>


        {/* Upload Logo */}
        <div>
          <label className="block font-medium mb-1">Upload Logo</label>
          <div
            onClick={() => logoRef.current?.click()}
            className="border rounded-lg p-4 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-50 transition"
          >
            <FaFile className="text-2xl text-gray-500 mb-2" />
            <span className="text-sm text-gray-600">Click to upload logo</span>
          </div>
          <input
            type="file"
            ref={logoRef}
            accept="image/*"
            className="hidden"
            onChange={(e) => onChange("logoFile", e.target.files?.[0] ?? null)}
          />
          {data.logoFile && (
            <Image
              src={URL.createObjectURL(data.logoFile)}
              alt="Logo"
              width="120"
              preview
              className="mt-2 rounded-md"
            />
          )}
        </div>

        {/* Upload License */}
        <div>
          <label className="block font-medium mb-1">Upload License</label>
          <div
            onClick={() => licenseRef.current?.click()}
            className="border rounded-lg p-4 flex flex-col items-center justify-center cursor-pointer hover:bg-gray-50 transition"
          >
            <FaFile className="text-2xl text-gray-500 mb-2" />
            <span className="text-sm text-gray-600">
              Click to upload license
            </span>
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
          {data.licenseFile && (
            <Image
              src={URL.createObjectURL(data.licenseFile)}
              alt="License"
              width="120"
              preview
              className="mt-2 rounded-md"
            />
          )}
        </div>

        {/* Category */}
        <div>
          <label className="block font-medium mb-1">Category</label>
          <select
            value={data.category}
            onChange={(e) => onChange("category", e.target.value)}
            className="w-full border rounded p-2 text-sm"
          >
            <option value="">Select</option>
            <option value="retailer">Retailer</option>
            <option value="wholesaler">Wholesaler</option>
            <option value="distributor">Distributor</option>
          </select>
        </div>
      </div>
    </div>
  );
}
