type CardComponentProps = {
  children?: React.ReactNode;
};
export default function CardComponent({ children }: CardComponentProps) {
  return <div className="border border-none shadow-[0px_4px_10px_0px_#8A8E940D] rounded-lg p-4  bg-[#FFFFFF] w-full h-auto min-h-[600px] space-y-6">{children}</div>;
}
