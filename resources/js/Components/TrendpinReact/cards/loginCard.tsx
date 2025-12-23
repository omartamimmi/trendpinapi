import React from "react";
import axios from "axios";
import { router } from "@inertiajs/react";
import { useState } from "react";

interface LoginCardProps {
  email: string;
  setEmail: (email: string) => void;
  password: string;
  setPassword: (password: string) => void;
}

export default function LoginCard({
  email,
  setEmail,
  password,
  setPassword,
}: LoginCardProps) {
  //validation error
  const [errors, setErrors] = useState<{
    email?: string;
    password?: string;
  }>({});
  const validate = () => {
    const newErrors: typeof errors = {};
    if (!email) newErrors.email = "Email is required";
    else if (!/\S+@\S+\.\S+/.test(email))
      newErrors.email = "Invalid email format";

    if (!password) newErrors.password = "Password is required";
    else if (password.length < 6)
      newErrors.password = "Password must be at least 6 characters";

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault(); // Prevent page reload on form submit

    if (!validate()) return;

    try {
      const res = await axios.post(
        "https://auth-d96dd-default-rtdb.firebaseio.com/users.json",
        { email, password }
      );

      if (res.status === 200) {
        // Use Inertia router to navigate
        router.visit("/dashboard");
      }
    } catch (err) {
      console.error("Login failed:", err);
    }
  };

  return (
   <div className="w-full flex items-center justify-center ">
  <form
    onSubmit={handleLogin}
    className="w-full max-w-md sm:max-w-lg md:max-w-xl lg:max-w-2xl flex flex-col gap-4  sm:p-8 "
  >
    <div>
      <input
        type="email"
        placeholder="Email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        className={`w-full p-3 border rounded-lg focus:outline-none focus:ring-2 ${
          errors.email
            ? "border-red-500 focus:ring-red-500"
            : "focus:ring-blue-500"
        }`}
        autoComplete="email"
      />
      {errors.email && (
        <p className="text-red-500 text-sm mt-1">{errors.email}</p>
      )}
    </div>

    <div>
      <input
        type="password"
        placeholder="Password"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        className={`w-full p-3 border rounded-lg focus:outline-none focus:ring-2 ${
          errors.password
            ? "border-red-500 focus:ring-red-500"
            : "focus:ring-blue-500"
        }`}
        autoComplete="current-password"
      />
      {errors.password && (
        <p className="text-red-500 text-sm mt-1">{errors.password}</p>
      )}
    </div>

    {/* Submit Button */}
    <button
      type="submit"
      className="w-full bg-[#2F305A] text-white py-3 rounded-lg mt-4 hover:bg-[#26284a] transition"
    >
      Login
    </button>
  </form>
</div>

  );
}
