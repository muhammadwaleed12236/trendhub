@extends('admin_panel.layout.app')

@section('content')
<link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
<style>
    .page-container { max-width: 1350px; margin: 0 auto; padding: 15px; }
    .card { border-radius: 12px; border: 1px solid #e2e8f0; }
    .card-header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: bold; }
    .badge-pending { background-color: #f59e0b; color: white; }
    .badge-processing { background-color: #3b82f6; color: white; }
    .badge-shipped { background-color: #8b5cf6; color: white; }
    .badge-delivered { background-color: #10b981; color: white; }
    .badge-cancelled { background-color: #ef4444; color: white; }
</style>

<div class="page-container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('web_orders.index') }}" class="btn btn-light border rounded-circle p-2 shadow-sm">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="fw-bold mb-0">Order #{{ $order->order_number }}</h4>
            <span class="badge badge-{{ $order->order_status }} text-capitalize fs-6">{{ $order->order_status }}</span>
        </div>
        
        <form action="{{ route('web_orders.status', $order->id) }}" method="POST" class="d-flex gap-2">
            @csrf
            <select name="status" class="form-select w-auto">
                <option value="pending" {{ $order->order_status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ $order->order_status == 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="shipped" {{ $order->order_status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ $order->order_status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ $order->order_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-primary">Update Status</button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        {{-- Order Items --}}
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0"><i class="fas fa-box-open me-2 text-primary"></i> Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th class="text-end pe-3">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-3">
                                            @if($item->product && $item->product->image)
                                                <img src="{{ asset('uploads/products/'.$item->product->image) }}" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $item->product_name }}</h6>
                                                <small class="text-muted">
                                                    @if($item->variant_name) Variant: {{ $item->variant_name }} @endif
                                                    @if($item->size) | Size: {{ $item->size }} @endif
                                                    @if($item->color) | Color: {{ $item->color }} @endif
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rs. {{ number_format($item->price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="text-end fw-bold pe-3">Rs. {{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3 text-end">
                    <div class="d-flex justify-content-end mb-2">
                        <span class="text-muted me-3">Subtotal:</span>
                        <span class="fw-bold" style="width: 120px;">Rs. {{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-end mb-2">
                        <span class="text-muted me-3">Discount:</span>
                        <span class="fw-bold text-danger" style="width: 120px;">- Rs. {{ number_format($order->discount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-end mb-2">
                        <span class="text-muted me-3">Shipping Fee:</span>
                        <span class="fw-bold" style="width: 120px;">Rs. {{ number_format($order->delivery_charges, 2) }}</span>
                    </div>
                    <hr class="my-2 ms-auto" style="width: 250px;">
                    <div class="d-flex justify-content-end">
                        <span class="fw-bold me-3 fs-5">Total:</span>
                        <span class="fw-bold text-success fs-5" style="width: 120px;">Rs. {{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Customer & Shipping Details --}}
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0"><i class="fas fa-user me-2 text-info"></i> Customer Details</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><i class="fas fa-user-circle me-2 text-muted"></i> <strong>{{ $order->customer->name ?? $order->shipping_name }}</strong></p>
                    <p class="mb-1"><i class="fas fa-envelope me-2 text-muted"></i> <a href="mailto:{{ $order->customer->email ?? $order->shipping_email }}">{{ $order->customer->email ?? $order->shipping_email }}</a></p>
                    <p class="mb-0"><i class="fas fa-phone me-2 text-muted"></i> <a href="tel:{{ $order->customer->phone ?? $order->shipping_phone }}">{{ $order->customer->phone ?? $order->shipping_phone }}</a></p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2 text-danger"></i> Shipping Address</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $order->shipping_name }}</strong></p>
                    <p class="mb-1">{{ $order->shipping_address }}</p>
                    <p class="mb-1">{{ $order->shipping_city }}</p>
                    <p class="mb-0">Pakistan</p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2 text-success"></i> Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Method:</span>
                        <span class="fw-bold text-uppercase">{{ $order->payment_method }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status:</span>
                        @if($order->payment_status == 'paid')
                            <span class="badge bg-success">Paid</span>
                        @elseif($order->payment_status == 'failed')
                            <span class="badge bg-danger">Failed</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </div>
                </div>
            </div>
            
            @if($order->order_notes)
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2 text-warning"></i> Order Notes</h5>
                </div>
                <div class="card-body bg-light">
                    <p class="mb-0 fst-italic">{{ $order->order_notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
