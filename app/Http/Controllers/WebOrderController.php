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

        $order = EcommerceOrder::findOrFail($id);
        $order->order_status = $request->status;
        $order->save();

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }
}
