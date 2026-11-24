@extends('admin_panel.layout.app')

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid py-4">
                <div class="row">
                    <div class="col-lg-12">

                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                            <div>
                                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                    <i class="fas fa-undo text-danger"></i> Sales Returns History
                                </h4>
                                <p class="text-muted mb-0 small">View and manage customer returns, credits and refund history</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a class="btn btn-warning px-3 shadow-sm fw-bold d-inline-flex align-items-center gap-1" href="{{ route('sale.return.create') }}" style="border-radius: 8px;">
                                    <i class="fas fa-undo-alt"></i> Sale Return
                                </a>
                                <a class="btn btn-outline-secondary px-3 shadow-sm fw-medium d-inline-flex align-items-center gap-1" href="{{ route('sale.index') }}" style="border-radius: 8px;">
                                    <i class="fas fa-arrow-left"></i> Back to Sales
                                </a>
                            </div>
                        </div>

                        {{-- Flash Message --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="border mt-1 shadow-sm rounded-3 bg-white">
                            <div class="table-responsive p-3">
                                <table id="return-table" class="table table-bordered table-hover text-center align-middle" style="font-size: 13px;">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Return Invoice</th>
                                            <th>Customer</th>
                                            <th>Warehouse</th>
                                            <th>Return Date</th>
                                            <th>Return Amount</th>
                                            <th>Original Invoice</th>
                                            <th>Total Returned</th>
                                            <th>Refund Type</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($returns as $return)
                                            @php
                                                $isPartialReturn =
                                                    $return->sale &&
                                                    $return->total_returned < $return->original_net_amount;
                                                $isFullReturn =
                                                    $return->sale &&
                                                    $return->total_returned >= $return->original_net_amount;
                                            @endphp
                                            <tr>
                                                <td>{{ $return->id }}</td>
                                                <td>
                                                    <strong class="text-primary">{{ $return->return_invoice }}</strong>
                                                    @if ($return->sale)
                                                        <br><small class="text-muted"><i class="fas fa-file-invoice"></i> {{ $return->sale->invoice_no }}</small>
                                                    @else
                                                        <br><span class="badge bg-info text-dark" style="font-size: 10px;"><i class="fas fa-cart-arrow-down"></i> Direct Return</span>
                                                    @endif
                                                </td>
                                                <td class="text-start">
                                                    <strong>{{ $return->customer->customer_name ?? 'Walking Customer' }}</strong>
                                                    @if (!empty($return->customer->mobile))
                                                        <br><small class="text-muted">{{ $return->customer->mobile }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $return->warehouse->warehouse_name ?? 'Default' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d/m/Y') }}</td>

                                                {{-- Return Amount --}}
                                                <td class="text-danger fw-bold">
                                                    Rs. {{ number_format($return->net_amount, 2) }}
                                                </td>

                                                {{-- Original Sale Amount --}}
                                                <td>
                                                    @if ($return->sale)
                                                        Rs. {{ number_format($return->original_net_amount, 2) }}
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>

                                                {{-- Total Returned --}}
                                                <td class="text-danger">
                                                    @if ($return->sale)
                                                        <strong>Rs. {{ number_format($return->total_returned, 2) }}</strong>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>

                                                {{-- Refund Type --}}
                                                <td>
                                                    @if ($return->refund_type === 'cash')
                                                        <span class="badge bg-success">Cash Refund</span>
                                                    @elseif ($return->refund_type === 'bank')
                                                        <span class="badge bg-info text-dark">Bank Refund</span>
                                                    @else
                                                        <span class="badge bg-primary">Ledger Credit</span>
                                                    @endif
                                                </td>

                                                {{-- Status Badge --}}
                                                <td>
                                                    @if ($isFullReturn)
                                                        <span class="badge bg-danger">Full Return</span>
                                                    @elseif($isPartialReturn)
                                                        <span class="badge bg-warning text-dark">Partial Return</span>
                                                    @else
                                                        <span class="badge bg-secondary">Posted</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <div class="d-inline-flex gap-1">
                                                        <a href="{{ route('sale.return.view', $return->id) }}"
                                                            class="btn btn-sm btn-primary" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('sale.return.print', $return->id) }}"
                                                            class="btn btn-sm btn-dark" title="Print Voucher" target="_blank">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Scripts --}}
                        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
                        <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
                        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

                        <script>
                            $(document).ready(function() {
                                $('#return-table').DataTable({
                                    "pageLength": 10,
                                    "lengthMenu": [5, 10, 25, 50, 100],
                                    "order": [
                                        [0, 'desc']
                                    ],
                                    "language": {
                                        "search": "Search Return:",
                                        "lengthMenu": "Show _MENU_ entries"
                                    }
                                });
                            });
                        </script>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
