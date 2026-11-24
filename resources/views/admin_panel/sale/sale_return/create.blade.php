@extends('admin_panel.layout.app')

@section('content')
    <link href="{{ asset('assets/vendors/select2/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        .sr-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .sr-header {
            padding: 14px 20px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 10px 10px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sr-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sr-section-title {
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 4px;
        }

        .form-control,
        .form-select {
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13px;
            padding: 6px 10px;
            height: 38px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .form-control[readonly] {
            background-color: #f1f5f9;
            cursor: not-allowed;
            color: #334155;
            font-weight: 600;
        }

        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 6px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            font-size: 13px !important;
            padding-left: 10px !important;
            color: #1e293b !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        /* Items Table */
        .sr-table-container {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .sr-table {
            margin-bottom: 0;
            font-size: 13px;
        }

        .sr-table thead th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 8px;
            border-bottom: 2px solid #cbd5e1;
            vertical-align: middle;
        }

        .sr-table tbody td {
            padding: 8px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .sr-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .input-qty {
            width: 85px;
            font-weight: 700;
            text-align: center;
        }

        .input-rate {
            width: 90px;
            font-weight: 600;
            text-align: right;
        }

        /* Refund Option Box */
        .refund-card {
            background-color: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 14px;
        }

        .refund-pill {
            cursor: pointer;
            padding: 10px 14px;
            border-radius: 8px;
            border: 2px solid #cbd5e1;
            background: #ffffff;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .refund-pill:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .refund-pill.active {
            border-color: #2563eb;
            background: #eff6ff;
            color: #1d4ed8;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.1);
        }

        .refund-pill input[type="radio"] {
            margin: 0;
            accent-color: #2563eb;
            transform: scale(1.15);
        }

        /* Summary Card */
        .summary-card {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 16px;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 13px;
            color: #475569;
        }

        .summary-line.total {
            border-top: 2px dashed #cbd5e1;
            padding-top: 12px;
            margin-top: 12px;
            font-size: 17px;
            font-weight: 800;
            color: #1e293b;
        }

        /* Condition badge */
        .badge-good {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .badge-damaged {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Loading spinner */
        #invoiceLoadingSpinner {
            display: none;
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid py-4">

                {{-- Page Header --}}
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-undo-alt text-warning"></i> Sales Return Entry
                        </h4>
                        <p class="text-muted mb-0 small">Create customer sales return with real-time stock & ledger adjustment</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('sale.return.index') }}" class="btn btn-outline-danger px-3 fw-medium d-inline-flex align-items-center gap-1" style="border-radius: 8px;">
                            <i class="fas fa-history"></i> Return History
                        </a>
                        <a href="{{ route('sale.index') }}" class="btn btn-outline-secondary px-3 fw-medium d-inline-flex align-items-center gap-1" style="border-radius: 8px;">
                            <i class="fas fa-arrow-left"></i> Back to Sales
                        </a>
                    </div>
                </div>

                {{-- Flash Alerts --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form id="saleReturnForm" action="{{ route('sale.return.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="form_token" value="{{ uniqid('sr_tok_', true) }}">
                    <input type="hidden" name="action" id="formAction" value="save">

                    {{-- CARD 1: Header Information --}}
                    <div class="sr-card">
                        <div class="sr-header">
                            <h5><i class="fas fa-file-invoice text-primary me-2"></i> Return Information & Source Invoice</h5>
                            <span class="badge bg-primary px-3 py-2 text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                                Return # <span id="displayReturnNo">{{ $nextReturnNumber }}</span>
                            </span>
                        </div>
                        <div class="p-4">
                            {{-- RETURN MODE SELECTOR --}}
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-3 border-bottom">
                                <div class="fw-bold text-secondary">
                                    <i class="fas fa-layer-group me-1 text-primary"></i> Select Return Mode:
                                </div>
                                <div class="btn-group" role="group" aria-label="Return Type Selector">
                                    <input type="radio" class="btn-check" name="return_type" id="return_type_invoice" value="invoice" checked autocomplete="off">
                                    <label class="btn btn-outline-primary px-3 py-2 fw-semibold" for="return_type_invoice">
                                        <i class="fas fa-file-invoice me-1"></i> Option 1: Return Against Invoice
                                    </label>

                                    <input type="radio" class="btn-check" name="return_type" id="return_type_direct" value="direct" autocomplete="off">
                                    <label class="btn btn-outline-success px-3 py-2 fw-semibold" for="return_type_direct">
                                        <i class="fas fa-cart-arrow-down me-1"></i> Option 2: Direct Return (Without Invoice)
                                    </label>
                                </div>
                            </div>

                            <div class="row g-3">
                                {{-- Return Number --}}
                                <div class="col-md-2 col-sm-6">
                                    <label class="form-label">Return No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="return_invoice" value="{{ $nextReturnNumber }}" readonly>
                                </div>

                                {{-- Return Date --}}
                                <div class="col-md-2 col-sm-6">
                                    <label class="form-label">Return Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="return_date" id="return_date" value="{{ date('Y-m-d') }}" required>
                                </div>

                                {{-- Warehouse --}}
                                <div class="col-md-2 col-sm-6">
                                    <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                                    <select class="form-select" name="warehouse_id" id="warehouse_id" data-default="{{ $defaultWarehouseId ?? 1 }}" required>
                                        @foreach ($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ (isset($defaultWarehouseId) && $wh->id == $defaultWarehouseId) ? 'selected' : '' }}>
                                                {{ $wh->warehouse_name ?? $wh->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Customer Selection --}}
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label">Customer Selection</label>
                                    <select class="form-select select2-customer" name="customer_id" id="customer_id">
                                        <option value="all">-- All Customers (Search Any) --</option>
                                        <option value="walkin" data-balance="0">Walk-in Customer</option>
                                        @foreach ($customers as $c)
                                            <option value="{{ $c->id }}" data-balance="{{ (float)($c->previous_balance ?? 0) }}">
                                                {{ $c->customer_name }} ({{ $c->mobile ?? 'No phone' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Original Invoice Selection (Invoice Mode) --}}
                                <div class="col-md-3 col-sm-12" id="invoiceSelectCol">
                                    <label class="form-label">Original Sales Invoice <span class="text-danger">*</span></label>
                                    <select class="form-select select2-invoice" name="sale_id" id="sale_id" required>
                                        <option value="">-- Select Sales Invoice --</option>
                                        @foreach ($recentSales as $s)
                                            <option value="{{ $s->id }}" {{ (isset($selectedSaleId) && $selectedSaleId == $s->id) ? 'selected' : '' }}>
                                                {{ $s->invoice_no }} - {{ $s->created_at ? $s->created_at->format('d/m/Y') : '' }} (Rs. {{ number_format($s->total_net, 2) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Direct Return Notice Badge (Direct Mode) --}}
                                <div class="col-md-3 col-sm-12 d-none" id="directReturnBadgeCol">
                                    <label class="form-label">Return Type</label>
                                    <div class="p-2 border rounded bg-light text-center">
                                        <span class="badge bg-success px-2 py-1"><i class="fas fa-check-circle me-1"></i> Direct Sales Return</span>
                                        <small class="d-block text-muted mt-1" style="font-size: 11px;">No Original Invoice Required</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Invoice Info Banner --}}
                            <div id="invoiceInfoBanner" class="mt-3 p-3 bg-light border rounded-3 d-none">
                                <div class="row align-items-center g-2">
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Invoice No</small>
                                        <div class="fw-bold text-dark fs-6" id="bannerInvoiceNo">-</div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Invoice Date</small>
                                        <div class="fw-semibold text-dark" id="bannerInvoiceDate">-</div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Customer</small>
                                        <div class="fw-semibold text-dark" id="bannerCustomerName">-</div>
                                        <small class="text-primary fw-bold" id="bannerCustomerBalance">Balance: Rs. 0.00</small>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Original Total Net</small>
                                        <div class="fw-bold text-success fs-6" id="bannerInvoiceNet">Rs. 0.00</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Invoice loading indicator --}}
                            <div id="invoiceLoadingSpinner" class="text-center py-3" style="display: none;">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                <span class="ms-2 text-muted fw-semibold">Loading sold products from invoice...</span>
                            </div>
                        </div>
                    </div>

                    {{-- CARD 2: Sold Products & Return Details Table --}}
                    <div class="sr-card">
                        <div class="sr-header">
                            <h5><i class="fas fa-boxes text-info me-2"></i> <span id="card2Title">Sold Products & Return Details</span></h5>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="btnReturnAll" disabled>
                                    <i class="fas fa-check-double me-1"></i> Return All Available
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnClearReturn" disabled>
                                    <i class="fas fa-eraser me-1"></i> Clear
                                </button>
                            </div>
                        </div>
                        <div class="p-3">
                            {{-- Direct Mode: Product Search Bar --}}
                            <div id="directProductSearchSection" class="p-3 bg-light border rounded-3 mb-3 d-none">
                                <div class="row align-items-center g-2">
                                    <div class="col-md-9 col-sm-12">
                                        <label class="form-label fw-bold mb-1"><i class="fas fa-search me-1 text-primary"></i> Search Product to Return (by Name, Item Code or Barcode)</label>
                                        <select class="form-select select2-product-search" id="direct_product_search" style="width: 100%;">
                                            <option value="">Type product name or code to search...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-12 pt-md-4">
                                        <button type="button" class="btn btn-success w-100 fw-bold" id="btnAddDirectProduct">
                                            <i class="fas fa-plus-circle me-1"></i> Add to Return
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive sr-table-container">
                                <table class="table sr-table align-middle" id="returnItemsTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;" class="text-center">#</th>
                                            <th style="min-width: 220px;">Product & Details</th>
                                            <th style="width: 90px;" class="text-center">Sold (Pcs)</th>
                                            <th style="width: 90px;" class="text-center">Returned</th>
                                            <th style="width: 90px;" class="text-center">Available</th>
                                            <th style="width: 110px;" class="text-center">Return Qty <span class="text-danger">*</span></th>
                                            <th style="width: 100px;" class="text-end">Rate (Rs)</th>
                                            <th style="width: 80px;" class="text-end">Disc</th>
                                            <th style="min-width: 140px;">Return Reason</th>
                                            <th style="min-width: 150px;">Stock Condition</th>
                                            <th style="width: 110px;" class="text-end">Line Total (Rs)</th>
                                            <th style="width: 50px;" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                        <tr id="emptyTableMessage">
                                            <td colspan="12" class="text-center py-5 text-muted">
                                                <i class="fas fa-shopping-bag fa-3x mb-3 text-secondary opacity-50"></i>
                                                <p class="mb-0 fw-semibold" id="emptyTableText">Please select an Original Sales Invoice above to load sold products.</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- CARD 3: Refund Options, Accounting & Financial Summary --}}
                    <div class="row g-3 mb-4">
                        {{-- Left Column: Refund Option & Remarks --}}
                        <div class="col-lg-7">
                            <div class="sr-card h-100 mb-0">
                                <div class="sr-header">
                                    <h5><i class="fas fa-hand-holding-usd text-success me-2"></i> Refund Option & Stock Adjustment</h5>
                                </div>
                                <div class="p-4">
                                    {{-- General Return Reason --}}
                                    <div class="mb-4">
                                        <label class="form-label">General Return Reason</label>
                                        <select class="form-select" name="return_reason" id="general_return_reason">
                                            <option value="">-- Select Reason --</option>
                                            <option value="Damaged Goods">Damaged Goods</option>
                                            <option value="Wrong Item Delivered">Wrong Item Delivered</option>
                                            <option value="Quality Issue">Quality Issue</option>
                                            <option value="Customer Change of Mind">Customer Change of Mind</option>
                                            <option value="Expired / Nearing Expiry">Expired / Nearing Expiry</option>
                                            <option value="Other">Other Reason</option>
                                        </select>
                                    </div>

                                    {{-- Refund Options --}}
                                    <div class="refund-card mb-4">
                                        <div class="sr-section-title">
                                            <i class="fas fa-wallet text-primary"></i> Select Refund Method <span class="text-danger">*</span>
                                        </div>

                                        {{-- 1. Customer Ledger Adjustment --}}
                                        <label class="refund-pill active" id="pillLedger">
                                            <input type="radio" name="refund_type" value="ledger" checked>
                                            <div>
                                                <div>Customer Ledger Adjustment <span class="badge bg-primary ms-1">Recommended</span></div>
                                                <small class="text-muted fw-normal d-block">Credits return amount directly to customer ledger. Customer debt is reduced.</small>
                                            </div>
                                        </label>

                                        {{-- 2. Cash Refund --}}
                                        <label class="refund-pill" id="pillCash">
                                            <input type="radio" name="refund_type" value="cash">
                                            <div>
                                                <div>Cash Refund</div>
                                                <small class="text-muted fw-normal d-block">Cash is paid immediately to customer from Cash Account.</small>
                                            </div>
                                        </label>

                                        {{-- 3. Bank Refund --}}
                                        <label class="refund-pill" id="pillBank">
                                            <input type="radio" name="refund_type" value="bank">
                                            <div>
                                                <div>Bank Refund</div>
                                                <small class="text-muted fw-normal d-block">Refund is processed via Bank account transfer / cheque.</small>
                                            </div>
                                        </label>

                                        {{-- Account Selection (Shown only when Cash or Bank is selected) --}}
                                        <div id="accountSelectWrapper" class="mt-3 p-3 bg-white border rounded d-none">
                                            <label class="form-label" id="accountSelectLabel">Select Payment Account <span class="text-danger">*</span></label>
                                            <select class="form-select" name="refund_account_id" id="refund_account_id">
                                                @foreach ($accounts as $acc)
                                                    @php
                                                        $isCash = stripos($acc->title, 'Cash') !== false || ($acc->account_code === 'CASH');
                                                        $isBank = stripos($acc->title, 'Bank') !== false || ($acc->account_code === 'BANK');
                                                        $type = $isBank ? 'bank' : ($isCash ? 'cash' : 'other');
                                                    @endphp
                                                    <option value="{{ $acc->id }}" data-type="{{ $type }}">
                                                        {{ $acc->title }} ({{ $acc->account_code ?? 'Acc #'.$acc->id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Stock Adjustment Summary Indicator --}}
                                    <div class="p-3 bg-light border rounded mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold small text-muted text-uppercase">Stock Adjustment Preview:</span>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="badge badge-good px-3 py-2 fw-semibold">
                                                <i class="fas fa-boxes me-1"></i> Good Stock (Restocked): <span id="summaryGoodPieces">0</span> pcs
                                            </span>
                                            <span class="badge badge-damaged px-3 py-2 fw-semibold">
                                                <i class="fas fa-tools me-1"></i> Damaged Stock (Segregated): <span id="summaryDamagedPieces">0</span> pcs
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Remarks --}}
                                    <div>
                                        <label class="form-label">Remarks / Additional Notes</label>
                                        <textarea class="form-control" name="remarks" id="remarks" rows="2" placeholder="Enter remarks or notes about this return..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Financial Calculations Summary --}}
                        <div class="col-lg-5">
                            <div class="sr-card h-100 mb-0">
                                <div class="sr-header">
                                    <h5><i class="fas fa-calculator text-primary me-2"></i> Return Calculations</h5>
                                </div>
                                <div class="p-4">
                                    <div class="summary-card">
                                        <div class="summary-line">
                                            <span>Items Gross Amount:</span>
                                            <strong class="text-dark" id="calcGrossSubtotal">Rs. 0.00</strong>
                                        </div>
                                        <div class="summary-line">
                                            <span>Item Discounts:</span>
                                            <strong class="text-danger" id="calcItemDiscount">- Rs. 0.00</strong>
                                        </div>
                                        <div class="summary-line">
                                            <span>Items Net Subtotal:</span>
                                            <strong class="text-dark" id="calcNetSubtotal">Rs. 0.00</strong>
                                        </div>
                                        <div class="summary-line align-items-center">
                                            <span>Extra Return Discount:</span>
                                            <div style="width: 140px;">
                                                <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end fw-bold" name="extra_discount" id="extra_discount" value="0.00">
                                            </div>
                                        </div>
                                        <div class="summary-line total">
                                            <span>Net Return Amount:</span>
                                            <span class="text-primary" id="calcNetAmount">Rs. 0.00</span>
                                        </div>
                                    </div>

                                    {{-- Customer Balance Impact Preview --}}
                                    <div id="customerBalancePreview" class="mt-3 p-3 bg-light border rounded">
                                        <div class="small fw-bold text-muted text-uppercase mb-2">Customer Balance Impact</div>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">Current Balance:</span>
                                            <strong id="impactCurrentBal">Rs. 0.00</strong>
                                        </div>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted" id="impactMethodLabel">Ledger Credit:</span>
                                            <strong class="text-success" id="impactCredit">- Rs. 0.00</strong>
                                        </div>
                                        <div class="d-flex justify-content-between small pt-1 border-top">
                                            <span class="fw-bold text-dark">New Balance:</span>
                                            <strong class="fw-bold text-dark" id="impactNewBal">Rs. 0.00</strong>
                                        </div>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="mt-4 d-grid gap-2">
                                        <button type="submit" id="btnSaveAndPrint" name="action_btn" value="save_and_print" class="btn btn-success py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm" style="border-radius: 8px;">
                                            <i class="fas fa-print"></i> Save Return & Print Voucher
                                        </button>
                                        <button type="submit" id="btnSave" name="action_btn" value="save" class="btn btn-primary py-2 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm" style="border-radius: 8px;">
                                            <i class="fas fa-save"></i> Save Return Only
                                        </button>
                                        <a href="{{ route('sale.index') }}" class="btn btn-outline-secondary py-2 fw-medium" style="border-radius: 8px;">
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/select2/js/select2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2-customer').select2({
                placeholder: "-- All Customers --",
                width: '100%'
            });

            $('.select2-invoice').select2({
                placeholder: "-- Select Sales Invoice --",
                width: '100%'
            });

            // Product Search Select2 for Direct Return
            $('.select2-product-search').select2({
                placeholder: "Type product name, item code or barcode...",
                allowClear: true,
                width: '100%',
                ajax: {
                    url: "{{ route('sale.return.search_products') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { 
                            q: params.term,
                            warehouse_id: $('#warehouse_id').val()
                        };
                    },
                    processResults: function (data) {
                        return { results: data.products };
                    },
                    cache: true
                },
                minimumInputLength: 1
            });

            let currentSaleData = null;
            let currentItems = [];
            let isSyncingFromInvoice = false;
            let currentReturnMode = 'invoice'; // 'invoice' or 'direct'
            let directRowCounter = 100;

            // 0. Return Mode Switcher (Option 1 vs Option 2)
            $('input[name="return_type"]').on('change', function() {
                currentReturnMode = $(this).val();

                if (currentReturnMode === 'direct') {
                    // Switch to Direct Return Mode
                    $('#invoiceSelectCol').addClass('d-none');
                    $('#directReturnBadgeCol').removeClass('d-none');
                    $('#sale_id').prop('required', false).val('').trigger('change.select2');
                    $('#invoiceInfoBanner').addClass('d-none');
                    $('#invoiceLoadingSpinner').hide();

                    $('#directProductSearchSection').removeClass('d-none');
                    $('#card2Title').text('Direct Return Products (Manual Selection)');
                    $('#btnReturnAll, #btnClearReturn').addClass('d-none');

                    renderDirectEmptyTable();
                } else {
                    // Switch to Return Against Invoice Mode
                    $('#invoiceSelectCol').removeClass('d-none');
                    $('#directReturnBadgeCol').addClass('d-none');
                    $('#sale_id').prop('required', true);

                    $('#directProductSearchSection').addClass('d-none');
                    $('#card2Title').text('Sold Products & Return Details');
                    $('#btnReturnAll, #btnClearReturn').removeClass('d-none');

                    renderEmptyTable();
                }
                recalculate();
            });

            // 1. Customer Selection Changed -> Filter Invoice Dropdown
            $('#customer_id').on('change', function() {
                if (isSyncingFromInvoice) return;

                const customerId = $(this).val();
                if (!customerId) return;

                if (customerId === 'walkin') {
                    // Walk-in customer has no customer ledger in ERP: switch to cash refund
                    $('#pillLedger').addClass('opacity-50').css('pointer-events', 'none');
                    $('#pillCash').find('input').prop('checked', true).trigger('change');
                } else {
                    $('#pillLedger').removeClass('opacity-50').css('pointer-events', 'auto');
                }

                if (currentReturnMode === 'invoice') {
                    const url = "{{ url('sale/return/get-invoices-by-customer') }}/" + customerId;
                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        beforeSend: function() {
                            $('#sale_id').prop('disabled', true);
                        },
                        success: function(res) {
                            $('#sale_id').prop('disabled', false).empty().append('<option value="">-- Select Sales Invoice --</option>');
                            if (res.status && res.sales) {
                                res.sales.forEach(function(s) {
                                    $('#sale_id').append(
                                        $('<option>', {
                                             value: s.id,
                                            text: `${s.invoice_no} - ${s.date} (Rs. ${parseFloat(s.total_net).toFixed(2)})`
                                        })
                                    );
                                });
                            }
                        },
                        error: function() {
                            $('#sale_id').prop('disabled', false);
                        }
                    });
                } else {
                    // In Direct Return mode, update customer balance impact
                    recalculate();
                }
            });

            // 2. Invoice Selection Changed -> Fetch Items & Header Info
            $('#sale_id').on('change', function() {
                if (currentReturnMode !== 'invoice') return;

                const saleId = $(this).val();
                if (!saleId) {
                    $('#invoiceInfoBanner').addClass('d-none');
                    renderEmptyTable();
                    recalculate();
                    return;
                }

                $('#invoiceLoadingSpinner').show();

                const url = "{{ url('sale/return/get-invoice-items') }}/" + saleId;
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        $('#invoiceLoadingSpinner').hide();
                        if (res.status) {
                            currentSaleData = res.sale;
                            currentItems = res.items;

                            // Update Banner
                            $('#bannerInvoiceNo').text(res.sale.invoice_no);
                            $('#bannerInvoiceDate').text(res.sale.date);
                            $('#bannerCustomerName').text(res.sale.customer_name);
                            $('#bannerCustomerBalance').text('Balance: Rs. ' + parseFloat(res.sale.customer_balance).toFixed(2));
                            $('#bannerInvoiceNet').text('Rs. ' + parseFloat(res.sale.total_net).toFixed(2));
                            $('#invoiceInfoBanner').removeClass('d-none');

                            // Set Warehouse if provided
                            if (res.sale.warehouse_id) {
                                $('#warehouse_id').val(res.sale.warehouse_id);
                            }

                            // Sync customer dropdown safely without triggering reset of #sale_id
                            if (res.sale.customer_id && $('#customer_id').val() != res.sale.customer_id) {
                                isSyncingFromInvoice = true;
                                $('#customer_id').val(res.sale.customer_id).trigger('change.select2');
                                isSyncingFromInvoice = false;
                            }

                            // Render Items
                            renderItemsTable(res.items);

                            // Enable buttons if items available
                            if (res.items && res.items.length > 0) {
                                $('#btnReturnAll, #btnClearReturn').prop('disabled', false);
                            }

                            recalculate();
                        } else {
                            alert(res.message || 'Failed to load sale invoice.');
                        }
                    },
                    error: function(xhr) {
                        $('#invoiceLoadingSpinner').hide();
                        alert('Error loading invoice details. Please try again.');
                    }
                });
            });

            // Pre-load if sale_id passed in URL
            @if (!empty($selectedSaleId))
                $('#sale_id').val("{{ $selectedSaleId }}").trigger('change');
            @endif

            // 3. Render Items in Table (Invoice Mode)
            function renderItemsTable(items) {
                const $tbody = $('#itemsTableBody');
                $tbody.empty();

                if (!items || items.length === 0) {
                    $tbody.html('<tr><td colspan="12" class="text-center py-4 text-muted">No products found in this invoice.</td></tr>');
                    return;
                }

                items.forEach(function(item, idx) {
                    const rowNum = idx + 1;
                    const maxReturn = parseFloat(item.remaining_returnable);
                    const isFullyReturned = maxReturn <= 0;

                    const rowHtml = `
                        <tr class="${isFullyReturned ? 'table-light opacity-75' : ''}" data-idx="${idx}">
                            <td class="text-center fw-bold text-muted">${rowNum}</td>
                            <td>
                                <input type="hidden" name="product_id[${idx}]" value="${item.product_id}">
                                <input type="hidden" name="color[${idx}]" value="${item.color || ''}">
                                <strong>${escapeHtml(item.product_name)}</strong>
                                ${item.variant_label ? `<br><span class="badge bg-light text-dark border">${escapeHtml(item.variant_label)}</span>` : ''}
                                ${item.product_code ? `<br><small class="text-muted">Code: ${escapeHtml(item.product_code)}</small>` : ''}
                            </td>
                            <td class="text-center fw-semibold">${parseFloat(item.sold_total_pieces).toFixed(0)}</td>
                            <td class="text-center text-danger fw-semibold">${parseFloat(item.already_returned).toFixed(0)}</td>
                            <td class="text-center">
                                ${isFullyReturned 
                                    ? '<span class="badge bg-secondary">Fully Returned</span>' 
                                    : `<span class="badge bg-success">${maxReturn.toFixed(0)} Available</span>`
                                }
                            </td>
                            <td class="text-center">
                                <input type="number" 
                                    step="1" 
                                    min="0" 
                                    max="${maxReturn}" 
                                    class="form-control form-control-sm input-qty row-qty" 
                                    name="qty[${idx}]" 
                                    value="0" 
                                    data-max="${maxReturn}" 
                                    ${isFullyReturned ? 'disabled' : ''}>
                            </td>
                            <td class="text-end">
                                <input type="number" 
                                    step="0.01" 
                                    min="0" 
                                    class="form-control form-control-sm input-rate row-rate text-end" 
                                    name="price[${idx}]" 
                                    value="${parseFloat(item.price).toFixed(2)}" 
                                    ${isFullyReturned ? 'disabled' : ''}>
                            </td>
                            <td class="text-end">
                                <input type="number" 
                                    step="0.01" 
                                    min="0" 
                                    class="form-control form-control-sm text-end row-disc" 
                                    style="width: 75px;" 
                                    name="item_disc[${idx}]" 
                                    value="0.00" 
                                    ${isFullyReturned ? 'disabled' : ''}>
                            </td>
                            <td>
                                <select class="form-select form-select-sm row-reason" name="item_reason[${idx}]" ${isFullyReturned ? 'disabled' : ''}>
                                    <option value="Damaged Goods">Damaged</option>
                                    <option value="Wrong Item">Wrong Item</option>
                                    <option value="Quality Issue" selected>Quality Issue</option>
                                    <option value="Customer Change of Mind">Change of Mind</option>
                                    <option value="Other">Other</option>
                                </select>
                            </td>
                            <td>
                                <select class="form-select form-select-sm row-condition" name="stock_condition[${idx}]" ${isFullyReturned ? 'disabled' : ''}>
                                    <option value="good" selected>Good Stock (Restock)</option>
                                    <option value="damaged">Damaged (Quarantine)</option>
                                </select>
                            </td>
                            <td class="text-end fw-bold row-line-total text-dark">Rs. 0.00</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-reset-row" title="Reset quantity to 0" ${isFullyReturned ? 'disabled' : ''}>
                                    <i class="fas fa-undo"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    $tbody.append(rowHtml);
                });
            }

            function renderEmptyTable() {
                $('#itemsTableBody').html(`
                    <tr id="emptyTableMessage">
                        <td colspan="12" class="text-center py-5 text-muted">
                            <i class="fas fa-shopping-bag fa-3x mb-3 text-secondary opacity-50"></i>
                            <p class="mb-0 fw-semibold">Please select an Original Sales Invoice above to load sold products.</p>
                        </td>
                    </tr>
                `);
                $('#btnReturnAll, #btnClearReturn').prop('disabled', true);
            }

            function renderDirectEmptyTable() {
                $('#itemsTableBody').html(`
                    <tr id="emptyTableMessage">
                        <td colspan="12" class="text-center py-5 text-muted">
                            <i class="fas fa-boxes fa-3x mb-3 text-secondary opacity-50"></i>
                            <p class="mb-0 fw-semibold">No products added yet. Use the product search bar above to add products to return.</p>
                        </td>
                    </tr>
                `);
            }

            // Direct Mode: Add Product to Table
            function addDirectProduct(productData) {
                if (!productData || !productData.product_id) return;

                const rowKey = String(productData.id || productData.product_id);

                // Check if product or exact variant is already added
                let exists = false;
                $('#itemsTableBody tr').each(function() {
                    if ($(this).attr('data-row-key') === rowKey) {
                        exists = true;
                        const $qtyInput = $(this).find('.row-qty');
                        $qtyInput.val((parseFloat($qtyInput.val()) || 0) + 1);
                        $qtyInput.trigger('input');
                    }
                });

                if (exists) {
                    $('.select2-product-search').val(null).trigger('change');
                    return;
                }

                $('#emptyTableMessage').remove();

                const idx = directRowCounter++;
                const rowNum = $('#itemsTableBody tr').length + 1;
                const price = parseFloat(productData.price) || 0;

                const variantBadgeHtml = productData.has_variant ? `
                    <br>
                    <span class="badge bg-light text-dark border me-1">Variant: ${escapeHtml(productData.variant_name)}</span>
                    <span class="badge bg-info-subtle text-info-emphasis border me-1">Size: ${escapeHtml(productData.size)}</span>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis border">Color: ${escapeHtml(productData.color_val)}</span>
                    <br><small class="text-muted">SKU: ${escapeHtml(productData.sku)} | Barcode: ${escapeHtml(productData.barcode || '-')}</small>
                ` : `
                    ${productData.sku ? `<br><small class="text-muted">Code: ${escapeHtml(productData.sku)}</small>` : ''}
                `;

                const rowHtml = `
                    <tr data-idx="${idx}" data-row-key="${escapeHtml(rowKey)}">
                        <td class="text-center fw-bold text-muted row-counter">${rowNum}</td>
                        <td>
                            <input type="hidden" class="input-product-id" name="product_id[${idx}]" value="${productData.product_id}">
                            <input type="hidden" name="color[${idx}]" value="${escapeHtml(productData.variant_data || '')}">
                            <strong>${escapeHtml(productData.item_name)}</strong>
                            ${productData.unit ? `<span class="badge bg-light text-secondary border ms-1">${escapeHtml(productData.unit)}</span>` : ''}
                            ${productData.brand ? `<span class="badge bg-light text-secondary border ms-1">${escapeHtml(productData.brand)}</span>` : ''}
                            ${variantBadgeHtml}
                        </td>
                        <td class="text-center text-muted">-</td>
                        <td class="text-center text-muted">-</td>
                        <td class="text-center">
                            <span class="badge bg-info mb-1">${productData.has_variant ? 'Variant' : 'Direct'}</span>
                            <br><small class="text-muted">Stock: ${parseFloat(productData.available_stock || 0).toFixed(0)} ${escapeHtml(productData.unit || 'pcs')}</small>
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                step="1" 
                                min="1" 
                                class="form-control form-control-sm input-qty row-qty" 
                                name="qty[${idx}]" 
                                value="1">
                        </td>
                        <td class="text-end">
                            <input type="number" 
                                step="0.01" 
                                min="0" 
                                class="form-control form-control-sm input-rate row-rate text-end" 
                                name="price[${idx}]" 
                                value="${price.toFixed(2)}">
                        </td>
                        <td class="text-end">
                            <input type="number" 
                                step="0.01" 
                                min="0" 
                                class="form-control form-control-sm text-end row-disc" 
                                style="width: 75px;" 
                                name="item_disc[${idx}]" 
                                value="0.00">
                        </td>
                        <td>
                            <select class="form-select form-select-sm row-reason" name="item_reason[${idx}]">
                                <option value="Damaged Goods">Damaged</option>
                                <option value="Wrong Item">Wrong Item</option>
                                <option value="Quality Issue" selected>Quality Issue</option>
                                <option value="Customer Change of Mind">Change of Mind</option>
                                <option value="Other">Other</option>
                            </select>
                        </td>
                        <td>
                            <select class="form-select form-select-sm row-condition" name="stock_condition[${idx}]">
                                <option value="good" selected>Good Stock (Restock)</option>
                                <option value="damaged">Damaged (Quarantine)</option>
                            </select>
                        </td>
                        <td class="text-end fw-bold row-line-total text-dark">Rs. ${price.toFixed(2)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Remove Item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#itemsTableBody').append(rowHtml);
                $('.select2-product-search').val(null).trigger('change');
                recalculate();
            }

            // Direct Mode: Add product button
            $('#btnAddDirectProduct').on('click', function() {
                const data = $('.select2-product-search').select2('data');
                if (data && data.length > 0 && data[0].id) {
                    addDirectProduct(data[0]);
                } else {
                    alert('Please search and select a product first.');
                }
            });

            // Direct Mode: Select2 selection triggers addition
            $('.select2-product-search').on('select2:select', function(e) {
                const data = e.params.data;
                if (data && data.id) {
                    addDirectProduct(data);
                }
            });

            // Remove Row Button (Direct Mode)
            $(document).on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
                $('#itemsTableBody tr').each(function(i) {
                    $(this).find('.row-counter').text(i + 1);
                });
                if ($('#itemsTableBody tr').length === 0) {
                    renderDirectEmptyTable();
                }
                recalculate();
            });

            // Reset Row Button (Invoice Mode)
            $(document).on('click', '.btn-reset-row', function() {
                const $row = $(this).closest('tr');
                $row.find('.row-qty').val(0);
                recalculate();
            });

            // 4. Return Quantity Input Validation & Dynamic Recalculation
            $(document).on('input change', '.row-qty', function() {
                const $input = $(this);
                let val = parseFloat($input.val()) || 0;
                const max = parseFloat($input.data('max'));

                if (val < 0) {
                    val = 0;
                    $input.val(0);
                } else if (currentReturnMode === 'invoice' && max !== undefined && !isNaN(max) && val > max) {
                    alert(`Return quantity cannot exceed remaining returnable quantity of ${max}.`);
                    val = max;
                    $input.val(max);
                }

                recalculate();
            });

            $(document).on('input change', '.row-rate, .row-disc, #extra_discount, .row-condition', function() {
                recalculate();
            });

            // Return All Button
            $('#btnReturnAll').on('click', function() {
                $('.row-qty').each(function() {
                    const max = parseFloat($(this).data('max')) || 0;
                    if (max > 0) {
                        $(this).val(max);
                    }
                });
                recalculate();
            });

            // Clear Button
            $('#btnClearReturn').on('click', function() {
                $('.row-qty').val(0);
                recalculate();
            });

            // 5. Overall Calculation Engine
            function recalculate() {
                let grossSubtotal = 0;
                let totalItemDiscount = 0;
                let goodPieces = 0;
                let damagedPieces = 0;

                $('#itemsTableBody tr').each(function() {
                    const $row = $(this);
                    const qty = parseFloat($row.find('.row-qty').val()) || 0;
                    const rate = parseFloat($row.find('.row-rate').val()) || 0;
                    const disc = parseFloat($row.find('.row-disc').val()) || 0;
                    const condition = $row.find('.row-condition').val() || 'good';

                    if (qty > 0) {
                        const itemGross = qty * rate;
                        const lineTotal = Math.max(0, itemGross - disc);
                        $row.find('.row-line-total').text('Rs. ' + lineTotal.toFixed(2));
                        grossSubtotal += itemGross;
                        totalItemDiscount += disc;

                        if (condition === 'good') {
                            goodPieces += qty;
                        } else {
                            damagedPieces += qty;
                        }
                    } else {
                        $row.find('.row-line-total').text('Rs. 0.00');
                    }
                });

                const extraDiscount = parseFloat($('#extra_discount').val()) || 0;
                const netSubtotal = Math.max(0, grossSubtotal - totalItemDiscount);
                const netAmount = Math.max(0, netSubtotal - extraDiscount);

                // Update UI Summary
                $('#calcGrossSubtotal').text('Rs. ' + grossSubtotal.toFixed(2));
                $('#calcItemDiscount').text('- Rs. ' + totalItemDiscount.toFixed(2));
                $('#calcNetSubtotal').text('Rs. ' + netSubtotal.toFixed(2));
                $('#calcNetAmount').text('Rs. ' + netAmount.toFixed(2));

                $('#summaryGoodPieces').text(goodPieces);
                $('#summaryDamagedPieces').text(damagedPieces);

                // Update Customer Balance Impact
                const isWalkin = (currentReturnMode === 'invoice' && currentSaleData && (!currentSaleData.customer_id || currentSaleData.customer_name === 'Walking Customer')) || ($('#customer_id').val() === 'walkin');

                const currentBal = isWalkin 
                    ? 0 
                    : ((currentReturnMode === 'invoice' && currentSaleData)
                        ? parseFloat(currentSaleData.customer_balance) || 0
                        : (parseFloat($('#customer_id option:selected').data('balance')) || 0));

                const refundType = $('input[name="refund_type"]:checked').val();

                if (isWalkin) {
                    $('#impactCurrentBal').text('Walk-in Customer (Rs. 0.00)');
                    $('#impactMethodLabel').text('Walk-in Refund:');
                    $('#impactCredit').text('Rs. ' + netAmount.toFixed(2) + ' (Paid via ' + (refundType || 'cash').toUpperCase() + ')');
                    $('#impactNewBal').text('Rs. 0.00 (No Customer Account)');
                } else {
                    $('#impactCurrentBal').text('Rs. ' + currentBal.toFixed(2));

                    if (refundType === 'ledger') {
                        $('#impactMethodLabel').text('Ledger Credit:');
                        $('#impactCredit').text('- Rs. ' + netAmount.toFixed(2));
                        const newBal = currentBal - netAmount;
                        $('#impactNewBal').text('Rs. ' + newBal.toFixed(2));
                    } else {
                        $('#impactMethodLabel').text(`${(refundType || 'cash').toUpperCase()} Paid:`);
                        $('#impactCredit').text('Rs. 0.00 (Paid in ' + (refundType || 'cash') + ')');
                        $('#impactNewBal').text('Rs. ' + currentBal.toFixed(2) + ' (Unchanged)');
                    }
                }
            }

            // 6. Refund Method Selector
            function updateAccountDropdown(selectedType) {
                $('#refund_account_id option').each(function() {
                    const accType = $(this).data('type');
                    if (selectedType === 'cash') {
                        if (accType === 'cash' || accType === 'other') {
                            $(this).show().prop('disabled', false);
                        } else {
                            $(this).hide().prop('disabled', true);
                        }
                    } else if (selectedType === 'bank') {
                        if (accType === 'bank' || accType === 'other') {
                            $(this).show().prop('disabled', false);
                        } else {
                            $(this).hide().prop('disabled', true);
                        }
                    }
                });

                // Pick first enabled option
                const firstValid = $('#refund_account_id option:not(:disabled):first').val();
                if (firstValid) {
                    $('#refund_account_id').val(firstValid);
                }
            }

            $('input[name="refund_type"]').on('change', function() {
                $('.refund-pill').removeClass('active');
                $(this).closest('.refund-pill').addClass('active');

                const val = $(this).val();
                if (val === 'cash' || val === 'bank') {
                    $('#accountSelectWrapper').removeClass('d-none');
                    $('#accountSelectLabel').text(`Select ${val.toUpperCase()} Account`);
                    updateAccountDropdown(val);
                } else {
                    $('#accountSelectWrapper').addClass('d-none');
                }

                recalculate();
            });

            // Sync general reason to all item reasons if changed
            $('#general_return_reason').on('change', function() {
                const reason = $(this).val();
                if (reason) {
                    $('.row-reason').val(reason);
                }
            });

            // Handle Save vs Save & Print actions
            let isSubmitting = false;

            $('#btnSaveAndPrint').on('click', function() {
                $('#formAction').val('save_and_print');
            });

            $('#btnSave').on('click', function() {
                $('#formAction').val('save');
            });

            // Form Submit Validation & Double-click Protection
            $('#saleReturnForm').on('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }

                let hasReturnQty = false;
                $('.row-qty').each(function() {
                    if ((parseFloat($(this).val()) || 0) > 0) {
                        hasReturnQty = true;
                    }
                });

                if (!hasReturnQty) {
                    e.preventDefault();
                    alert('Please enter a return quantity greater than 0 for at least one product before saving.');
                    return false;
                }

                isSubmitting = true;
                const act = $('#formAction').val();
                if (act === 'save_and_print') {
                    $('#btnSaveAndPrint').html('<span class="spinner-border spinner-border-sm me-2"></span>Saving & Opening Voucher...');
                } else {
                    $('#btnSave').html('<span class="spinner-border spinner-border-sm me-2"></span>Saving Return...');
                }

                setTimeout(function() {
                    $('#btnSaveAndPrint, #btnSave').prop('disabled', true).addClass('disabled');
                }, 50);
            });

            function escapeHtml(text) {
                if (!text) return '';
                return $('<div>').text(text).html();
            }
        });
    </script>
@endsection
