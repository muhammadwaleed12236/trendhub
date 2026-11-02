export const getAssetUrl = (path: string | undefined | null): string => {
  if (!path || !path.trim()) return "";
  let clean = path.trim();
  if (clean.includes("127.0.0.1:8000")) {
    clean = clean.replace(/https?:\/\/127\.0\.0\.1:8000\/?/, "");
  }
  if (clean.includes("localhost:8000")) {
    clean = clean.replace(/https?:\/\/localhost:8000\/?/, "");
  }
  if (clean.startsWith("http://") || clean.startsWith("https://")) {
    return clean;
  }
  if (clean.includes("http://") || clean.includes("https://")) {
    const httpIdx = clean.indexOf("http");
    return clean.substring(httpIdx);
  }
  const defaultBase = typeof window !== "undefined" ? "" : "http://127.0.0.1:8000";
  const base = (process.env.NEXT_PUBLIC_ASSET_URL || defaultBase).replace(/\/$/, "");
  const cleanPath = clean.startsWith("/") ? clean : `/${clean}`;
  return base ? `${base}${cleanPath}` : cleanPath;
};

export const resolveProductImageUrl = (imagePath: string | null | undefined): string | null => {
  if (!imagePath || !imagePath.trim()) return null;
  
  let path = imagePath.trim();
  
  if (path.startsWith("http://") || path.startsWith("https://")) {
    return path;
  }
  
  if (path.includes("127.0.0.1:8000")) {
    path = path.replace(/https?:\/\/127\.0\.0\.1:8000\/?/, "");
  }
  if (path.includes("localhost:8000")) {
    path = path.replace(/https?:\/\/localhost:8000\/?/, "");
  }

  if (path.startsWith("uploads/") || path.startsWith("/uploads/")) {
    return getAssetUrl(path);
  }

  return getAssetUrl(`uploads/products/${path}`);
};

export const FALLBACK_PRESET_IMAGES = [
  "/uploads/products/product_1_main.jpg",
  "/uploads/products/product_2_main.jpg",
  "/uploads/products/product_3_main.jpg",
  "/uploads/products/product_4_main.jpg",
  "/uploads/products/product_5_main.jpg",
  "/uploads/products/product_6_main.jpg",
  "/uploads/products/product_7_main.jpg",
  "/uploads/products/product_8_main.jpg",
  "/uploads/products/outfitter_prod_1.jpg",
  "/uploads/products/outfitter_prod_2.jpg",
  "/uploads/products/outfitter_prod_3.jpg",
  "/uploads/products/outfitter_prod_4.jpg",
  "/uploads/products/outfitter_prod_5.jpg",
  "/uploads/products/outfitter_prod_6.jpg",
  "/uploads/products/outfitter_prod_7.jpg",
  "/uploads/products/outfitter_prod_8.jpg",
];

export const getProductFallbackImage = (_productId?: number | string): string => {
  if (_productId === undefined || _productId === null) {
    return getAssetUrl(FALLBACK_PRESET_IMAGES[0]);
  }
  const num = typeof _productId === "number" ? _productId : parseInt(String(_productId).replace(/\D/g, "")) || 0;
  const index = Math.abs(num) % FALLBACK_PRESET_IMAGES.length;
  return getAssetUrl(FALLBACK_PRESET_IMAGES[index]);
};
