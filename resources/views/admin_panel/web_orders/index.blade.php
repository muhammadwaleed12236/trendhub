@extends('admin_panel.layout.app')

@section('content')
<link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendors/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet">

<style>
    .page-container {
        max-width: 1350px;
        margin: 0 auto;
        padding: 15px;
    }
    .badge-pending { background-color: #f59e0b; color: white; }
    .badge-processing { background-color: #3b82f6; color: white; }
    .badge-shipped { background-color: #8b5cf6; color: white; }
    .badge-delivered { background-color: #10b981; color: white; }
    .badge-cancelled { background-color: #ef4444; color: white; }
</style>

<div class="page-container">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">Web Orders</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0 rounded">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="ordersTable">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Total Amount</th>
                            <th>Payment Status</th>
                            <th>Order Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>#{{ $order->order_number }}</td>
                            <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
                            <td>{{ $order->customer->name ?? 'Guest' }}</td>
                            <td>{{ $order->customer->phone ?? 'N/A' }}</td>
                            <td class="fw-bold">Rs. {{ number_format($order->total, 2) }}</td>
                            <td>
                                @if($order->payment_status == 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($order->payment_status == 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $order->order_status }} text-capitalize">{{ $order->order_status }}</span>
                            </td>
                            <td>
                                <a href="{{ route('web_orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('assets/vendors/datatables/js/jquery.dataTables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#ordersTable').DataTable({
            "paging": false,
            "info": false,
            "order": [[ 1, "desc" ]]
        });
    });
</script>
@endsection
