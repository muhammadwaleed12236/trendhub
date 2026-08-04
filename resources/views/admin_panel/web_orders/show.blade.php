@extends('admin_panel.layout.app')

@section('content')
<link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
<style>
    .page-container {
        max-width: 1350px;
        margin: 0 auto;
        padding: 24px;
        background-color: #f8fafc;
        border-radius: 16px;
    }
    .page-header h4 {
        color: #0f172a;
        font-weight: 700;
        letter-spacing: -0.02em;
    }
    .card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 20px 24px;
        font-weight: 600;
    }
    .card-body {
        padding: 24px;
    }
    .table th {
        background-color: #f8fafc !important;
        color: #475569;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 14px 16px;
    }
    .table td {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #334155;
    }
    /* Soft Badges for Order Statuses */
    .badge-status-pending {
        background-color: #fef3c7;
        color: #92400e;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge-status-processing {
        background-color: #dbeafe;
        color: #1e40af;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge-status-shipped {
        background-color: #f3e8ff;
        color: #6b21a8;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge-status-delivered {
        background-color: #d1fae5;
        color: #065f46;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge-status-cancelled {
        background-color: #fee2e2;
        color: #991b1b;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    /* Payment Badge */
    .badge-pay-success {
        background-color: #d1fae5;
        color: #065f46;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.785rem;
    }
    .badge-pay-danger {
        background-color: #fee2e2;
        color: #991b1b;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.785rem;
    }
    .badge-pay-warning {
        background-color: #fef3c7;
        color: #92400e;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.785rem;
    }
    .btn-light {
        background-color: #ffffff;
        border-color: #cbd5e1;
        color: #475569;
        border-radius: 10px;
        transition: all 0.2s;
    }
    .btn-light:hover {
        background-color: #f8fafc;
        border-color: #94a3b8;
        color: #0f172a;
    }
    .form-select {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 0.95rem;
        color: #1e293b;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
        outline: 0;
    }
    .btn-primary {
        background-color: #4f46e5;
        border-color: #4f46e5;
        padding: 10px 24px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.2s;
    }
    .btn-primary:hover {
        background-color: #4338ca;
        border-color: #4338ca;
    }
    .alert-success {
        background-color: #d1fae5;
        border-color: #a7f3d0;
        color: #065f46;
        border-radius: 12px;
        padding: 16px 20px;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(6, 95, 70, 0.05);
    }
    .detail-item {
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 12px;
        margin-bottom: 12px;
    }
    .detail-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 0;
    }
</style>

