<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EcommerceOrder;

class WebOrderController extends Controller
{
    public function index()
    {
        $orders = EcommerceOrder::with('customer')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin_panel.web_orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = EcommerceOrder::with(['customer', 'items.product'])->findOrFail($id);
        return view('admin_panel.web_orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = EcommerceOrder::with('items.product')->findOrFail($id);
        $oldStatus = $order->order_status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return redirect()->back()->with('info', 'No status change detected.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // Confirm/Process Order -> Deduct Stock
            if (in_array($newStatus, ['processing', 'shipped', 'delivered']) && !$order->is_stock_deducted) {
                foreach ($order->items as $item) {
                    if (!$item->product_id) continue;
                    
                    // Default warehouse ID = 1
                    $warehouseStock = \App\Models\WarehouseStock::where('product_id', $item->product_id)
                        ->where('warehouse_id', 1)
                        ->first();

                    if (!$warehouseStock) {
                        // Create record if not exists but with 0 stock
                        $warehouseStock = \App\Models\WarehouseStock::create([
                            'product_id' => $item->product_id,
                            'warehouse_id' => 1,
                            'quantity' => 0,
                            'total_pieces' => 0
                        ]);
                    }

                    if ($warehouseStock->total_pieces < $item->quantity) {
                        throw new \Exception("Insufficient stock for product '{$item->product_name}'. Available: {$warehouseStock->total_pieces}, Required: {$item->quantity}");
                    }

                    // Deduct stock
                    $warehouseStock->total_pieces -= $item->quantity;
                    
                    $ppb = $item->product->pieces_per_box > 0 ? $item->product->pieces_per_box : 1;
                    $warehouseStock->quantity = round($warehouseStock->total_pieces / $ppb, 2);
                    $warehouseStock->save();

                    // Insert movement
                    \Illuminate\Support\Facades\DB::table('stock_movements')->insert([
                        'product_id' => $item->product_id,
                        'type' => 'out',
                        'qty' => -$item->quantity,
                        'ref_type' => 'web_order',
                        'ref_id' => $order->id,
                        'note' => 'Web Order Confirmed #' . $order->order_number,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                
                $order->is_stock_deducted = true;
            }
            
            // Cancel/Revert Order -> Restore Stock
            if (in_array($newStatus, ['cancelled', 'pending']) && $order->is_stock_deducted) {
                foreach ($order->items as $item) {
                    if (!$item->product_id) continue;

                    $warehouseStock = \App\Models\WarehouseStock::where('product_id', $item->product_id)
                        ->where('warehouse_id', 1)
                        ->first();

                    if (!$warehouseStock) {
                        $warehouseStock = \App\Models\WarehouseStock::create([
                            'product_id' => $item->product_id,
                            'warehouse_id' => 1,
                            'quantity' => 0,
                            'total_pieces' => 0
                        ]);
                    }

                    // Restore stock
                    $warehouseStock->total_pieces += $item->quantity;

                    $ppb = $item->product->pieces_per_box > 0 ? $item->product->pieces_per_box : 1;
                    $warehouseStock->quantity = round($warehouseStock->total_pieces / $ppb, 2);
                    $warehouseStock->save();

                    // Insert movement
                    \Illuminate\Support\Facades\DB::table('stock_movements')->insert([
                        'product_id' => $item->product_id,
                        'type' => 'in',
                        'qty' => $item->quantity,
                        'ref_type' => 'web_order_cancel',
                        'ref_id' => $order->id,
                        'note' => 'Web Order Reverted/Cancelled #' . $order->order_number,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $order->is_stock_deducted = false;
            }

            $order->order_status = $newStatus;
            $order->save();

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->back()->with('success', 'Order status updated and stock adjusted successfully.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }

    public function verifyPayment(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        $order = EcommerceOrder::with('items.product')->findOrFail($id);

        if ($request->action === 'approve') {
            if ($order->payment_status === 'paid') {
                return redirect()->back()->with('info', 'Payment is already marked as Paid.');
            }

            \Illuminate\Support\Facades\DB::beginTransaction();
            try {
                if (!$order->is_stock_deducted) {
                    foreach ($order->items as $item) {
                        if (!$item->product_id) continue;
                        
                        $warehouseStock = \App\Models\WarehouseStock::where('product_id', $item->product_id)
                            ->where('warehouse_id', 1)
                            ->first();

                        if (!$warehouseStock) {
                            $warehouseStock = \App\Models\WarehouseStock::create([
                                'product_id' => $item->product_id,
                                'warehouse_id' => 1,
                                'quantity' => 0,
                                'total_pieces' => 0
                            ]);
                        }

                        if ($warehouseStock->total_pieces < $item->quantity) {
                            throw new \Exception("Insufficient stock for product '{$item->product_name}'. Available: {$warehouseStock->total_pieces}, Required: {$item->quantity}");
                        }

                        $warehouseStock->total_pieces -= $item->quantity;
                        $ppb = $item->product->pieces_per_box > 0 ? $item->product->pieces_per_box : 1;
                        $warehouseStock->quantity = round($warehouseStock->total_pieces / $ppb, 2);
                        $warehouseStock->save();

                        \Illuminate\Support\Facades\DB::table('stock_movements')->insert([
                            'product_id' => $item->product_id,
                            'type' => 'out',
                            'qty' => -$item->quantity,
                            'ref_type' => 'web_order',
                            'ref_id' => $order->id,
                            'note' => 'Web Order Confirmed #' . $order->order_number,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                    $order->is_stock_deducted = true;
                }

                $order->payment_status = 'paid';
                $order->order_status = 'processing';
                $order->save();

                \Illuminate\Support\Facades\DB::commit();
                return redirect()->back()->with('success', 'Payment approved successfully! Order is now in Processing status.');

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                return redirect()->back()->with('error', 'Error approving payment: ' . $e->getMessage());
            }
        } else {
            $order->payment_status = 'failed';
            $order->save();
            return redirect()->back()->with('success', 'Payment rejected.');
        }
    }
}
