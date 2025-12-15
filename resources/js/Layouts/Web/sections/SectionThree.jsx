import { useState, useEffect } from "react";
import { FaChevronRight, FaChevronLeft } from "react-icons/fa6";

// Swiper Imports
import { Swiper, SwiperSlide } from "swiper/react";
import { Navigation } from "swiper/modules";

// Swiper CSS
import "swiper/css";
import "swiper/css/navigation";

export default function SectionThree() {
    const [activeTab, setActiveTab] = useState("restaurants");

    // Tabs
    const tabs = [
        { key: "restaurants", label: "Restaurants", icon: "🍽️" },
        { key: "stores", label: "Stores", icon: "🛍️" },
        { key: "hotels", label: "Hotels", icon: "🏨" },
        { key: "cafes", label: "Cafes", icon: "☕" },
        { key: "bakeries", label: "Bakeries", icon: "🥖" },
        { key: "desserts", label: "Desserts", icon: "🍰" },
    ];

    // Card Groups by Category
    const cardsData = {
        restaurants: [
            {
                title: "Hamada",
                img: "/images/eb1b5de188d1b98a9b452168e6f35a0727baae8c (1).png",
                badge: "Buy 1 Get 1",
                discount: "10% off",
                logo: "/images/hamada-logo.png",
            },
            {
                title: "Al Mousalli",
                img: "/images/55bada35b4600ea63c1c523dd7ae9e11a6516f93.png",
                badge: "Buy 1 Get 1",
                discount: "10% off",
                logo: "/images/mousalli-logo.png",
            },
            {
                title: "Al Mousalli",
                img: "/images/55bada35b4600ea63c1c523dd7ae9e11a6516f93.png",
                badge: "Buy 1 Get 1",
                discount: "10% off",
                logo: "/images/mousalli-logo.png",
            },
        ],

        stores: [
            {
                title: "ZARA",
                img: "/images/store1.png",
                badge: "Sale",
                discount: "30% off",
                logo: "/images/zara-logo.png",
            },
            {
                title: "H&M",
                img: "/images/store2.png",
                badge: "Buy 2 Get 1",
                discount: "20% off",
                logo: "/images/hm-logo.png",
            },
        ],

        hotels: [
            {
                title: "Intercontinental",
                img: "/images/hotel1.png",
                badge: "Free Night",
                discount: "15% off",
                logo: "/images/hotel-logo.png",
            },
            {
                title: "Rotana",
                img: "/images/hotel2.png",
                badge: "Weekend Deal",
                discount: "25% off",
                logo: "/images/rotana-logo.png",
            },
        ],
        cafes: [
            {
                title: "Starbucks",
                img: "/images/cafe1.png",
                badge: "Happy Hour",
                discount: "15% off",
                logo: "/images/starbucks-logo.png",
            },
        ],
        bakeries: [
            {
                title: "Paris Bakery",
                img: "/images/bakery1.png",
                badge: "Fresh Daily",
                discount: "20% off",
                logo: "/images/bakery-logo.png",
            },
        ],
        desserts: [
            {
                title: "Sweet Treats",
                img: "/images/dessert1.png",
                badge: "Buy 2 Get 1",
                discount: "25% off",
                logo: "/images/dessert-logo.png",
            },
        ],
    };

    // Get current category cards
    const cards = cardsData[activeTab] || [];

    // Reset Swiper when changing category
    useEffect(() => {
        const swiperInstance = document.querySelector(".swiper")?.swiper;
        swiperInstance?.slideTo(0);
    }, [activeTab]);

    return (
        <section className="px-6 lg:px-20 py-16 bg-white w-full">
            {/* Header with Title and Navigation */}
            <div className="flex justify-between items-start mb-12">
                {/* Title with decorative element */}
                <div className="relative">
                    <div className="absolute -top-4 -left-4 w-8 h-8 bg-pink-500 rounded-lg transform rotate-45 opacity-20"></div>
                    <h2 className="text-4xl lg:text-5xl font-bold leading-tight">
                        Offers Always Makes
                        <br />
                        You Fall In Love
                    </h2>
                </div>

                {/* Navigation Arrows - Desktop */}
                <div className="hidden md:flex gap-4">
                    <button
                        className="swiper-button-prev-custom w-12 h-12 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition-all duration-300 shadow-md"
                        aria-label="Previous slide"
                    >
                        <FaChevronLeft className="text-gray-700" size={16} />
                    </button>
                    <button
                        className="swiper-button-next-custom w-12 h-12 rounded-full bg-[#E91E63] hover:bg-[#d81b60] flex items-center justify-center transition-all duration-300 shadow-md"
                        aria-label="Next slide"
                    >
                        <FaChevronRight className="text-white" size={16} />
                    </button>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-5 gap-8">
                {/* LEFT TABS - SCROLLABLE */}
                <div className="lg:col-span-1">
                    <div className="space-y-3 max-h-[250px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-pink-300 scrollbar-track-gray-100 hover:scrollbar-thumb-pink-400">
                        {tabs.map((t) => (
                            <button
                                key={t.key}
                                onClick={() => setActiveTab(t.key)}
                                className={`w-full flex items-center gap-3 px-6 py-4 rounded-full text-base font-medium transition-all duration-300 ${
                                    activeTab === t.key
                                        ? "bg-[#E91E63] text-white shadow-lg shadow-pink-200"
                                        : "bg-white border-2 border-gray-100 text-gray-700 hover:border-pink-200 hover:shadow-md"
                                }`}
                            >
                                <span className="text-xl">{t.icon}</span>
                                <span>{t.label}</span>
                            </button>
                        ))}
                    </div>
                </div>

                {/* RIGHT SLIDER */}
                <div className="lg:col-span-4 relative">
                    {/* Vertical Divider Line */}
                    <div className="hidden lg:block absolute left-0 top-0 bottom-0 w-0.5 bg-gradient-to-b from-transparent via-pink-200 to-transparent -ml-4"></div>

                    {/* SWIPER */}
                    <Swiper
                        modules={[Navigation]}
                        spaceBetween={24}
                        slidesPerView={2}
                        navigation={{
                            nextEl: ".swiper-button-next-custom",
                            prevEl: ".swiper-button-prev-custom",
                        }}
                        breakpoints={{
                            0: { slidesPerView: 1 },
                            768: { slidesPerView: 2 },
                            1024: { slidesPerView: 2 },
                        }}
                        className="offers-swiper"
                    >
                        {cards.map((c, index) => (
                            <SwiperSlide key={index}>
                                <div className="group rounded-3xl overflow-hidden shadow-lg bg-white hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                                    {/* Image Container */}
                                    <div className="relative h-72 overflow-hidden">
                                        <img
                                            src={c.img}
                                            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                            alt={c.title}
                                        />

                                        {/* Gradient Overlay */}
                                        <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>

                                        {/* Logo */}
                                        {c.logo && (
                                            <div className="absolute top-4 left-4 w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-lg">
                                                <img
                                                    src={c.logo}
                                                    alt={`${c.title} logo`}
                                                    className="w-12 h-12 object-contain"
                                                />
                                            </div>
                                        )}

                                        {/* Content Overlay */}
                                        <div className="absolute bottom-0 left-0 right-0 p-6 text-white">
                                            <h3 className="text-2xl font-bold mb-3 drop-shadow-lg">
                                                {c.title}
                                            </h3>

                                            {/* Badge */}
                                            <div className="inline-flex items-center gap-2 bg-[#E91E63] px-4 py-2 rounded-full text-sm font-medium shadow-lg mb-3">
                                                <svg
                                                    className="w-4 h-4"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                                <span>{c.badge}</span>
                                            </div>

                                            {/* Discount with payment icons */}
                                            {/* Discount with payment icons */}
                                            <div className="flex items-center gap-3">
                                                {/* Overlapping payment method icons */}
                                                <div className="flex items-center -space-x-2">
                                                    <div className="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center shadow-md ring-2 ring-white z-30">
                                                        <svg
                                                            className="w-4 h-4 text-white"
                                                            fill="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z" />
                                                        </svg>
                                                    </div>
                                                    <div className="w-8 h-8 bg-red-600 rounded-full flex items-center justify-center shadow-md ring-2 ring-white z-20">
                                                        <svg
                                                            className="w-4 h-4 text-white"
                                                            fill="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z" />
                                                        </svg>
                                                    </div>
                                                    <div className="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center shadow-md ring-2 ring-white z-10">
                                                        <svg
                                                            className="w-4 h-4 text-white"
                                                            fill="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z" />
                                                        </svg>
                                                    </div>
                                                    <div className="w-8 h-8 bg-white rounded-full flex items-center justify-center text-gray-800 text-xs font-bold shadow-md ring-2 ring-white">
                                                        +3
                                                    </div>
                                                </div>

                                                {/* Discount badge */}
                                                <span className="font-bold text-lg bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full">
                                                    {c.discount}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </SwiperSlide>
                        ))}
                    </Swiper>

                    {/* Mobile Navigation */}
                    <div className="flex md:hidden justify-center gap-4 mt-6">
                        <button
                            className="swiper-button-prev-custom w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center"
                            aria-label="Previous"
                        >
                            <FaChevronLeft
                                className="text-gray-700"
                                size={16}
                            />
                        </button>
                        <button
                            className="swiper-button-next-custom w-12 h-12 rounded-full bg-[#E91E63] flex items-center justify-center"
                            aria-label="Next"
                        >
                            <FaChevronRight className="text-white" size={16} />
                        </button>
                    </div>
                </div>
            </div>

            {/* Custom Scrollbar Styles */}
            <style jsx>{`
                .scrollbar-thin::-webkit-scrollbar {
                    width: 6px;
                }
                .scrollbar-thin::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 10px;
                }
                .scrollbar-thin::-webkit-scrollbar-thumb {
                    background: #fbb6ce;
                    border-radius: 10px;
                }
                .scrollbar-thin::-webkit-scrollbar-thumb:hover {
                    background: #f687b3;
                }
            `}</style>
        </section>
    );
}
