import { FiFacebook } from "react-icons/fi";
import { FiInstagram } from "react-icons/fi";
import { FaLinkedinIn } from "react-icons/fa";

export default function Footer() {
  return (
    <footer className="bg-white  text-gray-600 py-6">
      <div className="container  grid grid-cols-1 sm:grid-cols-4 gap-6 text-center px-10 py-2">
        <div className="flex flex-col items-start justify-start text-left space-y-4">
          <img
            src="/images/Frame 1000000724.png"
            alt="Logo"
            className="w-30"
          />

          <p className="max-w-md text-gray-600">
            Unlock a wallet full of exclusive offers and discounts with only a
            few clicks.
          </p>

          <div className="flex space-x-4 text-gray-600 text-xl">
            <FiFacebook className="cursor-pointer hover:text-blue-600 transition" />
            <FiInstagram className="cursor-pointer hover:text-pink-500 transition" />
            <FaLinkedinIn className="cursor-pointer hover:text-blue-700 transition" />
          </div>
        </div>

        <div className="flex flex-col items-start justify-start text-left space-y-4">
            <p><strong>About</strong></p>
            <ul>
                <li>About us</li>
                <li>About us</li>
            </ul>

        </div>

        
        <div className="flex flex-col items-start justify-start text-left space-y-4">
            <p><strong>Company</strong></p>
            <ul>
                <li>Why Trendpin?</li>
                <li>Become a retailer</li>
            </ul>

        </div>


        <div className="flex flex-col items-start justify-start text-left space-y-4">
            <p><strong>Support</strong></p>
            <ul>
                <li>Contact Us</li>
            </ul>

        </div>
        
      </div>
    </footer>
  );
}
