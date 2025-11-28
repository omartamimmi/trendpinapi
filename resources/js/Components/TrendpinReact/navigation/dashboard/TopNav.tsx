import { useState } from "react";
import { AiFillCaretDown } from "react-icons/ai";
import { router } from "@inertiajs/react";
import { FaBell } from "react-icons/fa";

export default function TopNav() {
  const [open, setOpen] = useState(false);
  const [openNotifictaion, setNotifictaion] = useState(false);
  const notification = [1, 2, 3, 4, 5, 6];

  const handleLogout = () => {
    router.post("/logout");
  };

  return (
    <header className="w-full h-20 border-b border-indigo-200 border-b-indigo-50 bg-white shadow flex items-center justify-end px-6">
      <div className="w-auto flex items-center space-x-6">
        <div  onClick={() => setNotifictaion(!openNotifictaion)}>
          <button className="relative ">
           <FaBell className="text-2xl cursor-pointer"/>
            <span className="absolute -top-1 -right-1 bg-pink-500 text-white text-xs rounded-full px-1">
              {notification.length}
            </span>
          </button>
          {openNotifictaion && (
            <div className="z-40 absolute top-20 right-20 w-48 bg-white shadow-lg rounded-lg border overflow-auto border-gray-200 h-40 over md:right-56 ">
              <ul className="py-2 text-sm text-gray-700">
                <li className="px-4 py-2 hover:bg-gray-100 cursor-pointer text-gray-700">
                  new notification
                </li>
                <li className="px-4 py-2 hover:bg-gray-100 cursor-pointer text-gray-700">
                  new notification
                </li>
                <li className="px-4 py-2 hover:bg-gray-100 cursor-pointer text-gray-700">
                  new notification
                </li>
                <li className="px-4 py-2 hover:bg-gray-100 cursor-pointer text-gray-700">
                  new notification
                </li>
                <li className="px-4 py-2 hover:bg-gray-100 cursor-pointer text-gray-700">
                  new notification
                </li>
                <li className="px-4 py-2 hover:bg-gray-100 cursor-pointer text-gray-700">
                  new notification
                </li>
              </ul>
            </div>
          )}
        </div>
        <div className="h-6 border-l border-gray-300"></div>

        <div
          className="flex items-center gap-3 cursor-pointer relative"
          onClick={() => setOpen(!open)}
        >
          <img
            src="https://via.placeholder.com/32"
            alt="avatar"
            className="w-8 h-8 rounded-full"
          />
          <div className="text-sm w-32 flex-1  items-center space-x-1 hidden md:flex">
            <p className="font-medium">Omar Abu Rajab</p>
            <span className="ml-1">
              <AiFillCaretDown />
            </span>
          </div>
          {open && (
            <div className="absolute top-14 right-0 w-48 bg-white shadow-lg rounded-lg border border-gray-200 z-40">
              <ul className="py-2 text-sm text-gray-700">
                <li
                  className="px-4 py-2 hover:bg-gray-100 cursor-pointer text-red-500"
                  onClick={() => handleLogout()}
                >
                  Logout
                </li>
              </ul>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
