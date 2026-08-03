"use client";

import { useState } from "react";
import { Product } from "@/types";
import { Heart, ShoppingBag, Eye } from "lucide-react";
import { useWishlistStore } from "@/store/wishlistStore";
import { useCartStore } from "@/store/cartStore";
import Link from "next/link";

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
    : "/placeholder.jpg";

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

  return (
    <div
      className="group w-full flex flex-col relative overflow-hidden bg-white select-none"
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      {/* Badges */}
      <div className="absolute top-3 left-3 z-10 flex flex-col gap-1.5 pointer-events-none">
        {product.promo_tag && (
          <span className="bg-[#111111] text-white text-[9px] uppercase tracking-widest px-2.5 py-1 font-sans font-bold">
            {product.promo_tag}
          </span>
        )}
        {discountPercent > 0 && (
          <span className="bg-[#ff3b30] text-white text-[9px] uppercase tracking-widest px-2.5 py-1 font-sans font-bold">
            Save {discountPercent}%
          </span>
        )}
      </div>

      {/* Wishlist Heart */}
      <button
        onClick={() => toggleWishlist(product)}
        className="absolute top-3 right-3 z-10 p-2 bg-white/80 backdrop-blur-md rounded-full shadow-sm text-gray-400 hover:text-[#ff3b30] transition-colors hover:scale-110 duration-200 cursor-pointer"
      >
        <Heart size={15} fill={isInWishlist ? "#ff3b30" : "none"} stroke={isInWishlist ? "#ff3b30" : "currentColor"} />
      </button>

      {/* Image Container */}
      <div className="relative aspect-[3/4] overflow-hidden bg-gray-50 block">
        <Link href={`/product/${product.id}`} className="w-full h-full block">
          <img
            src={isHovered ? hoverImage : mainImage}
            alt={product.item_name}
            className="w-full h-full object-cover transition-all duration-700 ease-out"
          />
        </Link>

        {/* Hover Split Buttons Bar */}
        <div className="absolute bottom-4 left-4 right-4 hidden md:flex items-center bg-white border border-[#eaeaea] shadow-sm transform translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300 pointer-events-none group-hover:pointer-events-auto z-10">
          <Link
            href={`/product/${product.id}`}
            className="flex-1 text-center py-3.5 text-[10px] font-sans font-bold uppercase tracking-[0.18em] text-[#111111] hover:bg-gray-50 transition-colors"
          >
            Choose Options
          </Link>
          <div className="w-[1px] h-10 bg-[#eaeaea]" />
          <Link
            href={`/product/${product.id}`}
            className="px-4.5 h-10 flex items-center justify-center text-gray-500 hover:text-black transition-colors"
          >
            <Eye size={18} />
          </Link>
        </div>
      </div>

      {/* Product Info */}
      <div className="pt-4 text-left space-y-1">
        <Link
          href={`/product/${product.id}`}
          className="text-[13px] font-bold tracking-widest text-[#111111] hover:opacity-75 transition-all line-clamp-1 block uppercase font-sans"
        >
          {product.item_name}
        </Link>

        {/* Pricing */}
        <div className="flex items-baseline gap-2">
          <span className="text-[13px] text-black font-semibold font-sans">
            Rs.{product.final_price.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
          </span>
          {discountPercent > 0 && (
            <span className="text-[11px] text-gray-400 line-through font-sans">
              Rs.{product.sale_price_per_piece?.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
            </span>
          )}
        </div>
      </div>
    </div>
  );
}
