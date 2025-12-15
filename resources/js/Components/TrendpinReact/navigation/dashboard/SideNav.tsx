import { useState } from "react";
import { Link } from "@inertiajs/react";
import { BiSolidDashboard } from "react-icons/bi";
import { AiOutlineShop } from "react-icons/ai";
import { AiOutlineClose } from "react-icons/ai";
import { CiMenuBurger } from "react-icons/ci";


export default function SideNav() {
  const [open, setOpen] = useState(false);
  const baseClasses = "flex items-center gap-3 p-2 w-60 transition-colors";
  const activeClasses = "bg-white font-medium";

  const baseColorClasses = "text-[#949CA9]";
  const activeColorClasses = "text-pink-400";

  const navItems = [
    { href: "/dashboard", icon: <BiSolidDashboard />, label: "Dashboard" },
    { href: "/retailers", icon: <AiOutlineShop />, label: "Retailer Register" },
  ];

  return (
    <>
     <button
        className="md:hidden absolute top-6 left-4 z-50 text-2xl text-pink-400"
        onClick={() => setOpen(!open)}
      >
        {open ? '' : <CiMenuBurger />}
      </button>
      <aside
        className={`
          fixed top-0 left-0 h-screen bg-[#2F305A] 
          md:flex flex-col w-64 p-4 transition-transform
          ${open ? "translate-x-0" : "-translate-x-full"} md:translate-x-0 z-40
        `}
      >
     
     <div className="relative border-b border-gray-700 flex font-bold h-16 justify-center items-center text-2xl text-white">
  {open && (
    <button
      className="md:hidden absolute border p-1 rounded-full text-2xl text-pink-400 z-50 left-60 ml-2"
      onClick={() => setOpen(!open)}
    >
      <AiOutlineClose />
    </button>
  )}

  <span>Trenpin</span>
</div>
 
        <nav className="flex flex-col space-y-2">
          {navItems.map(({ href, icon, label }) => {
            const isActive = window.location.pathname === href;
            return (
              <Link
                key={href}
                href={href}
                className={`${baseClasses} ${isActive ? activeClasses : "hover:bg-indigo-600"}`}
              >
                <span className={isActive ? activeColorClasses : baseColorClasses}>{icon}</span>
                <span className={isActive ? activeColorClasses : baseColorClasses}>
                  {label}
                </span>
              </Link>
            );
          })}
        </nav>
      </aside>

      {open && (
        <div
          className="fixed inset-0 bg-black opacity-50 z-40 md:hidden"
          onClick={() => setOpen(false)}
        ></div>
      )}
    </>
  );
}
