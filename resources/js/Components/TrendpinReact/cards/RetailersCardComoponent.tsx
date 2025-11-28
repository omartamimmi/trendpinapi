type CardComponentProps = {
  title: string;
  body: React.ReactNode;
  actionLabel?: string;
  onAction?: () => void;
};

export default function RetailersCardComponent({
  title,
  body,
  actionLabel,
  onAction,
}: CardComponentProps) {
  return (
    <div className="w-[253px] h-[256px] bg-white rounded-lg shadow flex flex-col">
      {/* Header */}
        <div className="border-t rounded-t-lg px-4 py-2 bg-[#494B74]">
        <h2 className="text-lg font-semibold text-white">{title}</h2>
        </div>

      {/* Body */}
      <div className="p-4 text-gray-600 flex-1">
        {body}
      </div>

      {/* Footer Action */}
      {actionLabel && (
        <div className="px-4 py-2">
          <button
            onClick={onAction}
            className="w-full text-sm text-white bg-pink-500 rounded-md py-2 hover:bg-pink-600"
          >
            {actionLabel}
          </button>
        </div>
      )}
    </div>
  );
}
