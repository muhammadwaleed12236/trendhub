"use client";

import { useQuery } from "@tanstack/react-query";
import { useEffect, useRef, useState } from "react";
import api from "@/lib/api";
import { Product, Category } from "@/types";
import ProductCard from "@/components/ProductCard";
import Link from "next/link";
import { ArrowRight, Star, ChevronLeft, ChevronRight } from "lucide-react";
import { motion } from "framer-motion";
import { useSettings } from "@/hooks/useSettings";
import { getProductFallbackImage } from "@/lib/imageHelper";

export default function Home() {
  const { data: settings } = useSettings();
  const sliderRef = useRef<HTMLDivElement>(null);
  const [activeCollectionTab, setActiveCollectionTab] = useState("Men");

  const scrollCategories = (direction: "left" | "right") => {
    if (sliderRef.current) {
      const containerWidth = sliderRef.current.clientWidth;
      const scrollAmount = direction === "left" ? -containerWidth : containerWidth;
      sliderRef.current.scrollBy({ left: scrollAmount, behavior: "smooth" });
    }
  };

  // Fetch products for different sections
  const { data: homeProducts, isLoading: loadingProducts } = useQuery({
    queryKey: ["home-products"],
    queryFn: async () => {
      const res = await api.get("/products?show_on_homepage=1");
      return res.data?.data?.data as Product[] || [];
    },
  });

  const { data: categories } = useQuery({
    queryKey: ["home-categories"],
    queryFn: async () => {
      const res = await api.get("/categories");
      return res.data?.data as Category[] || [];
    },
  });

  // Dispatch event when home products are fetched
  useEffect(() => {
    if (!loadingProducts && homeProducts) {
      (window as any).__productsLoaded = true;
      window.dispatchEvent(new Event("products-loaded"));
    }
  }, [loadingProducts, homeProducts]);

  // Set default active tab once categories are loaded
  useEffect(() => {
    if (categories && categories.length > 0) {
      const hasMen = categories.some(cat => cat.name.toLowerCase() === "men");
      if (!hasMen) {
        setActiveCollectionTab(categories[0].name);
      }
    }
  }, [categories]);

  // Filter products by tags
  const newArrivals = homeProducts?.filter(p => p.promo_tag === "New Arrival") || [];
  const trendingProducts = homeProducts?.filter(p => p.promo_tag === "Trending") || [];
  const bestSellers = homeProducts?.filter(p => p.promo_tag === "Best Seller") || [];
  const flashSale = homeProducts?.filter(p => p.promo_tag === "Flash Sale") || [];

  // Filter products for the active collection tab
  const activeCollectionProducts = homeProducts?.filter(product => {
    if (!activeCollectionTab) return true;

    const nameLower = product.item_name.toLowerCase();
    const catNameLower = product.category_relation?.name?.toLowerCase() || "";
    const activeTabLower = activeCollectionTab.toLowerCase();

    // Exact category name match
    if (catNameLower === activeTabLower) {
      return true;
    }

    // Predefined legacy matching logic
    if (activeTabLower === "men") {
      return nameLower.includes("men") || nameLower.includes("man") || catNameLower.includes("men");
    }
    if (activeTabLower === "women") {
      return nameLower.includes("women") || nameLower.includes("lady") || catNameLower.includes("women");
    }
    if (activeTabLower === "boys") {
      return nameLower.includes("boys") || nameLower.includes("boy") || catNameLower.includes("boys");
    }
    if (activeTabLower === "girls") {
      return nameLower.includes("girls") || nameLower.includes("girl") || catNameLower.includes("girls");
    }

    // Sub-string fallback
    return nameLower.includes(activeTabLower);
  }) || [];

  // Fallback to top products if active tag has zero items
  const displayProducts = activeCollectionProducts.length > 0 
    ? activeCollectionProducts 
    : (homeProducts || []).slice(0, 5);

  // Generate 5 items for the Instagram Gallery
  const instagramGalleryItems = (() => {
    const placeholders = [
      "https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=400",
      "https://images.unsplash.com/photo-1529139574466-a303027c1d8b?q=80&w=400",
      "https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=400",
      "https://images.unsplash.com/photo-1509631179647-0177331693ae?q=80&w=400",
      "https://images.unsplash.com/photo-1496345875659-11f7dd282d1d?q=80&w=400"
    ];

    const items = [];
    const productsWithImages = homeProducts ? homeProducts.filter(p => p.web_main_image || p.image) : [];

    const baseUrl = process.env.NEXT_PUBLIC_ASSET_URL || "http://127.0.0.1:8000";

    for (let i = 0; i < 5; i++) {
      if (productsWithImages[i]) {
        const product = productsWithImages[i];
        const image = product.web_main_image
          ? `${baseUrl}/uploads/products/${product.web_main_image}`
          : product.image
          ? `${baseUrl}/uploads/products/${product.image}`
          : getProductFallbackImage(product.id);
        items.push({
          id: product.id,
          image,
          link: `/product/${product.id}`,
          label: product.item_name,
          isPlaceholder: false
        });
      } else {
        items.push({
          id: `placeholder-${i}`,
          image: placeholders[i],
          link: settings?.web_instagram_link || "https://instagram.com",
          label: `@${settings?.web_site_name || "TrendHub"}`,
          isPlaceholder: true
        });
      }
    }
    return items;
  })();

  return (
    <div className="w-full overflow-hidden">
      {/* 1. HERO BANNER WITH FASHION VIDEO */}
      <section className="fixed top-0 left-0 w-full bg-neutral-950 flex flex-col justify-center overflow-hidden h-[100vh] -z-10">
        {/* Full-width Background Autoplay Fashion Video */}
        <div className="absolute inset-0 w-full h-full overflow-hidden">
          <video
            autoPlay
            muted
            loop
            playsInline
            key={settings?.web_home_hero_video || "/hero-video.mp4"}
            className="w-full h-full object-cover scale-[1.03]"
          >
            <source
              src={settings?.web_home_hero_video ? `${process.env.NEXT_PUBLIC_ASSET_URL || "http://127.0.0.1:8000"}/${settings.web_home_hero_video}` : "/hero-video.mp4"}
              type="video/mp4"
            />
          </video>
        </div>

        {/* Hero Content (Pushed down to avoid absolute header overlap) */}
        <div className="relative z-10 flex-1 flex flex-col items-center justify-center text-center text-black px-6 max-w-[800px] mx-auto space-y-6 md:space-y-8 pt-12 sm:pt-16 pb-4">
          <motion.p
            initial={{ opacity: 0, y: 15 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            className="text-[10px] sm:text-xs uppercase tracking-[0.3em] font-sans font-bold text-neutral-800"
          >
          </motion.p>
          <motion.h1
            initial={{ opacity: 0, y: 15 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2 }}
            className="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-serif tracking-[0.25em] uppercase font-light leading-snug"
          >
            {/* TIMELESS ELEGANCE */}
          </motion.h1>
          <motion.p
            initial={{ opacity: 0, y: 15 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4 }}
            className="text-[11px] sm:text-xs md:text-sm font-serif italic text-neutral-800 tracking-[0.1em] font-light max-w-[500px]"
          >
            {/* Discover refined luxury wardrobe staples crafted for modern living. */}
          </motion.p>
          <motion.div
            initial={{ opacity: 0, y: 15 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.6 }}
            className="pt-4 flex justify-center gap-3 sm:gap-4"
          >
            {/* <Link
              href="/shop"
              className="bg-black text-white px-6 sm:px-8 py-3 text-[10px] sm:text-xs font-sans uppercase tracking-[0.2em] font-bold hover:bg-neutral-800 transition-colors shadow-lg hover:scale-105 duration-300"
            >
              Shop Now
            </Link>
            <Link
              href="/shop?promo_tag=Featured"
              className="border border-black text-black px-6 sm:px-8 py-3 text-[10px] sm:text-xs font-sans uppercase tracking-[0.2em] font-bold hover:bg-black hover:text-white transition-all duration-300 shadow-lg"
            >
              Explore Collection
            </Link> */}
          </motion.div>
        </div>
      </section>

      {/* Spacer to push scrollable content below the fixed hero video */}
      <div className="h-[100vh] pointer-events-none"></div>

      {/* Scrollable Content Wrapper that overlaps the fixed hero video */}
      <div className="relative z-10 bg-white w-full shadow-[0_-15px_30px_rgba(0,0,0,0.08)]">
        {/* Continuous Horizontal Product Slider showing ONLY product images */}
        <div className="relative z-10 w-full bg-white/95 backdrop-blur-md border-t border-gray-100 py-0 select-none overflow-hidden">
        <div className="w-full relative flex items-center">
          {/* Slide Track */}
          <div className="animate-infinite-scroll flex gap-1 px-1">
            {/* If products are loaded, display them twice to guarantee a smooth continuous loop */}
            {homeProducts && homeProducts.length > 0 ? (
              [...homeProducts, ...homeProducts, ...homeProducts].map((product, index) => {
                const pMainImage = product.web_main_image
                  ? `${process.env.NEXT_PUBLIC_ASSET_URL || "http://127.0.0.1:8000"}/uploads/products/${product.web_main_image}`
                  : product.image
                  ? `${process.env.NEXT_PUBLIC_ASSET_URL || "http://127.0.0.1:8000"}/uploads/products/${product.image}`
                  : getProductFallbackImage(product.id);
                
                return (
                  <Link
                    key={`${product.id}-${index}`}
                    href={`/product/${product.id}`}
                    className="w-[calc(100vw/2-4px)] sm:w-[calc(100vw/3-4px)] md:w-[calc(100vw/4-4px)] lg:w-[calc(100vw/6-4px)] aspect-[4/5] shrink-0 flex items-center justify-center bg-gray-50 overflow-hidden hover:opacity-90 transition-opacity duration-300 group"
                  >
                    <img
                      src={pMainImage}
                      alt={product.item_name}
                      className="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700 ease-out"
                    />
                  </Link>
                );
              })
            ) : (
              /* Fallbacks if products aren't loaded yet */
              [...Array(15)].map((_, i) => (
                <div
                  key={i}
                  className="w-[calc(100vw/2-4px)] sm:w-[calc(100vw/3-4px)] md:w-[calc(100vw/4-4px)] lg:w-[calc(100vw/6-4px)] aspect-[4/5] shrink-0 bg-gray-100 animate-pulse"
                />
              ))
            )}
          </div>
        </div>
      </div>

      {/* 1.5 FEATURED COLLECTIONS SECTION */}
      <section className="py-20 bg-white">
        <div className="max-w-[1400px] mx-auto px-6 space-y-10 text-center">
          <div className="space-y-6">
            <h2 className="font-serif text-3xl sm:text-4xl tracking-wide font-normal text-neutral-800">
              Featured Collections
            </h2>
            {/* Tab Pills */}
            <div className="flex justify-center gap-2">
              {categories && categories.length > 0 ? (
                categories.map((cat) => (
                  <button
                    key={cat.id}
                    onClick={() => setActiveCollectionTab(cat.name)}
                    className={`px-6 py-2 rounded-full text-xs font-sans font-medium tracking-wider transition-all duration-300 cursor-pointer ${
                      activeCollectionTab === cat.name
                        ? "bg-[#717171] text-white shadow-xs"
                        : "bg-[#f4f4f4] text-[#888888] hover:bg-[#e8e8e8]"
                    }`}
                  >
                    {cat.name}
                  </button>
                ))
              ) : (
                ["Men", "Women", "Boys", "Girls"].map((tab) => (
                  <button
                    key={tab}
                    onClick={() => setActiveCollectionTab(tab)}
                    className={`px-6 py-2 rounded-full text-xs font-sans font-medium tracking-wider transition-all duration-300 cursor-pointer ${
                      activeCollectionTab === tab
                        ? "bg-[#717171] text-white shadow-xs"
                        : "bg-[#f4f4f4] text-[#888888] hover:bg-[#e8e8e8]"
                    }`}
                  >
                    {tab}
                  </button>
                ))
              )}
            </div>
          </div>

          {/* 5-Column Responsive Product Grid */}
          <div className="grid grid-cols-2 md:grid-cols-5 gap-6">
            {displayProducts.slice(0, 5).map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>

          {/* View all products bottom button */}
          <div className="pt-6">
            <Link
              href="/shop"
              className="bg-[#333333] hover:bg-[#222222] text-white px-8 py-3 text-[10px] sm:text-xs uppercase tracking-[0.2em] font-sans font-bold transition-all duration-300 inline-block rounded-xs"
            >
              View all products
            </Link>
          </div>
        </div>
      </section>

      {/* 2. DYNAMIC NEW ARRIVALS SLIDER (REFERENCE MATCH) */}
      {newArrivals && newArrivals.length > 0 && (
        <section className="w-full bg-white py-20 overflow-hidden border-t border-gray-100">
          <div className="max-w-[1400px] mx-auto px-6 flex flex-col lg:flex-row items-center">
            
            {/* Left: Large Featured Image */}
            <div className="w-full lg:w-[35%] xl:w-[30%] relative aspect-[4/5] sm:aspect-[3/4] lg:aspect-[4/5] bg-gray-200 overflow-hidden group rounded-2xl z-0">
              <img 
                src={settings?.web_home_banner_image ? `${process.env.NEXT_PUBLIC_ASSET_URL || "http://127.0.0.1:8000"}/${settings.web_home_banner_image}` : "https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?q=80&w=800"} 
                alt="New Arrivals" 
                className="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105"
                loading="lazy"
              />
              <div className="absolute inset-0 bg-black/10 transition-opacity duration-300 group-hover:bg-black/20"></div>
            </div>

            {/* Right: Category Slider (Overlapping) */}
            <div className="w-full lg:w-[70%] flex flex-col justify-center pt-8 lg:pt-0 lg:-ml-12 z-10 overflow-hidden">
              
              {/* Header & Controls */}
              <div className="flex flex-col sm:flex-row justify-between items-center mb-8 space-y-4 sm:space-y-0 p-2 lg:p-0">
                <div className="space-y-1 text-center sm:text-left mx-auto">
                  <h2 className="font-serif text-3xl sm:text-4xl lg:text-5xl tracking-wide font-light text-black">
                    New Arrivals
                  </h2>
                </div>
                
                {/* Slider Arrows */}
                <div className="flex gap-2">
                  <button 
                    onClick={() => scrollCategories("left")}
                    className="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center hover:bg-black hover:text-white transition-colors disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-black cursor-pointer bg-white"
                  >
                    <ChevronLeft size={20} strokeWidth={1.5} />
                  </button>
                  <button 
                    onClick={() => scrollCategories("right")}
                    className="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center hover:bg-black hover:text-white transition-colors cursor-pointer bg-white"
                  >
                    <ChevronRight size={20} strokeWidth={1.5} />
                  </button>
                </div>
              </div>

              {/* Slider Track */}
              <div 
                ref={sliderRef}
                className="flex gap-4 sm:gap-6 overflow-x-auto snap-x snap-mandatory pb-4 pr-6 lg:pr-12"
                style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}
              >
                {/* Hide webkit scrollbar via inline styles equivalent */}
                <style dangerouslySetInnerHTML={{__html: `::-webkit-scrollbar { display: none; }`}} />
                
                {newArrivals.map((product) => {
                  const pMainImage = product.web_main_image
                    ? `${process.env.NEXT_PUBLIC_ASSET_URL || "http://127.0.0.1:8000"}/uploads/products/${product.web_main_image}`
                    : product.image
                    ? `${process.env.NEXT_PUBLIC_ASSET_URL || "http://127.0.0.1:8000"}/uploads/products/${product.image}`
                    : getProductFallbackImage(product.id);

                  return (
                    <Link
                      key={product.id}
                      href={`/product/${product.id}`}
                      className="group relative flex-none w-full sm:w-[calc((100%-16px)/2)] lg:w-[calc((100%-48px)/3)] aspect-[3/4] bg-gray-100 overflow-hidden snap-start block rounded-xl shadow-md"
                    >
                      <img
                        src={pMainImage}
                        alt={product.item_name}
                        className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        loading="lazy"
                      />
                    </Link>
                  );
                })}
              </div>

            </div>
          </div>
        </section>
      )}

      {/* 3. TRENDING PRODUCTS */}
      {trendingProducts.length > 0 && (
        <section className="bg-[#fafafa] py-20">
          <div className="max-w-[1400px] mx-auto px-6 space-y-10">
            <div className="text-center space-y-4">
              <h2 className="font-serif text-3xl sm:text-4xl tracking-wide font-normal text-neutral-800 uppercase">
                Trending Now
              </h2>
              <p className="text-[10px] text-gray-400 uppercase tracking-widest font-sans">Most wanted luxury essentials</p>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-5 gap-5">
              {trendingProducts.slice(0, 5).map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>

            <div className="pt-6 text-center">
              <Link
                href="/shop?promo_tag=Trending"
                className="bg-[#333333] hover:bg-[#222222] text-white px-8 py-3 text-[10px] sm:text-xs uppercase tracking-[0.2em] font-sans font-bold transition-all duration-300 inline-block rounded-xs"
              >
                View all trending
              </Link>
            </div>
          </div>
        </section>
      )}

      {/* 5. FLASH SALE BANNER */}
      {flashSale.length > 0 && (
        <section className="relative bg-black py-20 text-white text-center space-y-6 select-none overflow-hidden">
          <div className="absolute inset-0 bg-cover bg-center opacity-20 filter grayscale" style={{ backgroundImage: "url('https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=1200')" }}></div>
          <div className="relative z-10 max-w-[800px] mx-auto px-6 space-y-4">
            <span className="bg-red-600 text-white text-[9px] uppercase tracking-widest px-3 py-1 font-bold rounded-sm">Limited Offer</span>
            <h2 className="font-serif text-4xl tracking-widest uppercase font-light">FLASH SALE</h2>
            <p className="text-sm font-serif italic text-neutral-300 font-light max-w-[500px] mx-auto">
              Elevated pieces at exclusive pricing. Only available for a limited time.
            </p>
            <div className="pt-4">
              <Link
                href="/shop?promo_tag=Flash Sale"
                className="bg-white text-black px-8 py-3.5 text-xs font-sans uppercase tracking-[0.2em] font-bold hover:bg-neutral-200 transition-colors inline-block"
              >
                Shop The Sale
              </Link>
            </div>
          </div>
        </section>
      )}

      {/* 6. BEST SELLERS */}
      {bestSellers.length > 0 && (
        <section className="py-20 bg-[#fafafa]">
          <div className="max-w-[1400px] mx-auto px-6 space-y-10">
            <div className="text-center space-y-4">
              <h2 className="font-serif text-3xl sm:text-4xl tracking-wide font-normal text-neutral-800 uppercase">
                Best Sellers
              </h2>
              <p className="text-[10px] text-gray-400 uppercase tracking-widest font-sans">Our signature styles</p>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-5 gap-5">
              {bestSellers.slice(0, 5).map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>

            <div className="pt-6 text-center">
              <Link
                href="/shop?promo_tag=Best Seller"
                className="bg-[#333333] hover:bg-[#222222] text-white px-8 py-3 text-[10px] sm:text-xs uppercase tracking-[0.2em] font-sans font-bold transition-all duration-300 inline-block rounded-xs"
              >
                View all best sellers
              </Link>
            </div>
          </div>
        </section>
      )}

      {/* Default placeholder if no products enabled on homepage yet */}
      {(!homeProducts || homeProducts.length === 0) && !loadingProducts && (
        <section className="py-24 text-center space-y-4">
          <h3 className="font-serif text-2xl uppercase tracking-wider text-gray-400">Welcome to TrendHub Storefront</h3>
          <p className="text-sm text-gray-400 max-w-[450px] mx-auto font-sans leading-relaxed">
            Please enable visibility and "Show on Homepage" for some products in the admin panel Website Settings to build up your homepage grids.
          </p>
          <div className="pt-4">
            <Link
              href="/shop"
              className="bg-black text-white px-8 py-3 text-xs uppercase tracking-widest font-sans hover:bg-neutral-800 transition-colors font-bold"
            >
              Browse All Products
            </Link>
          </div>
        </section>
      )}

      {/* 7. CUSTOMER REVIEWS */}
      <section className="py-24 border-t border-gray-100">
        <div className="max-w-[900px] mx-auto px-6 text-center space-y-8">
          <div className="flex justify-center gap-1 text-black">
            {[...Array(5)].map((_, i) => <Star key={i} size={15} fill="currentColor" />)}
          </div>
          <p className="font-serif text-xl sm:text-2xl italic leading-relaxed text-gray-700 font-light">
            "The minimalist layout and clean black-and-white aesthetic makes shopping feel like browsing a high-end designer showroom. The quality of the clothing is second to none."
          </p>
          <div className="space-y-1">
            <h4 className="font-sans text-xs uppercase tracking-widest font-bold">Sophia Martinez</h4>
            <p className="text-[10px] text-gray-400 font-sans uppercase">Verified Client</p>
          </div>
        </div>
      </section>

      {/* 8. INSTAGRAM GALLERY */}
      <section className="w-full grid grid-cols-2 md:grid-cols-5 gap-1 border-t border-gray-100 pt-1 select-none">
        {instagramGalleryItems.map((item) => (
          <Link
            key={item.id}
            href={item.link}
            target={item.isPlaceholder ? "_blank" : undefined}
            rel={item.isPlaceholder ? "noopener noreferrer" : undefined}
            className="relative aspect-square overflow-hidden group bg-gray-50 block"
          >
            <img
              src={item.image}
              alt={item.label}
              className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
            <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-center p-4 text-center">
              <span className="text-white text-[10px] uppercase tracking-[0.2em] font-sans font-semibold mb-1">
                {item.isPlaceholder ? "Follow Us" : "Shop the Look"}
              </span>
              <span className="text-white text-xs uppercase tracking-widest font-sans font-bold line-clamp-2 px-2">
                {item.label}
              </span>
              {!item.isPlaceholder && (
                <span className="mt-3 border border-white text-white text-[9px] uppercase tracking-widest font-bold px-3 py-1 bg-white/10 hover:bg-white hover:text-black transition-all duration-300">
                  Shop Now
                </span>
              )}
            </div>
          </Link>
        ))}
      </section>
      </div>
    </div>
  );
}
