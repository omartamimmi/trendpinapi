import { Dropdown } from "primereact/dropdown";
import { InputText } from "primereact/inputtext";
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
  // Default phone country
  const [country, setCountry] = useState("jo");

  // Bank options
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

  // Add / remove payment method from array
  const toggleMethod = (method: string) => {
    const updated = data.paymentMethod.includes(method)
      ? data.paymentMethod.filter((m) => m !== method)
      : [...data.paymentMethod, method];

    onChange("paymentMethod", updated);
  };

  return (
    <div className="space-y-6">
      <h3 className="text-center text-lg font-semibold text-[#152C5B]">
        Payment Details
      </h3>

      {/* PAYMENT METHOD CHECKBOXES */}
      <div>
        <label className="block font-medium mb-2">Payment Method</label>

        <div className="space-y-3 flex items-center justify-between">

          {/* Bank */}
          <label className="flex items-center space-x-3 cursor-pointer">
            <input
              type="checkbox"
              checked={data.paymentMethod.includes("bank")}
              onChange={() => toggleMethod("bank")}
              className="
                w-5 h-5
                rounded 
                border-2 border-[#DB2E7C]
                accent-[#DB2E7C]
                checked:bg-[#DB2E7C]
                checked:border-[#DB2E7C]
              "
            />
            <span>Bank Account</span>
          </label>

          {/* Cliq */}
          <label className="flex items-center space-x-3 cursor-pointer">
            <input
              type="checkbox"
              checked={data.paymentMethod.includes("cliq")}
              onChange={() => toggleMethod("cliq")}
              className="
                w-5 h-5
                rounded 
                border-2 border-[#DB2E7C]
                accent-[#DB2E7C]
                checked:bg-[#DB2E7C]
                checked:border-[#DB2E7C]
              "
            />
            <span>Cliq Account</span>
          </label>
        </div>
      </div>

      {/* BANK FIELDS */}
      {data.paymentMethod.includes("bank") && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* Bank Name */}
          <div>
            <label className="block font-medium mb-1">Bank Name</label>
            <Dropdown
              value={data.bankName}
              options={bankOptions}
              onChange={(e) => onChange("bankName", e.value)}
              placeholder="Select Bank"
              className="w-full"
            />
          </div>

          {/* IBAN */}
          <div>
            <label className="block font-medium mb-1">IBAN</label>
            <InputText
              value={data.iban}
              onChange={(e) => onChange("iban", e.target.value)}
              className="w-full"
              placeholder="JO94CBJO0010000000000131000302"
            />
          </div>
        </div>
      )}

      {/* CLIQ PHONE INPUT */}
      {data.paymentMethod.includes("cliq") && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
 <label className="block font-medium mb-1">Cliq Phone Number</label>
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
              height: "42px",
              borderRadius: "6px",
              border: "1px solid #d1d5db",
              fontSize: "14px",
              paddingLeft: "48px",
            }}
            countrySelectorStyleProps={{
              buttonStyle: {
                border: "none",
                backgroundColor: "transparent",
                paddingLeft: "8px",
              },
            }}
          />
          </div>
         
        </div>
      )}
    </div>
  );
}
