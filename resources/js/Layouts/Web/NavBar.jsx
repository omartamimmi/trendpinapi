import { Link, usePage } from "@inertiajs/react";
import { IoStorefrontOutline } from "react-icons/io5";

export default function NavBar() {
  const { auth } = usePage().props;

  return (
<nav className="bg-white flex items-center justify-between pt-4 w-full px-8">
      <img src="/images/Frame 1000000724.png" alt="Trendpin" className="w-40" />
      <ul className="flex space-x-6 text-gray-600">
        <li>
          {auth.user ? (
            // User is logged in - show Dashboard button
            <Link
              href="/retailer/dashboard"
              className="bg-[#E8347E] text-white px-4 py-2 rounded-lg hover:bg-[#d12d6e] transition w-auto flex items-center justify-around space-x-2"
            >
              <IoStorefrontOutline size={24} />
              <p>Dashboard</p>
            </Link>
          ) : (
            // User is not logged in - show Become a retailer button
            <Link
              href="/login"
              className="bg-[#E8347E] text-white px-4 py-2 rounded-lg hover:bg-[#d12d6e] transition w-auto flex items-center justify-around space-x-2"
            >
              <IoStorefrontOutline size={24} />
              <p>Become a retailer</p>
            </Link>
          )}
        </li>
      </ul>
    </nav>
  );
}
