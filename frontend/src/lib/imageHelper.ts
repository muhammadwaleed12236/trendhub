const FALLBACK_IMAGES = [
  "https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=600",
  "https://images.unsplash.com/photo-1485968579580-b6d095142e6e?q=80&w=600",
  "https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=600",
  "https://images.unsplash.com/photo-1509631179647-0177331693ae?q=80&w=600",
  "https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?q=80&w=600",
  "https://images.unsplash.com/photo-1496345875659-11f7dd282d1d?q=80&w=600",
  "https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?q=80&w=600",
  "https://images.unsplash.com/photo-1507679799987-c73779587ccf?q=80&w=600"
];

export const getProductFallbackImage = (productId: number | string | undefined): string => {
  const idNum = typeof productId === 'string' 
    ? parseInt(productId, 10) || 0 
    : typeof productId === 'number'
    ? productId
    : 0;
    
  const index = Math.abs(idNum) % FALLBACK_IMAGES.length;
  return FALLBACK_IMAGES[index];
};

export const getAssetUrl = (path: string | undefined | null): string => {
  if (!path) return "";
  let clean = path;
  if (clean.includes("127.0.0.1:8000")) {
    clean = clean.replace(/https?:\/\/127\.0\.0\.1:8000\/?/, "");
  }
  if (clean.includes("localhost:8000")) {
    clean = clean.replace(/https?:\/\/localhost:8000\/?/, "");
  }
  if (clean.startsWith("http://") || clean.startsWith("https://")) {
    return clean;
  }
  const base = (process.env.NEXT_PUBLIC_ASSET_URL || "").replace(/\/$/, "");
  const cleanPath = clean.startsWith("/") ? clean : `/${clean}`;
  return base ? `${base}${cleanPath}` : cleanPath;
};

