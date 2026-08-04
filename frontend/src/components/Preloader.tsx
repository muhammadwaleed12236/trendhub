"use client";

import { useEffect, useState } from "react";
import { usePathname } from "next/navigation";

export default function Preloader() {
  const [showPreloader, setShowPreloader] = useState(true);
  const [fadeOut, setFadeOut] = useState(false);
  const [windowLoaded, setWindowLoaded] = useState(false);
  const [productsLoaded, setProductsLoaded] = useState(false);
  const pathname = usePathname();

  // 1. Detect standard window load
  useEffect(() => {
    if (document.readyState === "complete") {
      setWindowLoaded(true);
      return;
    }

    const handleLoad = () => {
      setWindowLoaded(true);
    };

    window.addEventListener("load", handleLoad);
    return () => window.removeEventListener("load", handleLoad);
  }, []);

  // 2. Detect homepage products loaded
  useEffect(() => {
    if (pathname !== "/") {
      setProductsLoaded(true);
      return;
    }

    if ((window as any).__productsLoaded) {
      setProductsLoaded(true);
      return;
    }

    const handleProductsLoaded = () => {
      setProductsLoaded(true);
    };

    window.addEventListener("products-loaded", handleProductsLoaded);
    return () => window.removeEventListener("products-loaded", handleProductsLoaded);
  }, [pathname]);

  // 3. Trigger fade out when both window and products (if on home page) are loaded
  useEffect(() => {
    if (windowLoaded && productsLoaded) {
      const triggerFadeOut = () => {
        setFadeOut(true);
        const timer = setTimeout(() => {
          setShowPreloader(false);
        }, 500); // match duration-500 transition
        return () => clearTimeout(timer);
      };

      const delayTimer = setTimeout(triggerFadeOut, 400); // brief delay to appreciate the logo
      return () => clearTimeout(delayTimer);
    }
  }, [windowLoaded, productsLoaded]);

  // Safety Fallback Timer: Hide preloader after 5 seconds no matter what
  useEffect(() => {
    const safetyTimer = setTimeout(() => {
      setFadeOut(true);
      const timer = setTimeout(() => {
        setShowPreloader(false);
      }, 500);
      return () => clearTimeout(timer);
    }, 5000);

    return () => clearTimeout(safetyTimer);
  }, []);

  if (!showPreloader) return null;

  return (
    <div
      className={`fixed inset-0 bg-white z-[9999] flex flex-col items-center justify-center select-none pointer-events-none transition-opacity duration-500 ease-in-out ${
        fadeOut ? "opacity-0" : "opacity-100"
      }`}
    >
      <div className="flex flex-col items-center space-y-4">
        {/* Pulsing Brand Logo */}
        <div className="animate-pulse flex items-center justify-center min-h-[64px]">
          <img
            src="/logo.png"
            alt="TrendHub Logo"
            className="h-16 sm:h-20 object-contain"
          />
        </div>
      </div>
    </div>
  );
}
