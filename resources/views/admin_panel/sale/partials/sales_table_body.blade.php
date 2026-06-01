@foreach ($sales as $sale)
    @php
        // Product Names
        $pNames = 'N/A';
        if ($sale->items && $sale->items->count() > 0) {
            $pNames = $sale->items
                ->map(fn($item) => optional($item->product)->item_name ?? '?')
                ->implode(', ');
        } elseif ($sale->product) {
            $pNames = $sale->product;
        }

        // Status Styling
        $statusBadge = '<span class="badge badge-warning text-dark border border-warning">Draft</span>';
        if ($sale->sale_status === 'posted') {
            if ($sale->is_booking) {
                $statusBadge = '<span class="badge badge-success border border-success"><i class="fas fa-check-circle me-1"></i>Confirmed Booking</span>';
            } else {
                $statusBadge = '<span class="badge badge-success border border-success">Posted</span>';
            }
        } elseif ($sale->sale_status === 'booked') {
            $statusBadge = '<span class="badge badge-warning text-dark border border-warning"><i class="fas fa-bookmark me-1"></i>Booked</span>';
        } elseif ($sale->sale_status === 'returned') {
            $statusBadge = '<span class="badge badge-danger border border-danger">Returned</span>';
        } elseif ($sale->sale_status == 1) {
            $statusBadge = '<span class="badge badge-danger border border-danger">Return</span>';
        } elseif ($sale->sale_status === null) {
            $statusBadge = '<span class="badge badge-success border border-success">Sale</span>';
        }

        // Check for returns
        if ($sale->returns && $sale->returns->count() > 0) {
            $statusBadge .= '<br><small class="badge badge-danger border border-danger mt-1"><i class="fas fa-undo-alt me-1"></i> Partial Return</small>';
        }
    @endphp
    <tr class="border-bottom-0">
        <td class="ps-3 fw-bold text-muted font-monospace">#{{ $sale->id }}</td>
        <td>
            <div class="d-flex align-items-center">
                <div class="avatar-circle bg-info-subtle text-info me-2 fw-bold d-flex align-items-center justify-content-center rounded-circle"
                    style="width: 32px; height: 32px; font-size: 14px; background-color: #e0f2fe; color: #0369a1;">
                    {{ strtoupper(substr(optional($sale->customer_relation)->customer_name ?? 'C', 0, 1)) }}
                </div>
                <span class="fw-medium text-dark">{{ optional($sale->customer_relation)->customer_name ?? 'N/A' }}</span>
            </div>
        </td>
        <td class="font-monospace text-dark">{{ $sale->reference ?? '-' }}</td>
        <td title="{{ $pNames }}" class="text-muted small">
            {{ \Illuminate\Support\Str::limit($pNames, 40) }}
        </td>
        <td class="text-center font-monospace">
            {{ $sale->total_items > 0 ? $sale->total_items : $sale->qty }}
        </td>
        <td class="text-end fw-bold text-dark font-monospace">
            {{ number_format($sale->total_bill_amount > 0 ? $sale->total_bill_amount : (float) $sale->per_total, 2) }}
        </td>
        <td class="text-end text-danger font-monospace">
            {{ number_format($sale->total_extradiscount, 2) }}
        </td>
        <td class="text-end text-success fw-bold font-monospace">
            {{ number_format($sale->total_net, 2) }}
        </td>
        <td class="text-nowrap small text-muted">
            {{ $sale->created_at->format('d M, Y') }}
        </td>
        <td>{!! $statusBadge !!}</td>
        <td class="pe-3 text-center">
            <div class="d-flex flex-wrap gap-1 align-items-center justify-content-center">
                @if ($sale->sale_status === 'draft' || $sale->sale_status === 'booked')
                    {{-- Draft / Booked Actions --}}
                    <form action="{{ route('sales.confirm', $sale->id) }}" method="POST" class="d-inline confirm-booking-form">
                        @csrf
                        <button type="button" class="btn btn-xs btn-success confirm-booking-btn">
                            <i class="fas fa-check-circle me-1"></i>Confirm
                        </button>
                    </form>
                    <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-xs btn-warning text-dark">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('sales.invoice', $sale->id) }}" target="_blank" class="btn btn-xs btn-info text-white">
                        <i class="fas fa-file-invoice me-1"></i>Invoice
                    </a>
                    <a href="{{ route('sales.invoice', ['id' => $sale->id, 'type' => 'estimate']) }}" target="_blank" class="btn btn-xs btn-outline-info">
                        <i class="fas fa-calculator me-1"></i>Estimate
                    </a>
                @else
                    {{-- Posted Actions --}}
                    <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-xs btn-warning text-dark">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('sales.invoice', $sale->id) }}" target="_blank" class="btn btn-xs btn-info text-white">
                        <i class="fas fa-file-invoice me-1"></i>Invoice
                    </a>
                    <a href="{{ route('sales.invoice', ['id' => $sale->id, 'type' => 'estimate']) }}" target="_blank" class="btn btn-xs btn-outline-info">
                        <i class="fas fa-calculator me-1"></i>Est.
                    </a>
                    <a href="{{ route('sales.dc', $sale->id) }}" target="_blank" class="btn btn-xs btn-warning text-white" style="background-color: #f97316; border-color: #ea580c;">
                        <i class="fas fa-shipping-fast me-1"></i>DC
                    </a>
                    <a href="{{ route('sales.dc_thermal', $sale->id) }}" target="_blank" class="btn btn-xs btn-secondary text-white">
                        <i class="fas fa-truck me-1"></i>DC Thermal
                    </a>
                    <a href="{{ route('sales.receipt', $sale->id) }}" target="_blank" class="btn btn-xs btn-success text-white">
                        <i class="fas fa-receipt me-1"></i>Receipt
                    </a>
                    @if ($sale->sale_status !== 'returned')
                        <a href="{{ route('sale.return.show', $sale->id) }}" class="btn btn-xs btn-danger text-white">
                            <i class="fas fa-undo me-1"></i>Return
                        </a>
                    @else
                        <button class="btn btn-xs btn-secondary text-white" disabled>Returned</button>
                    @endif
                @endif
            </div>
        </td>
    </tr>
@endforeach
