<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CheckoutApiController extends Controller
{
    public function placeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'shipping_city' => 'required|string|max:100',
            'payment_method' => 'required|string|in:COD,Bank Transfer',
            'order_notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.size' => 'nullable|string',
            'items.*.color' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $subtotal = 0;
            $itemsToCreate = [];

            // Calculate totals and fetch products safely
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                
                // Final price checking (web price vs POS price)
                $price = $product->web_sale_price ?: $product->sale_price_per_piece;
                $totalItemPrice = $price * $itemData['quantity'];
                $subtotal += $totalItemPrice;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->item_name,
                    'price' => $price,
                    'quantity' => $itemData['quantity'],
                    'size' => $itemData['size'] ?? null,
                    'color' => $itemData['color'] ?? null,
                    'total' => $totalItemPrice
                ];
            }

            // Simple delivery calculations (e.g. Free above 10,000, else Rs. 250)
            $deliveryCharges = $subtotal >= 10000 ? 0 : 250;
            $discount = 0; // Future coupon support
            $total = $subtotal + $deliveryCharges - $discount;

            // Generate unique order number
            $orderNumber = 'TH-' . strtoupper(Str::random(4)) . '-' . time();

            // Create Order
            $order = new EcommerceOrder();
            $order->order_number = $orderNumber;
            // Link web customer if logged in (future implementation)
            $order->web_customer_id = auth('sanctum')->check() ? auth('sanctum')->id() : null;
            $order->subtotal = $subtotal;
            $order->discount = $discount;
            $order->delivery_charges = $deliveryCharges;
            $order->total = $total;
            $order->payment_method = $request->payment_method;
            $order->payment_status = 'pending';
            $order->order_status = 'pending';
            $order->order_notes = $request->order_notes;
            $order->shipping_name = $request->shipping_name;
            $order->shipping_phone = $request->shipping_phone;
            $order->shipping_address = $request->shipping_address;
            $order->shipping_city = $request->shipping_city;
            $order->save();

            // Create Order Items
            foreach ($itemsToCreate as $itemToCreate) {
                $orderItem = new EcommerceOrderItem($itemToCreate);
                $orderItem->ecommerce_order_id = $order->id;
                $orderItem->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order placed successfully',
                'order' => $order
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Checkout error: " . $e->getMessage() . " \nTrace: " . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Could not place order: ' . $e->getMessage()
            ], 500);
        }
    }
}
