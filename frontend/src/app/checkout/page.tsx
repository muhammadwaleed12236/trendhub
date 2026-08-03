"use client";

import { useState } from "react";
import { useCartStore } from "@/store/cartStore";
import api from "@/lib/api";
import Link from "next/link";
import { CheckCircle, ShoppingBag, Loader2 } from "lucide-react";

export default function Checkout() {
  const { items, getTotalPrice, getDiscountAmount, getFinalTotal, appliedCoupon, applyCoupon, removeCoupon, clearCart } = useCartStore();

  // Form states
  const [shippingName, setShippingName] = useState("");
  const [shippingPhone, setShippingPhone] = useState("");
  const [shippingAddress, setShippingAddress] = useState("");
  const [shippingCity, setShippingCity] = useState("");
  const [paymentMethod, setPaymentMethod] = useState("COD");
  const [orderNotes, setOrderNotes] = useState("");

  const [loading, setLoading] = useState(false);
  const [orderSuccess, setOrderSuccess] = useState<any>(null);

  // Coupon states
  const [promoCode, setPromoCode] = useState("");
  const [promoError, setPromoError] = useState("");
  const [validatingPromo, setValidatingPromo] = useState(false);

  const subtotal = getTotalPrice();
  const discountAmount = getDiscountAmount();
  const subtotalAfterDiscount = getFinalTotal();
  const deliveryCharges = subtotalAfterDiscount >= 10000 ? 0 : 250;
  const total = subtotalAfterDiscount + deliveryCharges;

  const handleApplyPromo = async () => {
    if (!promoCode.trim()) return;
    setValidatingPromo(true);
    setPromoError("");
    try {
      const res = await api.post("/checkout/validate-coupon", {
        coupon_code: promoCode.trim(),
        cart_total: subtotal
      });
      
      if (res.data.status === "success") {
        applyCoupon({
          code: promoCode.trim(),
          type: res.data.type,
          value: res.data.value
        });
        setPromoCode("");
      } else {
        setPromoError(res.data.message || "Invalid coupon code");
      }
    } catch (err: any) {
      setPromoError(err.response?.data?.message || "Error validating coupon");
    } finally {
      setValidatingPromo(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (items.length === 0) return;

    setLoading(true);

    const payload = {
      shipping_name: shippingName,
      shipping_phone: shippingPhone,
      shipping_address: shippingAddress,
      shipping_city: shippingCity,
      payment_method: paymentMethod,
      order_notes: orderNotes,
      coupon_code: appliedCoupon?.code || null,
      items: items.map((item) => ({
        product_id: item.product.id,
        quantity: item.quantity,
        size: item.selectedSize || null,
        color: item.selectedColor || null,
      })),
    };

    try {
      const res = await api.post("/checkout", payload);
      if (res.data.status === "success") {
        setOrderSuccess(res.data.order);
        clearCart();
        removeCoupon();
      }
    } catch (err) {
      alert("Could not place order. Please try again.");
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  if (orderSuccess) {
    return (
      <div className="max-w-[600px] mx-auto px-6 py-32 text-center space-y-6 select-none font-sans">
        <div className="flex justify-center text-black">
          <CheckCircle size={64} className="stroke-[1.5]" />
        </div>
        <div className="space-y-2">
          <h1 className="font-serif text-3xl tracking-widest uppercase font-light">Order Placed Successfully!</h1>
          <p className="text-sm text-gray-500 font-serif italic">
            Thank you for shopping with TrendHub. Your order has been registered.
          </p>
        </div>
        
        <div className="bg-gray-50 p-6 border border-gray-100 space-y-3 text-left max-w-sm mx-auto text-sm">
          <div className="flex justify-between">
            <span className="text-gray-400">Order Number:</span>
            <span className="font-mono font-bold text-black">{orderSuccess.order_number}</span>
          </div>
          <div className="flex justify-between">
            <span className="text-gray-400">Total Amount:</span>
            <span className="font-bold text-black">Rs. {orderSuccess.total.toLocaleString()}</span>
          </div>
          <div className="flex justify-between">
            <span className="text-gray-400">Payment Mode:</span>
            <span className="font-bold text-black">{orderSuccess.payment_method}</span>
          </div>
        </div>

        <div className="pt-4">
          <Link
            href="/shop"
            className="inline-block bg-black text-white px-8 py-3.5 text-xs font-sans uppercase tracking-[0.2em] font-semibold hover:bg-neutral-800 transition-colors"
          >
            Continue Shopping
          </Link>
        </div>
      </div>
    );
  }

  if (items.length === 0) {
    return (
      <div className="max-w-[1400px] mx-auto px-6 py-28 text-center space-y-6 select-none font-sans">
        <ShoppingBag size={48} className="mx-auto text-gray-300 stroke-[1]" />
        <h1 className="font-serif text-2xl uppercase tracking-widest font-light">Your Cart is Empty</h1>
        <p className="text-sm text-gray-400 max-w-[400px] mx-auto font-light">
          You need to add products to your cart before proceeding to checkout.
        </p>
        <div className="pt-4">
          <Link
            href="/shop"
            className="inline-block bg-black text-white px-8 py-3.5 text-xs font-sans uppercase tracking-[0.2em] font-semibold hover:bg-neutral-800 transition-colors"
          >
            Go To Shop
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-[1400px] mx-auto px-6 py-28 select-none font-sans">
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-16">
        
        {/* Left Form: Checkout Details */}
        <form onSubmit={handleSubmit} className="lg:col-span-7 space-y-8">
          <div className="space-y-2 border-b border-gray-100 pb-4">
            <h1 className="font-serif text-2xl tracking-widest uppercase font-light">CHECKOUT DETAILS</h1>
            <p className="text-xs text-gray-400 uppercase tracking-widest">Complete your order information</p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div className="space-y-2">
              <label className="text-xs font-bold uppercase tracking-wider text-black">Full Name</label>
              <input
                type="text"
                required
                value={shippingName}
                onChange={(e) => setShippingName(e.target.value)}
                placeholder="e.g. John Doe"
                className="w-full border border-gray-200 px-4 py-3 text-xs focus:outline-none focus:border-black transition-colors"
              />
            </div>

            <div className="space-y-2">
              <label className="text-xs font-bold uppercase tracking-wider text-black">Phone Number</label>
              <input
                type="tel"
                required
                value={shippingPhone}
                onChange={(e) => setShippingPhone(e.target.value)}
                placeholder="e.g. 03XXXXXXXXX"
                className="w-full border border-gray-200 px-4 py-3 text-xs focus:outline-none focus:border-black transition-colors"
              />
            </div>

            <div className="sm:col-span-2 space-y-2">
              <label className="text-xs font-bold uppercase tracking-wider text-black">Shipping Address</label>
              <input
                type="text"
                required
                value={shippingAddress}
                onChange={(e) => setShippingAddress(e.target.value)}
                placeholder="Street address, apartment, suite, unit etc."
                className="w-full border border-gray-200 px-4 py-3 text-xs focus:outline-none focus:border-black transition-colors"
              />
            </div>

            <div className="space-y-2">
              <label className="text-xs font-bold uppercase tracking-wider text-black">City</label>
              <input
                type="text"
                required
                value={shippingCity}
                onChange={(e) => setShippingCity(e.target.value)}
                placeholder="e.g. Lahore"
                className="w-full border border-gray-200 px-4 py-3 text-xs focus:outline-none focus:border-black transition-colors"
              />
            </div>

            <div className="space-y-2">
              <label className="text-xs font-bold uppercase tracking-wider text-black">Country</label>
              <input
                type="text"
                disabled
                value="Pakistan"
                className="w-full border border-gray-100 bg-gray-50 px-4 py-3 text-xs text-gray-400 focus:outline-none cursor-not-allowed"
              />
            </div>

            <div className="sm:col-span-2 space-y-3">
              <span className="text-xs font-bold uppercase tracking-wider text-black block">Payment Mode</span>
              <div className="flex gap-4">
                <button
                  type="button"
                  onClick={() => setPaymentMethod("COD")}
                  className={`flex-1 border py-3 text-xs font-semibold uppercase tracking-wider transition-colors cursor-pointer ${
                    paymentMethod === "COD"
                      ? "border-black bg-black text-white font-bold"
                      : "border-gray-200 text-gray-500 hover:border-black"
                  }`}
                >
                  Cash on Delivery
                </button>
                <button
                  type="button"
                  onClick={() => setPaymentMethod("Bank Transfer")}
                  className={`flex-1 border py-3 text-xs font-semibold uppercase tracking-wider transition-colors cursor-pointer ${
                    paymentMethod === "Bank Transfer"
                      ? "border-black bg-black text-white font-bold"
                      : "border-gray-200 text-gray-500 hover:border-black"
                  }`}
                >
                  Bank Transfer
                </button>
              </div>
            </div>

            <div className="sm:col-span-2 space-y-2">
              <label className="text-xs font-bold uppercase tracking-wider text-black">Order Notes (Optional)</label>
              <textarea
                value={orderNotes}
                onChange={(e) => setOrderNotes(e.target.value)}
                placeholder="Special notes about your delivery address or package details..."
                rows={4}
                className="w-full border border-gray-200 px-4 py-3 text-xs focus:outline-none focus:border-black transition-colors resize-none"
              />
            </div>
          </div>

          <div className="pt-4">
            <button
              type="submit"
              disabled={loading}
              className="w-full bg-black text-white py-4 text-xs font-sans uppercase tracking-[0.2em] font-semibold hover:bg-neutral-800 transition-colors flex items-center justify-center gap-2 cursor-pointer"
            >
              {loading ? (
                <>
                  <Loader2 className="animate-spin" size={16} /> PLACING ORDER...
                </>
              ) : (
                "PLACE ORDER NOW"
              )}
            </button>
          </div>
        </form>

        {/* Right Summary Panel */}
        <div className="lg:col-span-5 bg-gray-50 p-8 border border-gray-100 rounded-lg h-fit space-y-6">
          <h4 className="font-serif text-lg tracking-wider uppercase font-semibold text-black">ORDER SUMMARY</h4>
          
          {/* Items */}
          <div className="space-y-4 max-h-64 overflow-y-auto pr-2">
            {items.map((item, idx) => (
              <div key={idx} className="flex justify-between items-center text-xs">
                <div>
                  <span className="font-medium text-black">{item.product.item_name}</span>
                  <span className="text-gray-400 font-light block">
                    Qty: {item.quantity} {item.selectedSize ? `| Size: ${item.selectedSize}` : ""}
                  </span>
                </div>
                <span className="font-semibold text-black">
                  Rs. {(item.product.final_price * item.quantity).toLocaleString()}
                </span>
              </div>
            ))}
          </div>

          {/* Calculations */}
          <div className="border-t border-gray-200 pt-4 space-y-2 text-xs">
            <div className="flex justify-between">
              <span className="text-gray-500">Subtotal:</span>
              <span className="font-semibold text-black">Rs. {subtotal.toLocaleString()}</span>
            </div>
            
            {/* Promo Code Input */}
            <div className="pt-2 pb-2">
              {appliedCoupon ? (
                <div className="bg-green-50 border border-green-100 p-3 flex justify-between items-center text-green-700">
                  <div className="flex flex-col">
                    <span className="font-bold tracking-wider uppercase text-[10px]">Applied Coupon</span>
                    <span className="font-mono">{appliedCoupon.code}</span>
                  </div>
                  <button 
                    type="button"
                    onClick={removeCoupon}
                    className="text-xs underline hover:text-green-900 cursor-pointer"
                  >
                    Remove
                  </button>
                </div>
              ) : (
                <div className="flex gap-2">
                  <input
                    type="text"
                    value={promoCode}
                    onChange={(e) => setPromoCode(e.target.value)}
                    placeholder="Promo code"
                    className="flex-1 border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:border-black transition-colors"
                  />
                  <button
                    type="button"
                    onClick={handleApplyPromo}
                    disabled={validatingPromo}
                    className="bg-black text-white px-4 py-2 text-[10px] font-bold uppercase tracking-wider hover:bg-neutral-800 transition-colors cursor-pointer disabled:opacity-50"
                  >
                    {validatingPromo ? "..." : "Apply"}
                  </button>
                </div>
              )}
              {promoError && <p className="text-red-500 text-[10px] mt-1">{promoError}</p>}
            </div>

            {discountAmount > 0 && (
              <div className="flex justify-between text-[#ff3b30]">
                <span>Discount:</span>
                <span className="font-semibold">- Rs. {discountAmount.toLocaleString()}</span>
              </div>
            )}
            
            <div className="flex justify-between">
              <span className="text-gray-500">Delivery Charges:</span>
              <span className="font-semibold text-black">
                {deliveryCharges === 0 ? "Free" : `Rs. ${deliveryCharges}`}
              </span>
            </div>
          </div>

          <div className="border-t border-gray-200 pt-4 flex justify-between items-center">
            <span className="text-sm font-bold uppercase tracking-wider text-black">Total Amount:</span>
            <span className="text-lg font-bold text-black">Rs. {total.toLocaleString()}</span>
          </div>
        </div>
      </div>
    </div>
  );
}
