import { Link } from "@inertiajs/react";
import { IoStorefrontOutline } from "react-icons/io5";

export default function NavBar() {
  return (
       <nav className="bg-white  pt-4 flex justify-between items-center">
        <img src="/images/Frame 1000000724.png" alt="Trendpin" className="w-40" />
        <ul className="flex space-x-6 text-gray-600">
          <li>
            <Link
              href="/login"
              className="bg-[#E8347E] text-white px-4 py-2 rounded-lg hover:bg-[#E8347E]-700 transition  w-auto flex items-center justify-around space-x-2"
            >
              <IoStorefrontOutline size={24} />
              <p>Become a retailer</p>
            </Link>
          </li>
        </ul>
      </nav>
  );
}
