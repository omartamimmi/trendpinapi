type CardComponentProps = {
  img: string;
  header: string;
  title: string;
  description: string;
  offer: string;
  validity: string;
  name: string; // for radio group
  checked?: boolean;
  onChange?: () => void;
};

export default function CardRetailerSubscriptionInformation({
  img,
  header,
  title,
  description,
  offer,
  validity,
  name,
  checked,
  onChange,
}: CardComponentProps) {
  return (
    <div className="border border-[#E0E0EC] rounded-lg">
      {/* Header Section */}
      <div className="flex items-center justify-between p-4">
        {/* Left - Image */}
        <div className="border border-[#E0E0EC] flex justify-center w-12 h-12 items-center md:h-12 md:w-12 p-2 rounded-full sm:h-12 sm:w-12">
          <img src={img} alt={header} className="max-w-full max-h-full" />
        </div>

        {/* Middle - Header + Title */}
        <div>
          <h3 className="text-[#152C5B] text-xs">{header}</h3>
          <span className="text-[#152C5B]">{title}</span>
        </div>

        {/* Right - Radio */}
        <div className="border border-[#E0E0EC] flex h-8 w-8 items-center justify-center md:h-8 md:w-8 p-2 rounded-full sm:h-8 sm:w-8">
          <input
            type="radio"
            name={name}
            checked={checked}
            onChange={onChange}
          />
        </div>
      </div>

      {/* Body Section */}
      <div className="p-4 leading-6 text-left">
        <span className="text-xs text-[#2F305A]">{description}</span>
        <h3 className="font-bold text-[#071731]">{offer}</h3>
        <p className="text-xs">{validity}</p>
      </div>
    </div>
  );
}
