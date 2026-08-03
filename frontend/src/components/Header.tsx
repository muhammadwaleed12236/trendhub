"use client";

import { useState, useEffect } from "react";
import { Search, Heart, User, ShoppingBag, Menu, X } from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useCartStore } from "@/store/cartStore";
import { useWishlistStore } from "@/store/wishlistStore";
import { useAuthStore } from "@/store/authStore";
import CartSidebar from "./CartSidebar";
import AnnouncementBar from "./AnnouncementBar";
import { useSettings } from "@/hooks/useSettings";

export default function Header() {
  const pathname = usePathname();
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isCartOpen, setIsCartOpen] = useState(false);
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const [isMounted, setIsMounted] = useState(false);

  const cartItemsCount = useCartStore((state) => state.getTotalItems());
  const wishlistItemsCount = useWishlistStore((state) => state.items.length);
  const { user, checkAuth } = useAuthStore();

  const { data: settings, isLoading, error } = useSettings();
  console.log("Header settings payload:", { settings, isLoading, error });

  useEffect(() => {
    setIsMounted(true);
    checkAuth();
  }, [checkAuth]);

  // Scroll handler for transparent/white header logic
  useEffect(() => {
    const handleScroll = () => {
      if (window.scrollY > 50) {
        setIsScrolled(true);
      } else {
        setIsScrolled(false);
      }
    };

    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  const isHome = pathname === "/";

  return (
    <>
      <header className="w-full z-40">
        <AnnouncementBar />

        {/* Main Navbar */}
        <nav
          className={`w-full transition-all duration-300 border-b border-gray-100 ${
            isHome
              ? isScrolled
                ? "fixed top-0 bg-white/95 backdrop-blur-md shadow-sm"
                : "absolute bg-transparent text-white border-transparent"
              : "sticky top-0 bg-white/95 backdrop-blur-md shadow-sm"
          }`}
        >
          <div className="max-w-[1400px] mx-auto px-6 h-20 flex items-center justify-between">
            {/* Left: Hamburger menu for mobile & Shop links for desktop */}
            <div className="flex items-center gap-3 sm:gap-6">
              <button
                onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
                className="lg:hidden p-1 hover:bg-gray-100/10 rounded-md cursor-pointer"
              >
                {isMobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
              </button>

              <button
                onClick={() => setIsSearchOpen(true)}
                className="lg:hidden p-1 hover:bg-gray-100/10 rounded-md cursor-pointer"
              >
                <Search size={20} />
              </button>

              <div className="hidden lg:flex items-center gap-8 text-[11px] uppercase tracking-[0.2em] font-sans font-semibold">
                <Link
                  href="/shop"
                  className={`hover:opacity-60 transition-opacity ${
                    pathname === "/shop" ? "underline underline-offset-4 decoration-2" : ""
                  }`}
                >
                  Shop All
                </Link>
                <Link href="/shop?promo_tag=Featured" className="hover:opacity-60 transition-opacity">
                  Featured
                </Link>
                <Link href="/shop?promo_tag=New Arrival" className="hover:opacity-60 transition-opacity">
                  New Arrivals
                </Link>
                <Link href="/shop?promo_tag=Trending" className="hover:opacity-60 transition-opacity">
                  Trending
                </Link>
              </div>
            </div>

            {/* Center: Brand Logo */}
            <div className="absolute left-1/2 -translate-x-1/2">
              <Link href="/" className="font-serif text-lg sm:text-2xl tracking-[0.2em] sm:tracking-[0.25em] font-bold text-inherit uppercase flex items-center justify-center">
                {settings?.web_site_logo ? (
                  <img 
                    src={`http://127.0.0.1:8000/${settings.web_site_logo}`} 
                    alt={settings?.web_site_name || "TrendHub"} 
                    className={`h-10 sm:h-14 object-contain transition-all duration-300 ${
                      (!isScrolled && pathname === "/" && !isMobileMenuOpen) ? "brightness-0 invert mix-blend-screen" : ""
                    }`}
                  />
                ) : (
                  settings?.web_site_name || "TrendHub"
                )}
              </Link>
            </div>

            {/* Right: Actions */}
            <div className="flex items-center gap-3 sm:gap-5">
              <button
                onClick={() => setIsSearchOpen(true)}
                className="hidden lg:block p-1.5 hover:bg-gray-100/20 rounded-full transition-colors cursor-pointer"
              >
                <Search size={20} />
              </button>

              <Link
                href={user ? "/dashboard" : "/login"}
                className="p-1.5 hover:bg-gray-100/20 rounded-full transition-colors hidden sm:block"
              >
                <User size={20} />
              </Link>

              <Link
                href="/wishlist"
                className="p-1.5 hover:bg-gray-100/20 rounded-full transition-colors relative"
              >
                <Heart size={20} />
                {isMounted && wishlistItemsCount > 0 && (
                  <span className="absolute top-0 right-0 w-4 h-4 bg-black text-white rounded-full flex items-center justify-center text-[9px] font-sans">
                    {wishlistItemsCount}
                  </span>
                )}
              </Link>

              <button
                onClick={() => setIsCartOpen(true)}
                className="p-1.5 hover:bg-gray-100/20 rounded-full transition-colors relative cursor-pointer"
              >
                <ShoppingBag size={20} />
                {isMounted && cartItemsCount > 0 && (
                  <span className="absolute top-0 right-0 w-4 h-4 bg-black text-white rounded-full flex items-center justify-center text-[9px] font-sans">
                    {cartItemsCount}
                  </span>
                )}
              </button>
            </div>
          </div>
        </nav>

        {/* Mobile Menu Drawer */}
        {isMobileMenuOpen && (
          <div className="fixed inset-0 bg-white z-50 lg:hidden flex flex-col pt-10 px-8">
            <div className="flex justify-between items-center mb-10">
              <span className="font-serif text-2xl tracking-widest font-bold">MENU</span>
              <button onClick={() => setIsMobileMenuOpen(false)} className="p-2 border border-gray-100 rounded-full">
                <X size={20} />
              </button>
            </div>
            <div className="flex flex-col gap-6 text-lg font-serif tracking-wider">
              <Link href="/shop" onClick={() => setIsMobileMenuOpen(false)}>Shop All</Link>
              <Link href="/shop?promo_tag=Featured" onClick={() => setIsMobileMenuOpen(false)}>Featured</Link>
              <Link href="/shop?promo_tag=New Arrival" onClick={() => setIsMobileMenuOpen(false)}>New Arrivals</Link>
              <Link href="/shop?promo_tag=Trending" onClick={() => setIsMobileMenuOpen(false)}>Trending</Link>
              <Link href="/wishlist" onClick={() => setIsMobileMenuOpen(false)}>
                Wishlist ({isMounted ? wishlistItemsCount : 0})
              </Link>
              <Link href="/dashboard" onClick={() => setIsMobileMenuOpen(false)}>My Account</Link>
            </div>
          </div>
        )}

        {/* Search Overlay */}
        {isSearchOpen && (
          <div className="fixed inset-0 bg-white z-50 flex flex-col p-6">
            <div className="max-w-[1000px] mx-auto w-full mt-10">
              <div className="flex justify-between items-center border-b border-black pb-4">
                <input
                  type="text"
                  placeholder="SEARCH FOR LUXURY APPAREL..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === "Enter" && searchQuery.trim() !== "") {
                      setIsSearchOpen(false);
                      window.location.href = `/shop?search=${encodeURIComponent(searchQuery)}`;
                    }
                  }}
                  className="w-full bg-transparent text-xl font-serif tracking-widest focus:outline-none placeholder-gray-300 text-black uppercase"
                  autoFocus
                />
                <button onClick={() => setIsSearchOpen(false)} className="p-2">
                  <X size={24} />
                </button>
              </div>
              <div className="mt-8">
                <h5 className="text-xs font-semibold tracking-widest text-gray-400 uppercase font-sans">Popular Searches</h5>
                <div className="flex flex-wrap gap-3 mt-4">
                  {["Formal Shirts", "Polo Shirts", "Denim Jackets", "Trousers"].map((term) => (
                    <Link
                      key={term}
                      href={`/shop?search=${term}`}
                      onClick={() => setIsSearchOpen(false)}
                      className="border border-gray-200 px-4 py-2 text-xs font-sans uppercase tracking-wider hover:border-black transition-colors"
                    >
                      {term}
                    </Link>
                  ))}
                </div>
              </div>
            </div>
          </div>
        )}
      </header>

      {/* Cart Sidebar Drawer */}
      <CartSidebar isOpen={isCartOpen} onClose={() => setIsCartOpen(false)} />
    </>
  );
}
