"use client";

import { useState } from "react";
import { Product } from "@/types";
import { Heart, ShoppingBag, Eye } from "lucide-react";
import { useWishlistStore } from "@/store/wishlistStore";
import { useCartStore } from "@/store/cartStore";
import Link from "next/link";
import { getProductFallbackImage } from "@/lib/imageHelper";

interface ProductCardProps {
  product: Product;
}

export default function ProductCard({ product }: ProductCardProps) {
  const [isHovered, setIsHovered] = useState(false);
  const toggleWishlist = useWishlistStore((state) => state.toggleWishlist);
  const isInWishlist = useWishlistStore((state) => state.isInWishlist(product.id));
  const addItem = useCartStore((state) => state.addItem);

  const assetUrl = process.env.NEXT_PUBLIC_ASSET_URL || "http://127.0.0.1:8000";

  // Compute Primary and Secondary Image urls
  const mainImage = product.web_main_image
    ? `${assetUrl}/uploads/products/${product.web_main_image}`
    : product.image
    ? `${assetUrl}/uploads/products/${product.image}`
    : getProductFallbackImage(product.id);

  // Use first gallery image as secondary/hover image if available
  let hoverImage = mainImage;
  if (product.web_images && product.web_images.length > 0) {
    hoverImage = `${assetUrl}/uploads/products/${product.web_images[0].image_path}`;
  }

  // Calculate discount percentage
  let discountPercent = 0;
  if (product.web_sale_price && product.sale_price_per_piece) {
    const diff = product.sale_price_per_piece - product.web_sale_price;
    discountPercent = Math.round((diff / product.sale_price_per_piece) * 100);
  }

  // Helper to determine fabric / variant details overlay
  const getOverlayText = () => {
    if (product.color) {
      try {
        const parsed = JSON.parse(product.color);
        const item = parsed[0];
        if (item) {
          if (typeof item === "object") {
            return item.color || item.name || "Texture";
          }
          return String(item);
        }
      } catch (e) {
        if (typeof product.color === "string") {
          return product.color;
        }
      }
    }

    const nameLower = product.item_name.toLowerCase();
    if (nameLower.includes("polo")) return "Micro Zig Zag";
    if (nameLower.includes("shirt")) return "Seer Sucker Lycra";
    if (nameLower.includes("jacket")) return "Drop Needle Jersey";
    if (nameLower.includes("trouser")) return "Stretch Fit";
    if (nameLower.includes("cargo")) return "Neoprene";
    
    return "Texture";
  };

  return (
    <div
      className="group w-full flex flex-col relative overflow-hidden bg-white select-none"
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      {/* Discount & Status Badges */}
      <div className="absolute top-2 left-2 z-10 flex flex-col gap-1 pointer-events-none">
        {discountPercent > 0 && (
          <span className="bg-[#7a0f12] text-white text-[10px] px-2.5 py-1 font-sans font-bold self-start uppercase">
            -{discountPercent}%
          </span>
        )}
        {product.total_stock !== undefined && product.total_stock <= 0 && (
          <span className="bg-[#222222] text-white text-[9px] uppercase tracking-wider px-2 py-1 font-sans font-bold self-start mt-0.5">
            Out of Stock
          </span>
        )}
      </div>

      {/* Image Container */}
      <div className="relative aspect-[3/4] overflow-hidden bg-gray-50 block">
        <Link href={`/product/${product.id}`} className="w-full h-full block">
          <img
            src={isHovered ? hoverImage : mainImage}
            alt={product.item_name}
            className={`w-full h-full object-cover transition-all duration-700 ease-out ${
              product.total_stock !== undefined && product.total_stock <= 0 ? "opacity-60 grayscale-[40%]" : ""
            }`}
          />
        </Link>

        {/* Fabric/Variant Text Overlay on Bottom-Left */}
        <div className="absolute bottom-0 left-0 bg-[#dcdcdc]/70 text-neutral-800 text-[10px] font-sans font-medium py-1 px-3.5 tracking-wide select-none pointer-events-none">
          {getOverlayText()}
        </div>
      </div>

      {/* Product Info */}
      <div className="pt-4 text-center space-y-1.5 px-2">
        <Link
          href={`/product/${product.id}`}
          className="text-xs sm:text-[13px] font-sans uppercase font-semibold text-neutral-800 hover:text-black transition-colors tracking-wide min-h-[32px] sm:min-h-[40px] flex items-center justify-center line-clamp-2"
        >
          {product.item_name}
        </Link>

        {/* Pricing */}
        <div className="flex justify-center items-baseline gap-2 text-[10px] sm:text-xs">
          {discountPercent > 0 && (
            <span className="text-gray-400 line-through font-sans">
              PKR {Math.round(product.sale_price_per_piece || 0).toLocaleString()}
            </span>
          )}
          <span className="text-[#9e6b41] font-bold font-sans">
            PKR {Math.round(product.final_price).toLocaleString()}
          </span>
        </div>
      </div>
    </div>
  );
}
