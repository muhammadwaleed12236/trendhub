"use client";

import { useQuery } from "@tanstack/react-query";
import { useEffect, useRef } from "react";
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

  const scrollCategories = (direction: "left" | "right") => {
    if (sliderRef.current) {
      const scrollAmount = window.innerWidth < 768 ? 240 : 320;
      sliderRef.current.scrollBy({ left: direction === "left" ? -scrollAmount : scrollAmount, behavior: "smooth" });
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

  // Filter products by tags
  const newArrivals = homeProducts?.filter(p => p.promo_tag === "New Arrival") || [];
  const trendingProducts = homeProducts?.filter(p => p.promo_tag === "Trending") || [];
  const bestSellers = homeProducts?.filter(p => p.promo_tag === "Best Seller") || [];
  const flashSale = homeProducts?.filter(p => p.promo_tag === "Flash Sale") || [];

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
          {/* Slight light overlay for better black text readability on video */}
          <div className="absolute inset-0 bg-white/35"></div>
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

      {/* 2. DYNAMIC CATEGORY SHOWCASE (REFERENCE MATCH) */}
      <section className="w-full bg-white py-16 sm:py-24 overflow-hidden border-t border-gray-100">
        <div className="max-w-[1500px] mx-auto px-4 sm:px-6 flex flex-col lg:flex-row items-center">
          
          {/* Left: Large Featured Image */}
          <div className="w-full lg:w-[35%] xl:w-[30%] relative aspect-[4/5] sm:aspect-[3/4] lg:aspect-[4/5] bg-gray-200 overflow-hidden group rounded-2xl z-0">
            <img 
              src={settings?.web_home_banner_image ? `${process.env.NEXT_PUBLIC_ASSET_URL || "http://127.0.0.1:8000"}/${settings.web_home_banner_image}` : "https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?q=80&w=800"} 
              alt="Featured Collection" 
              className="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105"
              loading="lazy"
            />
            <div className="absolute inset-0 bg-black/10 transition-opacity duration-300 group-hover:bg-black/20"></div>
          </div>

          {/* Right: Category Slider (Overlapping) */}
          <div className="w-full lg:w-[70%] flex flex-col justify-center pt-8 lg:pt-0 lg:-ml-12 z-10">
            
            {/* Header & Controls */}
            <div className="flex flex-col sm:flex-row justify-between items-center mb-8 space-y-4 sm:space-y-0 p-2 lg:p-0">
              <div className="space-y-1 text-center sm:text-left mx-auto">
                <h2 className="font-serif text-3xl sm:text-4xl lg:text-5xl tracking-wide font-light text-black">
                  Men's Collections
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
              
              {categories && categories.length > 0 ? categories.map((cat, index) => {
                const fallbackImages = [
                  "https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=600",
                  "https://images.unsplash.com/photo-1485968579580-b6d095142e6e?q=80&w=600",
                  "https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=600",
                  "https://images.unsplash.com/photo-1509631179647-0177331693ae?q=80&w=600"
                ];
                
                const backendUrl = "http://127.0.0.1:8000";
                // web_image_url might already start with http if asset() included it, but if it starts with /, we prepend the backend URL.
                let imageUrl = fallbackImages[index % fallbackImages.length];
                if (cat.web_image_url) {
                  imageUrl = cat.web_image_url.startsWith("http") 
                    ? cat.web_image_url 
                    : `${backendUrl}${cat.web_image_url.startsWith('/') ? '' : '/'}${cat.web_image_url}`;
                }

                return (
                  <Link
                    key={cat.id}
                    href={`/shop?category_id=${cat.id}`}
                    className="group relative flex-none w-[200px] sm:w-[240px] md:w-[260px] aspect-[3/4] bg-gray-100 overflow-hidden snap-start block rounded-xl shadow-md"
                  >
                    <img
                      src={imageUrl}
                      alt={cat.name}
                      className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                      loading="lazy"
                    />
                    
                    {/* Text background overlay like reference (small dark translucent box) */}
                    <div className="absolute bottom-3 left-1/2 -translate-x-1/2">
                      <div className="bg-black/40 backdrop-blur-md px-4 py-1.5 text-center rounded-sm">
                        <h3 className="font-sans text-xs sm:text-sm text-white font-medium">
                          {cat.name}
                        </h3>
                      </div>
                    </div>
                  </Link>
                );
              }) : (
                [...Array(4)].map((_, i) => (
                  <div key={i} className="flex-none w-[220px] sm:w-[260px] md:w-[280px] aspect-[3/4] bg-gray-200 animate-pulse snap-start"></div>
                ))
              )}
            </div>

          </div>
        </div>
      </section>

      {/* 3. NEW ARRIVALS */}
      {newArrivals.length > 0 && (
        <section className="bg-[#fafafa] py-24">
          <div className="max-w-[1400px] mx-auto px-6 space-y-12">
            <div className="flex justify-between items-end border-b border-gray-100 pb-4">
              <div className="space-y-1">
                <h2 className="font-serif text-2xl tracking-widest uppercase font-light">New Arrivals</h2>
                <p className="text-[10px] text-gray-400 uppercase tracking-widest font-sans">Freshly dropped additions</p>
              </div>
              <Link
                href="/shop?promo_tag=New Arrival"
                className="text-xs uppercase tracking-widest font-sans flex items-center gap-1.5 hover:underline font-semibold"
              >
                View All <ArrowRight size={12} />
              </Link>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {newArrivals.slice(0, 4).map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          </div>
        </section>
      )}

      {/* 4. TRENDING PRODUCTS */}
      {trendingProducts.length > 0 && (
        <section className="py-24">
          <div className="max-w-[1400px] mx-auto px-6 space-y-12">
            <div className="flex justify-between items-end border-b border-gray-100 pb-4">
              <div className="space-y-1">
                <h2 className="font-serif text-2xl tracking-widest uppercase font-light">Trending Now</h2>
                <p className="text-[10px] text-gray-400 uppercase tracking-widest font-sans">Most wanted luxury essentials</p>
              </div>
              <Link
                href="/shop?promo_tag=Trending"
                className="text-xs uppercase tracking-widest font-sans flex items-center gap-1.5 hover:underline font-semibold"
              >
                View All <ArrowRight size={12} />
              </Link>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {trendingProducts.slice(0, 4).map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
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
        <section className="py-24 bg-[#fafafa]">
          <div className="max-w-[1400px] mx-auto px-6 space-y-12">
            <div className="flex justify-between items-end border-b border-gray-100 pb-4">
              <div className="space-y-1">
                <h2 className="font-serif text-2xl tracking-widest uppercase font-light">Best Sellers</h2>
                <p className="text-[10px] text-gray-400 uppercase tracking-widest font-sans">Our signature styles</p>
              </div>
              <Link
                href="/shop?promo_tag=Best Seller"
                className="text-xs uppercase tracking-widest font-sans flex items-center gap-1.5 hover:underline font-semibold"
              >
                View All <ArrowRight size={12} />
              </Link>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {bestSellers.slice(0, 4).map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
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
        {[
          "https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=400",
          "https://images.unsplash.com/photo-1529139574466-a303027c1d8b?q=80&w=400",
          "https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=400",
          "https://images.unsplash.com/photo-1509631179647-0177331693ae?q=80&w=400",
          "https://images.unsplash.com/photo-1496345875659-11f7dd282d1d?q=80&w=400"
        ].map((url, idx) => (
          <div key={idx} className="relative aspect-square overflow-hidden group bg-gray-50">
            <img src={url} alt="Instagram" className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
            <div className="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
              <span className="text-white text-xs uppercase tracking-widest font-sans font-bold">@TrendHub</span>
            </div>
          </div>
        ))}
      </section>
      </div>
    </div>
  );
}
