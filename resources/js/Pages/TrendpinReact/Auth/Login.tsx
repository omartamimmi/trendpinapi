import { useState } from "react";
import LoginCard from "../../../Components/TrendpinReact/cards/loginCard";

export default function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  return (
    <div className="w-screen h-screen flex flex-col lg:flex-row bg-[#2F305A] overflow-hidden items-center justify-center">
      {/* Left Side (Login Form Section) */}
      <div className="flex flex-col items-center justify-center w-full lg:w-[35%] relative lg:ml-24 my-8">
        {/* ✅ Logo ABOVE the card */}
        <div className="flex space-x-2 items-center mb-6">
          <img src="/images/logo.png" alt="Trenpin Logo" className="w-14 h-14 " />
          <h1 className="text-3xl font-semibold text-white">Trenpin</h1>
        </div>

        {/* White Login Card */}
        <div className="flex flex-col justify-center items-center w-full bg-white rounded-2xl shadow-2xl px-6 lg:px-12 py-10">
          <h2 className="text-xl font-semibold text-[#2F305A] mb-2">
            Retailer Login
          </h2>
          <p className="text-sm text-gray-500 mb-4">
            Enter details to create your account
          </p>

          <div className="w-full max-w-md">
            <LoginCard
              email={email}
              setEmail={setEmail}
              password={password}
              setPassword={setPassword}
            />
          </div>

          <p className="text-sm text-gray-500 mt-6">
            You don’t have an account?{" "}
            <span className="text-[#E70D7E] font-semibold cursor-pointer hover:underline">
              Sign up
            </span>
          </p>
        </div>
      </div>

      {/* Right Side Image (hidden on mobile/tablet) */}
      <div className="hidden lg:flex flex-1 items-center  relative">
        <img
          src="/images/Screens-Top.png"
          alt="App Screens"
          className="relative w-full p-[30px] max-w-[700px]"
        />
      </div>
    </div>
  );
}
