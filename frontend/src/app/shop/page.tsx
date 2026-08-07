"use client";

import { useState, useEffect, Suspense } from "react";
import { useQuery } from "@tanstack/react-query";
import { useSearchParams, useRouter } from "next/navigation";
import api from "@/lib/api";
import { Product, Category } from "@/types";
import ProductCard from "@/components/ProductCard";
import { SlidersHorizontal, ChevronDown, Loader2 } from "lucide-react";

function ShopCatalog() {
  const searchParams = useSearchParams();
  const router = useRouter();

  // Filter States
  const [selectedCategory, setSelectedCategory] = useState<string>(searchParams.get("category_id") || "");
  const [selectedTag, setSelectedTag] = useState<string>(searchParams.get("promo_tag") || "");
  const [searchQuery, setSearchQuery] = useState<string>(searchParams.get("search") || "");
  const [sortBy, setSortBy] = useState<string>("newest");
  const [isFilterOpen, setIsFilterOpen] = useState(false);

  // Sync state with URL search params changes
  useEffect(() => {
    setSelectedCategory(searchParams.get("category_id") || "");
    setSelectedTag(searchParams.get("promo_tag") || "");
    setSearchQuery(searchParams.get("search") || "");
  }, [searchParams]);

  // Fetch Categories
  const { data: categories } = useQuery({
    queryKey: ["shop-categories"],
    queryFn: async () => {
      const res = await api.get("/categories");
      return res.data?.data as Category[] || [];
    },
  });

  // Fetch Products based on filters
  const { data: productsData, isLoading: loadingProducts } = useQuery({
    queryKey: ["shop-products", selectedCategory, selectedTag, searchQuery],
    queryFn: async () => {
      const params = new URLSearchParams();
      if (selectedCategory) params.append("category_id", selectedCategory);
      if (selectedTag) params.append("promo_tag", selectedTag);
      if (searchQuery) params.append("search", searchQuery);

      const res = await api.get(`/products?${params.toString()}`);
      return res.data?.data?.data as Product[] || [];
    },
  });

  // Client Side Sorting
  const sortedProducts = (() => {
    if (!productsData) return [];
    let items = [...productsData];
    if (sortBy === "price-low") {
      items.sort((a, b) => a.final_price - b.final_price);
    } else if (sortBy === "price-high") {
      items.sort((a, b) => b.final_price - a.final_price);
    } else {
      // Default / Newest (sort by id desc)
      items.sort((a, b) => b.id - a.id);
    }
    return items;
  })();

  const clearFilters = () => {
    setSelectedCategory("");
    setSelectedTag("");
    setSearchQuery("");
    router.push("/shop");
  };

  return (
    <div className="max-w-[1400px] mx-auto px-6 pt-8 pb-20 sm:pt-12 sm:pb-28 space-y-8 select-none">
      
      {/* Title / Banner */}
      <div className="space-y-2 border-b border-gray-100 pb-6">
        <h1 className="font-serif text-3xl sm:text-4xl uppercase tracking-widest font-light">COLLECTIONS</h1>
        <p className="text-xs text-gray-400 uppercase tracking-widest font-sans">
          {searchQuery ? `Search Results for "${searchQuery}"` : "Explore curated modern luxury wardrobe staples"}
        </p>
      </div>

      {/* Filter and Sort bar */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 py-4 border-b border-gray-100 font-sans">
        <div className="flex items-center gap-4">
          <button
            onClick={() => setIsFilterOpen(!isFilterOpen)}
            className="flex items-center gap-2 border border-black px-5 py-2.5 text-[11px] uppercase tracking-widest font-semibold hover:bg-black hover:text-white transition-all duration-300 cursor-pointer"
          >
            <SlidersHorizontal size={14} />
            Filters {isFilterOpen ? "Close" : "Open"}
          </button>
          {(selectedCategory || selectedTag || searchQuery) && (
            <button
              onClick={clearFilters}
              className="text-[11px] uppercase tracking-widest font-bold text-gray-400 hover:text-black transition-colors"
            >
              Clear All ({[selectedCategory, selectedTag, searchQuery].filter(Boolean).length})
            </button>
          )}
        </div>

        <div className="flex items-center gap-2 self-end sm:self-auto">
          <span className="text-[11px] uppercase tracking-wider text-gray-400">Sort By:</span>
          <div className="relative">
            <select
              value={sortBy}
              onChange={(e) => setSortBy(e.target.value)}
              className="appearance-none bg-transparent border-b border-black pr-8 pl-1 py-1 text-xs uppercase tracking-wider focus:outline-none cursor-pointer text-black font-semibold"
            >
              <option value="newest">Newest</option>
              <option value="price-low">Price: Low to High</option>
              <option value="price-high">Price: High to Low</option>
            </select>
            <ChevronDown size={12} className="absolute right-0.5 top-1/2 -translate-y-1/2 pointer-events-none text-black" />
          </div>
        </div>
      </div>

      {/* Main Grid area with filters sidebar */}
      <div className="flex gap-10">
        
        {/* Sidebar Filters */}
        {isFilterOpen && (
          <aside className="w-64 flex-shrink-0 space-y-8 animate-fadeIn font-sans hidden lg:block border-r border-gray-100 pr-8">
            {/* Category Filter */}
            <div className="space-y-3">
              <h4 className="text-[11px] font-bold uppercase tracking-widest text-black">Categories</h4>
              <div className="flex flex-col gap-2.5 text-xs text-gray-500">
                <button
                  onClick={() => setSelectedCategory("")}
                  className={`text-left hover:text-black transition-colors uppercase tracking-wider ${
                    selectedCategory === "" ? "text-black font-bold" : ""
                  }`}
                >
                  All Categories
                </button>
                {categories?.map((cat) => (
                  <button
                    key={cat.id}
                    onClick={() => setSelectedCategory(cat.id.toString())}
                    className={`text-left hover:text-black transition-colors uppercase tracking-wider ${
                      selectedCategory === cat.id.toString() ? "text-black font-bold" : ""
                    }`}
                  >
                    {cat.name}
                  </button>
                ))}
              </div>
            </div>

            {/* Tag Filter */}
            <div className="space-y-3">
              <h4 className="text-[11px] font-bold uppercase tracking-widest text-black">Collections</h4>
              <div className="flex flex-col gap-2.5 text-xs text-gray-500">
                {["Featured", "New Arrival", "Trending", "Best Seller", "Flash Sale"].map((tag) => (
                  <button
                    key={tag}
                    onClick={() => setSelectedTag(tag === selectedTag ? "" : tag)}
                    className={`text-left hover:text-black transition-colors uppercase tracking-wider ${
                      selectedTag === tag ? "text-black font-bold" : ""
                    }`}
                  >
                    {tag}
                  </button>
                ))}
              </div>
            </div>
          </aside>
        )}

        {/* Product Grid Area */}
        <div className="flex-1">
          {loadingProducts ? (
            <div className="h-96 flex items-center justify-center">
              <Loader2 className="animate-spin text-gray-300" size={32} />
            </div>
          ) : sortedProducts.length === 0 ? (
            <div className="h-96 flex flex-col items-center justify-center text-center space-y-4">
              <h3 className="font-serif text-lg text-gray-500 italic">No products found</h3>
              <p className="text-xs text-gray-400 font-sans">Try expanding your filters or search criteria.</p>
              <button
                onClick={clearFilters}
                className="bg-black text-white px-6 py-2.5 text-xs uppercase tracking-widest font-sans hover:bg-neutral-800 transition-colors font-bold"
              >
                Reset Shop
              </button>
            </div>
          ) : (
            <div className="grid grid-cols-2 md:grid-cols-4 gap-5">
              {sortedProducts.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default function Shop() {
  return (
    <Suspense fallback={
      <div className="h-screen flex items-center justify-center">
        <Loader2 className="animate-spin text-gray-300" size={32} />
      </div>
    }>
      <ShopCatalog />
    </Suspense>
  );
}