<div class="page-container mt-3">
    <div class="d-flex align-items-center justify-content-between mb-4 page-header flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('web_orders.index') }}" class="btn btn-light border rounded-circle p-2 shadow-sm d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="fw-bold mb-0">Order #{{ $order->order_number }}</h4>
            @php
                $statusClass = 'badge-status-pending';
                $statusIcon = 'fa-clock';
                if ($order->order_status == 'processing') {
                    $statusClass = 'badge-status-processing';
                    $statusIcon = 'fa-cogs';
                } elseif ($order->order_status == 'shipped') {
                    $statusClass = 'badge-status-shipped';
                    $statusIcon = 'fa-truck';
                } elseif ($order->order_status == 'delivered') {
                    $statusClass = 'badge-status-delivered';
                    $statusIcon = 'fa-box-open';
                } elseif ($order->order_status == 'cancelled') {
                    $statusClass = 'badge-status-cancelled';
                    $statusIcon = 'fa-ban';
                }
            @endphp
            <span class="{{ $statusClass }} text-capitalize">
                <i class="fas {{ $statusIcon }}"></i> {{ $order->order_status }}
            </span>
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
            <button type="submit" class="btn btn-primary shadow-sm"><i class="fas fa-save me-1"></i> Update Status</button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-4 border" role="alert">
            <i class="fas fa-check-circle me-3 fs-4"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="row g-4">
        {{-- Order Items --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0 fw-bold text-slate-800"><i class="fas fa-box-open me-2 text-indigo"></i> Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th class="text-end pe-4">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            @if($item->product && $item->product->image)
                                                <img src="{{ asset('uploads/products/'.$item->product->image) }}" class="rounded border shadow-sm" style="width: 54px; height: 54px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted shadow-sm" style="width: 54px; height: 54px;">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-1 fw-bold text-slate-800">{{ $item->product_name }}</h6>
                                                <div class="text-muted small">
                                                    @if($item->variant_name) <span class="badge bg-light text-dark me-1">Variant: {{ $item->variant_name }}</span> @endif
                                                    @if($item->size) <span class="badge bg-light text-dark me-1">Size: {{ $item->size }}</span> @endif
                                                    @if($item->color) <span class="badge bg-light text-dark">Color: {{ $item->color }}</span> @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-semibold text-slate-700">Rs. {{ number_format($item->price, 2) }}</td>
                                    <td class="fw-bold text-slate-800">{{ $item->quantity }}</td>
                                    <td class="text-end fw-bold text-indigo pe-4">Rs. {{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top p-4 text-end">
                    <div class="d-flex justify-content-end mb-2">
                        <span class="text-muted me-4">Subtotal:</span>
                        <span class="fw-semibold text-slate-800" style="width: 140px;">Rs. {{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-end mb-2">
                        <span class="text-muted me-4">Discount:</span>
                        <span class="fw-bold text-danger" style="width: 140px;">- Rs. {{ number_format($order->discount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-end mb-2">
                        <span class="text-muted me-4">Shipping Fee:</span>
                        <span class="fw-semibold text-slate-800" style="width: 140px;">Rs. {{ number_format($order->delivery_charges, 2) }}</span>
                    </div>
                    <hr class="my-3 ms-auto" style="width: 250px; border-color: #cbd5e1;">
                    <div class="d-flex justify-content-end align-items-center">
                        <span class="fw-bold me-4 text-slate-800 fs-5">Grand Total:</span>
                        <span class="fw-bold text-success fs-4" style="width: 140px;">Rs. {{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Customer & Shipping Details --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0 fw-bold text-slate-800"><i class="fas fa-user me-2 text-info"></i> Customer Details</h5>
                </div>
                <div class="card-body">
                    <div class="detail-item d-flex align-items-start gap-3">
                        <i class="fas fa-user-circle fs-5 text-slate-400 mt-1"></i>
                        <div>
                            <span class="text-muted d-block small mb-1">Name</span>
                            <strong class="text-slate-800 fs-6">{{ $order->customer->name ?? $order->shipping_name }}</strong>
                        </div>
                    </div>
                    <div class="detail-item d-flex align-items-start gap-3">
                        <i class="fas fa-envelope fs-5 text-slate-400 mt-1"></i>
                        <div>
                            <span class="text-muted d-block small mb-1">Email Address</span>
                            <a href="mailto:{{ $order->customer->email ?? $order->shipping_email }}" class="text-decoration-none fw-semibold text-indigo" style="color:#4f46e5;">{{ $order->customer->email ?? $order->shipping_email }}</a>
                        </div>
                    </div>
                    <div class="detail-item d-flex align-items-start gap-3">
                        <i class="fas fa-phone fs-5 text-slate-400 mt-1"></i>
                        <div>
                            <span class="text-muted d-block small mb-1">Phone Number</span>
                            <a href="tel:{{ $order->customer->phone ?? $order->shipping_phone }}" class="text-decoration-none fw-semibold text-indigo" style="color:#4f46e5;">{{ $order->customer->phone ?? $order->shipping_phone }}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0 fw-bold text-slate-800"><i class="fas fa-map-marker-alt me-2 text-danger"></i> Shipping Address</h5>
                </div>
                <div class="card-body">
                    <div class="detail-item d-flex align-items-start gap-3">
                        <i class="fas fa-user fs-5 text-slate-400 mt-1"></i>
                        <div>
                            <span class="text-muted d-block small mb-1">Recipient</span>
                            <strong class="text-slate-800">{{ $order->shipping_name }}</strong>
                        </div>
                    </div>
                    <div class="detail-item d-flex align-items-start gap-3">
                        <i class="fas fa-map fs-5 text-slate-400 mt-1"></i>
                        <div>
                            <span class="text-muted d-block small mb-1">Address</span>
                            <span class="text-slate-700 d-block">{{ $order->shipping_address }}</span>
                            <strong class="text-slate-800 mt-1 d-block">{{ $order->shipping_city }}, Pakistan</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0 fw-bold text-slate-800"><i class="fas fa-credit-card me-2 text-success"></i> Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Payment Method:</span>
                        <span class="fw-bold text-uppercase text-slate-800">{{ $order->payment_method }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Payment Status:</span>
                        @if($order->payment_status == 'paid')
                            <span class="badge-pay-success"><i class="fas fa-check-circle me-1"></i> Paid</span>
                        @elseif($order->payment_status == 'failed')
                            <span class="badge-pay-danger"><i class="fas fa-times-circle me-1"></i> Failed</span>
                        @else
                            <span class="badge-pay-warning"><i class="fas fa-clock me-1"></i> Pending</span>
                        @endif
                    </div>
                </div>
            </div>
            
            @if($order->order_notes)
            <div class="card">
                <div class="card-header py-3">
                    <h5 class="mb-0 fw-bold text-slate-800"><i class="fas fa-sticky-note me-2 text-warning"></i> Order Notes</h5>
                </div>
                <div class="card-body bg-light-soft" style="background-color: #f8fafc;">
                    <p class="mb-0 fst-italic text-slate-600">{{ $order->order_notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
