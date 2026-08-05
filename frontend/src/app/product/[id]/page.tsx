"use client";

import { useState, use } from "react";
import { useQuery } from "@tanstack/react-query";
import api from "@/lib/api";
import { Product } from "@/types";
import { useCartStore } from "@/store/cartStore";
import { useWishlistStore } from "@/store/wishlistStore";
import ProductCard from "@/components/ProductCard";
import { Heart, Plus, Minus, Info, Loader2 } from "lucide-react";
import { getProductFallbackImage } from "@/lib/imageHelper";

interface ProductPageProps {
  params: Promise<{ id: string }>;
}

export default function ProductDetail({ params }: ProductPageProps) {
  // Resolve params using React.use() wrapper for Next.js App Router rules
  const resolvedParams = use(params);
  const productId = resolvedParams.id;

  const [activeImage, setActiveImage] = useState("");
  const [quantity, setQuantity] = useState(1);
  const [selectedSize, setSelectedSize] = useState("");
  const [selectedColor, setSelectedColor] = useState("");

  const addItem = useCartStore((state) => state.addItem);
  const toggleWishlist = useWishlistStore((state) => state.toggleWishlist);
  const isInWishlist = useWishlistStore((state) => state.isInWishlist(parseInt(productId)));

  const assetUrl = process.env.NEXT_PUBLIC_ASSET_URL || "http://127.0.0.1:8000";

  // 1. Fetch Product details
  const { data: product, isLoading } = useQuery<Product>({
    queryKey: ["product-detail", productId],
    queryFn: async () => {
      const res = await api.get(`/products/${productId}`);
      return res.data?.data;
    },
  });

  // 2. Fetch Related Products
  const { data: relatedProducts } = useQuery<Product[]>({
    queryKey: ["related-products", product?.category_id],
    enabled: !!product?.category_id,
    queryFn: async () => {
      const res = await api.get(`/products?category_id=${product?.category_id}`);
      return (res.data?.data?.data as Product[] || []).filter(p => p.id !== parseInt(productId));
    },
  });

  if (isLoading) {
    return (
      <div className="h-screen flex items-center justify-center">
        <Loader2 className="animate-spin text-gray-300" size={32} />
      </div>
    );
  }

  if (!product) {
    return (
      <div className="h-screen flex flex-col items-center justify-center text-center space-y-4">
        <h3 className="font-serif text-xl italic text-gray-500">Product not found</h3>
      </div>
    );
  }

  // Pre-calculate image list
  const defaultMain = product.web_main_image
    ? `${assetUrl}/uploads/products/${product.web_main_image}`
    : product.image
    ? `${assetUrl}/uploads/products/${product.image}`
    : getProductFallbackImage(product.id);

  const allImages = [defaultMain];
  if (product.web_images) {
    product.web_images.forEach(img => {
      allImages.push(`${assetUrl}/uploads/products/${img.image_path}`);
    });
  }

  const currentImage = activeImage || defaultMain;

  // 3. Parse Sizes and Colors from product.color JSON
  let availableSizes: string[] = [];
  let availableColors: string[] = [];

  if (product.color) {
    try {
      const parsed = JSON.parse(product.color);
      if (Array.isArray(parsed) && parsed.length > 0) {
        if (typeof parsed[0] === "string") {
          availableColors = parsed;
        } else if (typeof parsed[0] === "object") {
          const sizeSet = new Set<string>();
          const colorSet = new Set<string>();
          parsed.forEach((v: any) => {
            if (v.size && v.size !== "-") sizeSet.add(v.size);
            if (v.color && v.color !== "-") colorSet.add(v.color);
          });
          availableSizes = Array.from(sizeSet);
          availableColors = Array.from(colorSet);
        }
      }
    } catch (e) {
      console.error("Failed to parse product colors/variants", e);
    }
  }

  // Pre-select first options if available and nothing is selected
  if (availableSizes.length > 0 && !selectedSize) {
    setSelectedSize(availableSizes[0]);
  }
  if (availableColors.length > 0 && !selectedColor) {
    setSelectedColor(availableColors[0]);
  }

  return (
    <div className="max-w-[1400px] mx-auto px-6 py-28 space-y-24 select-none">
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-16">
        
        {/* Left Side: Product Gallery */}
        <div className="space-y-4">
          {/* Main Large Image */}
          <div className="relative aspect-[3/4] overflow-hidden bg-gray-50 border border-gray-100 rounded-lg">
            <img
              src={currentImage}
              alt={product.item_name}
              className="w-full h-full object-cover transition-all duration-300"
            />
          </div>

          {/* Thumbnails list */}
          {allImages.length > 1 && (
            <div className="flex gap-3 overflow-x-auto pb-2">
              {allImages.map((imgUrl, index) => (
                <button
                  key={index}
                  onClick={() => setActiveImage(imgUrl)}
                  className={`w-20 h-24 relative overflow-hidden bg-gray-50 border transition-all duration-200 ${
                    currentImage === imgUrl ? "border-black scale-95" : "border-gray-200"
                  }`}
                >
                  <img src={imgUrl} alt="thumbnail" className="w-full h-full object-cover" />
                </button>
              ))}
            </div>
          )}
        </div>

        {/* Right Side: Product Details info */}
        <div className="space-y-8 font-sans">
          <div className="space-y-2">
            <span className="text-[10px] uppercase tracking-[0.25em] text-gray-400 font-bold block">
              {product.category_relation?.name || "Apparel"}
            </span>
            <h1 className="font-serif text-3xl sm:text-4xl tracking-wide uppercase font-light text-black">
              {product.item_name}
            </h1>
            <p className="text-[11px] text-gray-400 font-mono tracking-wider">SKU: {product.item_code}</p>
          </div>

          {/* Price */}
          <div className="flex items-baseline gap-3">
            <span className="text-2xl font-bold text-black">
              Rs. {product.final_price.toLocaleString()}
            </span>
            {product.web_sale_price && product.sale_price_per_piece && (
              <span className="text-base text-gray-400 line-through font-light">
                Rs. {product.sale_price_per_piece.toLocaleString()}
              </span>
            )}
          </div>

          <div className="border-t border-b border-gray-100 py-6 space-y-6">
            {/* Sizes */}
            {availableSizes.length > 0 && (
              <div className="space-y-3">
                <div className="flex justify-between items-center text-xs">
                  <span className="font-bold uppercase tracking-wider text-black">Select Size</span>
                  <span className="text-gray-400 underline cursor-pointer hover:text-black">Size Guide</span>
                </div>
                <div className="flex gap-2 flex-wrap">
                  {availableSizes.map((sz) => (
                    <button
                      key={sz}
                      onClick={() => setSelectedSize(sz)}
                      className={`border px-5 py-2.5 text-xs font-semibold tracking-wider transition-all duration-300 uppercase cursor-pointer ${
                        selectedSize === sz
                          ? "border-black bg-black text-white"
                          : "border-gray-200 text-gray-500 hover:border-black"
                      }`}
                    >
                      {sz}
                    </button>
                  ))}
                </div>
              </div>
            )}

            {/* Colors */}
            {availableColors.length > 0 && (
              <div className="space-y-3">
                <span className="text-xs font-bold uppercase tracking-wider text-black block">Select Color</span>
                <div className="flex gap-2 flex-wrap">
                  {availableColors.map((col) => (
                    <button
                      key={col}
                      onClick={() => setSelectedColor(col)}
                      className={`border px-4 py-2.5 text-xs font-semibold tracking-wider transition-all duration-300 uppercase cursor-pointer ${
                        selectedColor === col
                          ? "border-black bg-black text-white"
                          : "border-gray-200 text-gray-500 hover:border-black"
                      }`}
                    >
                      {col}
                    </button>
                  ))}
                </div>
              </div>
            )}
            
            {availableSizes.length === 0 && availableColors.length === 0 && (
              <div className="text-sm text-gray-500 italic">No specific size or color variations available.</div>
            )}
          </div>

          {/* Out of Stock Alert */}
          {product.total_stock !== undefined && product.total_stock <= 0 && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3.5 text-xs uppercase font-bold tracking-widest text-center">
              This Product is Out of Stock
            </div>
          )}

          {/* Action Row: Quantity + Add To Cart */}
          <div className="flex gap-4">
            {/* Quantity Controls */}
            <div className={`flex items-center border border-gray-200 ${
              product.total_stock !== undefined && product.total_stock <= 0 ? "opacity-40 pointer-events-none" : ""
            }`}>
              <button
                disabled={product.total_stock !== undefined && product.total_stock <= 0}
                onClick={() => setQuantity(Math.max(1, quantity - 1))}
                className="px-4 py-3.5 text-gray-400 hover:text-black transition-colors"
              >
                <Minus size={14} />
              </button>
              <span className="px-4 text-sm text-black font-bold">
                {product.total_stock !== undefined && product.total_stock <= 0 ? 0 : quantity}
              </span>
              <button
                disabled={product.total_stock !== undefined && product.total_stock <= 0}
                onClick={() => setQuantity(quantity + 1)}
                className="px-4 py-3.5 text-gray-400 hover:text-black transition-colors"
              >
                <Plus size={14} />
              </button>
            </div>

            {/* Add to Cart button */}
            <button
              disabled={product.total_stock !== undefined && product.total_stock <= 0}
              onClick={() => {
                if (product.total_stock !== undefined && product.total_stock <= 0) return;
                addItem(product, quantity, selectedSize, selectedColor);
              }}
              className={`flex-1 text-center py-4 text-xs font-sans uppercase tracking-[0.2em] font-semibold transition-colors ${
                product.total_stock !== undefined && product.total_stock <= 0
                  ? "bg-gray-200 text-gray-400 cursor-not-allowed"
                  : "bg-black text-white hover:bg-neutral-800 cursor-pointer"
              }`}
            >
              {product.total_stock !== undefined && product.total_stock <= 0 ? "Out of Stock" : "Add To Bag"}
            </button>

            {/* Wishlist Button */}
            <button
              onClick={() => toggleWishlist(product)}
              className="border border-gray-200 p-4 hover:border-black text-gray-400 hover:text-black transition-colors cursor-pointer"
            >
              <Heart size={20} fill={isInWishlist ? "#ff3b30" : "none"} stroke={isInWishlist ? "#ff3b30" : "currentColor"} />
            </button>
          </div>

          {/* Accordion / Informative cards */}
          <div className="space-y-4 pt-6 border-t border-gray-100">
            <div className="space-y-1">
              <h4 className="text-xs font-bold uppercase tracking-wider text-black flex items-center gap-1.5">
                <Info size={14} /> Description & Care
              </h4>
              <p className="text-xs text-gray-500 leading-relaxed font-sans font-light">
                {product.description || "Designed as part of our premium modern luxury apparel collection, this piece stands out with premium stitching and elegant cuts. Hand wash or machine wash cold on delicate cycle."}
              </p>
            </div>
            
            <div className="space-y-1">
              <h4 className="text-xs font-bold uppercase tracking-wider text-black">Delivery & Shipping</h4>
              <p className="text-xs text-gray-500 leading-relaxed font-sans font-light">
                Complimentary shipping on orders over Rs. 10,000. Delivered in premium signature boxes. Typically ships within 2-4 business days.
              </p>
            </div>

            <div className="space-y-1">
              <h4 className="text-xs font-bold uppercase tracking-wider text-black">Return Policy</h4>
              <p className="text-xs text-gray-500 leading-relaxed font-sans font-light">
                We accept returns of unworn items within 14 days of receipt. Sale and promotional products are eligible for size exchanges only.
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Related Products Grid */}
      {relatedProducts && relatedProducts.length > 0 && (
        <div className="space-y-12">
          <div className="border-b border-gray-100 pb-4 text-center sm:text-left">
            <h2 className="font-serif text-2xl tracking-widest uppercase font-light">Related Products</h2>
            <p className="text-[10px] text-gray-400 uppercase tracking-widest font-sans">Complete the look</p>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            {relatedProducts.slice(0, 4).map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
