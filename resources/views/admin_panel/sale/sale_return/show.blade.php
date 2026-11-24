@extends('admin_panel.layout.app')

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid py-4">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-file-invoice-dollar text-primary"></i> Sale Return Details
                        </h4>
                        <p class="text-muted mb-0 small">Return Invoice: <strong>{{ $return->return_invoice }}</strong></p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('sale.return.print', $return->id) }}" class="btn btn-dark d-inline-flex align-items-center gap-1" target="_blank" style="border-radius: 8px;">
                            <i class="fas fa-print"></i> Print Voucher
                        </a>
                        <a href="{{ route('sale.return.create') }}" class="btn btn-warning fw-bold d-inline-flex align-items-center gap-1" style="border-radius: 8px;">
                            <i class="fas fa-plus"></i> New Return
                        </a>
                        <a href="{{ route('sale.return.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1" style="border-radius: 8px;">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">

                        {{-- Return Header Info --}}
                        <div class="row mb-4 g-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border h-100">
                                    <h6 class="text-muted text-uppercase small mb-3 fw-bold">Return Information</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="fw-bold text-muted" style="width: 140px;">Return Invoice:</td>
                                            <td><span class="badge bg-primary fs-6">{{ $return->return_invoice }}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Return Date:</td>
                                            <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Customer:</td>
                                            <td><strong>{{ $return->customer->customer_name ?? $return->customer->name ?? 'Walking Customer' }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Warehouse:</td>
                                            <td>{{ $return->warehouse->warehouse_name ?? 'Default' }}</td>
                                        </tr>
                                        @if ($return->sale)
                                            <tr>
                                                <td class="fw-bold text-muted">Original Invoice:</td>
                                                <td>
                                                    <a href="{{ route('sales.invoice', $return->sale->id) }}" class="text-primary fw-bold" target="_blank">
                                                        {{ $return->sale->invoice_no }} <i class="fas fa-external-link-alt small"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td class="fw-bold text-muted">Return Type:</td>
                                                <td><span class="badge bg-info text-dark"><i class="fas fa-cart-arrow-down"></i> Direct Sales Return (Without Invoice)</span></td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td class="fw-bold text-muted">Refund Method:</td>
                                            <td>
                                                @if ($return->refund_type === 'cash')
                                                    <span class="badge bg-success">Cash Refund</span>
                                                @elseif ($return->refund_type === 'bank')
                                                    <span class="badge bg-info text-dark">Bank Refund</span>
                                                @else
                                                    <span class="badge bg-primary">Customer Ledger Credit</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border h-100">
                                    <h6 class="text-muted text-uppercase small mb-3 fw-bold">Financial Summary</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="fw-bold text-muted">Bill Amount:</td>
                                            <td class="text-end fw-semibold">Rs. {{ number_format($return->bill_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Item Discount:</td>
                                            <td class="text-end text-danger">- Rs. {{ number_format($return->item_discount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Extra Discount:</td>
                                            <td class="text-end text-danger">- Rs. {{ number_format($return->extra_discount ?? 0, 2) }}</td>
                                        </tr>
                                        <tr class="border-top">
                                            <td class="fw-bold fs-6">Net Return Amount:</td>
                                            <td class="text-end fw-bold text-primary fs-6">
                                                Rs. {{ number_format($return->net_amount, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Refund Paid:</td>
                                            <td class="text-end text-success fw-bold">Rs. {{ number_format($return->paid, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Customer Credit Balance:</td>
                                            <td class="text-end fw-bold {{ $return->balance > 0 ? 'text-danger' : 'text-success' }}">
                                                Rs. {{ number_format($return->balance, 2) }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Return Items --}}
                        <h6 class="text-muted text-uppercase small mb-3 fw-bold">Returned Items & Stock Adjustment</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" style="font-size: 13px;">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">#</th>
                                        <th>Product</th>
                                        <th class="text-center" style="width: 90px;">Total Pieces</th>
                                        <th class="text-center" style="width: 90px;">Boxes</th>
                                        <th class="text-end" style="width: 100px;">Price/Pc</th>
                                        <th class="text-end" style="width: 80px;">Discount</th>
                                        <th style="width: 130px;">Reason</th>
                                        <th style="width: 150px;">Stock Condition</th>
                                        <th class="text-end" style="width: 120px;">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($return->items as $index => $item)
                                        <tr>
                                            <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->product->item_name ?? $item->product->product_name ?? 'N/A' }}</strong>
                                                @php
                                                    $variant = [];
                                                    if (!empty($item->color)) {
                                                        $b64Decoded = base64_decode($item->color, true);
                                                        if ($b64Decoded !== false) {
                                                            $json = json_decode($b64Decoded, true);
                                                            if (is_array($json)) $variant = $json;
                                                        }
                                                        if (empty($variant)) {
                                                            $json = json_decode($item->color, true);
                                                            if (is_array($json)) $variant = $json;
                                                        }
                                                    }
                                                    $vName = $variant['name'] ?? '';
                                                    $vSize = $variant['size'] ?? ($variant['size_val'] ?? '-');
                                                    $vColor = $variant['color'] ?? ($variant['color_val'] ?? '-');
                                                @endphp
                                                @if (!empty($vName))
                                                    <span class="badge bg-light text-dark border ms-1">{{ $vName }} ({{ $vSize }} | {{ $vColor }})</span>
                                                @endif
                                                @if (!empty($item->product->product_code))
                                                    <br><small class="text-muted">Code: {{ $item->product->product_code }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold">{{ number_format($item->qty, 0) }}</td>
                                            <td class="text-center">{{ number_format($item->boxes, 2) }}</td>
                                            <td class="text-end">Rs. {{ number_format($item->price, 2) }}</td>
                                            <td class="text-end text-danger">- Rs. {{ number_format($item->item_discount, 2) }}</td>
                                            <td>{{ $item->return_reason ?? $return->return_reason ?? '-' }}</td>
                                            <td>
                                                @if ($item->stock_condition === 'damaged')
                                                    <span class="badge bg-danger"><i class="fas fa-tools me-1"></i> Damaged (Quarantined)</span>
                                                @else
                                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i> Good Stock (Restocked)</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-bold">Rs. {{ number_format($item->line_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($return->remarks)
                            <div class="mt-4 p-3 bg-light rounded border">
                                <h6 class="text-muted text-uppercase small mb-1 fw-bold">Remarks</h6>
                                <p class="mb-0 text-dark">{{ $return->remarks }}</p>
                            </div>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
