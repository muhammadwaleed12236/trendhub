@extends('admin_panel.layout.app')

@section('content')
    <!-- Loader Overlay -->
    <div id="pageLoader"
        class="{{ isset($sale) ? '' : 'd-none' }} position-fixed top-0 start-0 w-100 h-100 d-flex flex-column gap-3 justify-content-center align-items-center"
        style="background: rgba(255,255,255,0.9); z-index: 1055;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="fw-bold text-primary fs-5">Loading Sale Data...</div>
    </div>
    <link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendors/select2/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        /* ================= ULTRA-COMPACT EXCEL-LIKE UI ================= */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .sales-table { min-width: 700px; }
        .sales-table td.large-col { min-width: 80px; width: 80px; padding: 0 !important; }
        .discount-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0;
            flex-wrap: nowrap;
        }
        .discount-wrapper .discount-value { width: 50px; min-width: 50px; font-size: 0.75rem; padding: 1px 3px; }
        .discount-wrapper .discount-plus { width: 18px; height: 18px; padding: 0; font-size: 11px; line-height: 1; }
        .discount-wrapper .discount-type { position: absolute; right: 0; top: 115%; width: 55px; font-size: 0.7rem; z-index: 30; }
        @media (max-width: 992px) { .main-container { max-width: 100%; } .sales-table { min-width: 700px; } .minw-350 { min-width: 100%; } }
        @media (max-width: 768px) { .header-text { font-size: 0.85rem; } .btn { padding: .2rem .4rem; } .minw-350 { width: 100%; } .form-control, .form-select { font-size: .75rem; } }
        @media (max-width: 576px) { .sales-table { min-width: 650px; } .discount-wrapper .discount-value { min-width: 70px; } }
    </style>
    <style>
        /* Premium Customer Card CSS - Refactored 2-row layout */
        .customer-card-premium {
            background-color: #111827 !important;
            border-radius: 10px !important;
            padding: 12px 16px !important;
            color: #f3f4f6 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            width: 100% !important;
        }

        .customer-card-premium .col-title {
            font-size: 0.65rem !important;
            text-transform: uppercase !important;
            font-weight: 700 !important;
            color: #9ca3af !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            margin-bottom: 2px !important;
        }

        .customer-card-premium .col-title i {
            margin-right: 2px !important;
        }

        .customer-card-premium .col-value {
            font-size: 0.88rem !important;
            font-weight: 700 !important;
            line-height: 1.2 !important;
        }

        .customer-card-premium .col-value-sub {
            font-size: 0.7rem !important;
            font-weight: 700 !important;
            margin-top: -2px !important;
        }

        /* Sleek Segmented Control for Radio Buttons */
        .toggle-button-group {
            background-color: #f1f5f9;
            border: 2px solid #cbd5e1;
            border-radius: 8px;
            padding: 2px;
            display: flex;
            height: 38px;
            align-items: center;
        }
        .toggle-button-group .btn-check {
            display: none;
        }
        .toggle-button-group .toggle-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            height: 100%;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 0 !important;
            padding: 0 !important;
        }
        .toggle-button-group .toggle-btn:hover {
            color: #1e293b;
            background-color: #e2e8f0;
        }
        .toggle-button-group .btn-check:checked + .toggle-btn {
            background-color: #2563eb;
            color: #ffffff !important;
        }
        .toggle-button-group .btn-check:checked + .toggle-btn:hover {
            background-color: #1d4ed8;
            color: #ffffff !important;
        }

        /* Specific styling for the customer selection Select2 to match standard input heights */
        #customerSelect + .select2-container--default .select2-selection--single {
            border: 2px solid #cbd5e1 !important;
            border-radius: 8px !important;
            height: 38px !important;
            padding: 0 12px !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            background-color: #ffffff !important;
            transition: all 0.2s ease-in-out !important;
            display: flex !important;
            align-items: center !important;
        }
        #customerSelect + .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px !important;
            padding-left: 0 !important;
            font-size: 0.85rem !important;
            color: #1e293b !important;
        }
        #customerSelect + .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px !important;
            top: 2px !important;
            right: 8px !important;
        }
        #customerSelect + .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15) !important;
        }

        /* 💎 ULTRA-COMPACT EXCEL-LIKE ERP THEME 💎 */
        body {
            background-color: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        /* Containers & Cards */
        .main-container {
            border: 1px solid #94a3b8 !important;
            border-radius: 4px !important;
            box-shadow: none !important;
            background-color: #ffffff !important;
            padding: 6px !important;
            font-size: .78rem;
            max-width: 100%;
        }
        
        .card-panel {
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 3px !important;
            padding: 6px !important;
            height: 100%;
        }
        
        .totals-card {
            background-color: #f1f5f9 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 3px !important;
            padding: 6px !important;
        }
        
        /* Section Titles */
        .section-title {
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 0.72rem !important;
            letter-spacing: 0.5px !important;
            color: #1e293b !important;
            margin-bottom: 4px !important;
            border-left: 3px solid #2563eb !important;
            padding-left: 6px !important;
        }
        
        /* Clean inputs - compact */
        .form-control,
        .form-select,
        .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 3px !important;
            padding: 2px 6px !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            background-color: #ffffff !important;
            transition: all 0.15s ease-in-out !important;
            height: auto !important;
            font-size: 0.78rem !important;
        }
        
        .form-control:focus,
        .form-select:focus,
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1) !important;
            outline: none !important;
        }
        
        /* Read-only fields */
        .input-readonly {
            background-color: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #475569 !important;
            font-weight: 600 !important;
            cursor: not-allowed !important;
        }
        
        /* Compact Buttons */
        .btn-action-primary {
            background-color: #2563eb !important;
            border: 1px solid #1d4ed8 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-radius: 3px !important;
            padding: 4px 12px !important;
            transition: all 0.15s;
            font-size: 0.78rem !important;
        }
        .btn-action-primary:hover {
            background-color: #1d4ed8 !important;
            color: #ffffff !important;
        }
        
        .btn-action-secondary {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #475569 !important;
            font-weight: 600 !important;
            border-radius: 3px !important;
            padding: 4px 12px !important;
            transition: all 0.15s;
            font-size: 0.78rem !important;
        }
        .btn-action-secondary:hover {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
        }
        
        /* Transaction Grid / Table */
        .table-responsive {
            border: 1px solid #cbd5e1 !important;
            border-radius: 2px !important;
            overflow-x: auto !important;
            overflow-y: visible !important;
            box-shadow: none !important;
            min-height: 100px;
            background-color: #ffffff;
        }
        
        .minw-350 {
            min-width: 280px;
            width: 280px;
            flex-shrink: 0;
        }

        .sales-table {
            border-collapse: collapse !important;
            margin-bottom: 0 !important;
            width: 100%;
            min-width: 900px;
        }
        
        .sales-table thead th {
            background-color: #e2e8f0 !important;
            color: #0f172a !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 10px !important;
            letter-spacing: 0.3px;
            padding: 3px 4px !important;
            border: 1px solid #94a3b8 !important;
            border-bottom: 2px solid #64748b !important;
            vertical-align: middle !important;
            text-align: center;
            white-space: nowrap;
        }

        .sales-table thead th.col-product {
            text-align: left !important;
            padding-left: 4px !important;
        }
        
        .sales-table tbody td {
            border: 1px solid #cbd5e1 !important;
            padding: 0 !important;
            background-color: #ffffff;
            vertical-align: middle !important;
        }

        /* ⚡ FLAT BORDERLESS GRID INPUTS - COMPACT ⚡ */
        .sales-table tbody .form-control,
        .sales-table tbody .form-select {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            height: 26px !important;
            margin: 0 !important;
            padding: 1px 4px !important;
            width: 100% !important;
            background-color: transparent !important;
            text-align: center;
            color: #1e293b !important;
            font-weight: 500 !important;
            font-size: 0.76rem !important;
        }

        .sales-table tbody td.col-product .form-select {
            text-align: left !important;
            padding-left: 12px !important;
        }

        .sales-table tbody .input-readonly,
        .sales-table tbody input[readonly],
        .sales-table tbody select[disabled] {
            background-color: #f1f5f9 !important;
            cursor: not-allowed !important;
            color: #475569 !important;
            font-weight: 600 !important;
        }

        .sales-table tbody .form-control:focus,
        .sales-table tbody .form-select:focus {
            outline: none !important;
            background-color: #eff6ff !important;
            box-shadow: inset 0 0 0 1px #2563eb !important;
        }

        /* Select2 Specific flat borderless styling */
        .sales-table tbody .select2-container--default .select2-selection--single {
            height: 26px !important;
            padding: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background-color: transparent !important;
            display: flex;
            align-items: center;
        }

        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            padding-left: 4px !important;
            padding-right: 16px !important;
            font-size: 0.76rem !important;
            color: #1e293b !important;
            font-weight: 500 !important;
            text-align: left !important;
        }

        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 26px !important;
            right: 4px !important;
        }

        /* Select2 Focus state */
        .sales-table tbody .select2-container--default.select2-container--focus .select2-selection--single {
            background-color: #eff6ff !important;
            box-shadow: inset 0 0 0 1px #2563eb !important;
        }

        /* Elegant flat block layout for discount input + toggle */
        .sales-table tbody .discount-wrapper {
            display: flex !important;
            align-items: stretch !important;
            width: 100% !important;
            height: 26px !important;
            gap: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .sales-table tbody .discount-wrapper .discount-value {
            flex-grow: 1 !important;
            border: none !important;
            border-radius: 0 !important;
            height: 100% !important;
            text-align: center;
            background-color: transparent !important;
            padding: 1px 3px !important;
        }

        .sales-table tbody .discount-wrapper .discount-toggle {
            border: none !important;
            border-radius: 0 !important;
            background-color: #e2e8f0 !important;
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 0.7rem !important;
            width: 24px !important;
            min-width: 24px !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            cursor: pointer !important;
        }

        .sales-table tbody .discount-wrapper .discount-toggle:hover {
            background-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
        
        .sales-table tfoot td {
            background-color: #e2e8f0 !important;
            border: 1px solid #94a3b8 !important;
            border-top: 2px solid #64748b !important;
            padding: 2px 4px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            font-size: 0.78rem !important;
        }
        
        /* Row hover */
        .sales-table tbody tr:hover td {
            background-color: #f8fafc !important;
        }
        
        /* Column Widths - Compact & Full Width */
        .col-product { width: auto; min-width: 280px; }
        .col-warehouse { min-width: 100px; }
        .col-stock { width: 70px; min-width: 70px; }
        .col-qty { width: 70px; min-width: 70px; }
        .col-price { width: 85px; min-width: 85px; }
        .col-disc { width: 90px; min-width: 90px; }
        .col-disc-amt { width: 80px; min-width: 80px; }
        .col-pieces { width: 70px; min-width: 70px; }
        .col-price-p { width: 85px; min-width: 85px; }
        .col-price-m2 { width: 85px; min-width: 85px; }
        .col-amount { width: 95px; min-width: 95px; }
        .col-action { width: 35px; min-width: 35px; text-align: center; }
        .col-size { width: 75px; min-width: 75px; }
        .col-color { width: 85px; min-width: 85px; }

        /* Invalid cells & inputs */
        .invalid-cell {
            background-color: #fff5f5 !important;
            border: 1px solid #ef4444 !important;
        }
        .invalid-select,
        .invalid-input {
            border-color: #ef4444 !important;
            box-shadow: none !important;
        }
        .badge-soft {
            background: #eef2ff;
            color: #3730a3;
            font-weight: 700;
        }

        /* Walk-in Customer Select Alignment */
        #customerInputWrapper .select2-container--default .select2-selection--single {
            height: 26px !important;
            min-height: 26px !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 3px !important;
            background-color: #ffffff !important;
        }
        #customerInputWrapper .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px !important;
            padding-left: 6px !important;
            font-size: 0.78rem !important;
            color: #1e293b !important;
            font-weight: 500 !important;
        }
        #customerInputWrapper .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 24px !important;
            top: 0 !important;
            right: 2px !important;
        }
        #customerInputWrapper .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1) !important;
        }
    </style>



    <div class="container-fluid py-0 px-1">
        <div class="main-container bg-white border mx-auto">

            <div id="alertBox" class="alert d-none mb-1" role="alert" style="padding:4px 8px; font-size:0.78rem;"></div>

            <form id="saleForm" autocomplete="off">
                @csrf
                <input type="hidden" id="booking_id" name="booking_id" value="">
                <input type="hidden" id="action" name="action" value="sale">

                {{-- HEADER - Compact --}}
                <div class="d-flex justify-content-between align-items-center px-2 py-1 border-bottom" style="min-height:28px;">
                    <small class="text-secondary" id="entryDateTime" style="font-size:0.72rem;">Entry Date_Time: --</small>
                    <div class="d-flex align-items-center gap-1">
                        <small class="text-secondary d-none" id="entryDate" style="font-size:0.72rem;">Date: --</small>
                        <button type="button" class="btn btn-sm btn-success py-0 px-2" id="btnHeaderPosted"
                            disabled style="font-size:0.72rem; height:22px; line-height:20px;">Sale</button>
                    </div>
                </div>

                <!-- HORIZONTAL TOP PANEL -->
                <div class="p-1 border bg-white mb-1" style="border-radius:3px;">
                    <div class="row g-1 align-items-end w-100 m-0">
                        <!-- Invoice No -->
                        <div class="col-sm-2">
                            <label class="form-label fw-bold text-secondary mb-0" style="font-size: 0.72rem;">Invoice No.</label>
                            <input type="text" class="form-control input-readonly" name="Invoice_no"
                                value="{{ $nextInvoiceNumber }}" readonly style="height: 26px !important;">
                        </div>
                        
                        <!-- Credit Days -->
                        <div class="col-sm-1">
                            <label class="form-label fw-bold text-secondary mb-0" style="font-size: 0.72rem;">Cr. Days</label>
                            <input type="number" class="form-control" name="credit_days" placeholder="0"
                                min="0" value="{{ $sale->credit_days ?? '' }}" style="height: 26px !important; padding: 0 4px;">
                        </div>
                        
                        <!-- Date -->
                        <div class="col-sm-2">
                            <label class="form-label fw-bold text-secondary mb-0" style="font-size: 0.72rem;">Date:</label>
                            <input type="text" name="sale_date" class="form-control datepicker-custom" id="displayDateInput" value="{{ date('Y-m-d') }}" style="background-color: #ffffff; height: 26px !important; padding: 0 4px;">
                        </div>
                        
                        <!-- M.Bill -->
                        <div class="col-sm-2">
                            <label class="form-label fw-bold text-secondary mb-0" style="font-size: 0.72rem;">M.Bill:</label>
                            <input type="text" class="form-control" name="reference" id="remarks" placeholder="Remarks" style="height: 26px !important; padding: 0 4px;">
                        </div>
                        
                        <!-- Customer & Walkin Toggle -->
                        <div class="col-sm-5">
                            <div class="d-flex justify-content-between align-items-center mb-0">
                                <label class="form-label fw-bold text-secondary mb-0" style="font-size: 0.72rem;">Customer:</label>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-success py-0 px-1" data-bs-toggle="modal" data-bs-target="#addCustomerModal" title="Add New Customer" style="font-size: 0.68rem; height: 18px; line-height: 16px;">
                                        <i class="fas fa-plus"></i> New
                                    </button>
                                    <div class="form-check form-switch mb-0 d-flex align-items-center" style="min-height: 0; padding-left: 2.5em;">
                                        <input class="form-check-input" type="checkbox" role="switch" id="walkinToggle" name="is_walkin" value="1" checked style="height: 14px; width: 28px; margin-top:0;">
                                        <label class="form-check-label fw-bold ms-1" for="walkinToggle" style="color: #6366f1; font-size: 0.72rem; cursor: pointer;">Walk-in</label>
                                    </div>
                                </div>
                            </div>
                            <!-- Input Morph Container -->
                            <div id="customerInputWrapper" style="height: 26px;">
                                <!-- Walk-in Input -->
                                <input type="text" class="form-control" name="walkin_name" id="walkinNameInput" value="Walk-in Customer" placeholder="Enter Name..." style="height: 26px !important;">
                                <!-- Main Customer Select2 -->
                                <select class="form-select d-none" id="customerSelect" name="customer" style="width:100%">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Hidden fields for backend --}}
                <input type="hidden" id="address" name="address">
                <input type="hidden" id="tel" name="tel">
                <input type="hidden" id="previousBalance" value="0">
                <input type="hidden" id="rangeBalance" value="0">

                <!-- Items Section full width -->
                <div class="p-1 border bg-white mt-1" style="border-radius:3px;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="section-title mb-0" style="font-size:0.7rem;">Items</div>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-outline-success py-0 px-1" data-bs-toggle="modal" data-bs-target="#quickAddProductModal" style="font-size:0.7rem; height:20px; line-height:18px;">
                                <i class="fas fa-plus me-1"></i>Quick Add
                            </button>
                            <button type="button" class="btn btn-primary py-0 px-1" id="btnAdd" style="font-size:0.7rem; height:20px; line-height:18px;">+Row</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered sales-table mb-0">
                            <thead>
                                <tr>
                                    <th class="col-product">Product</th>
                                    <th class="col-stock">Stock</th>
                                    <th style="width:70px;min-width:70px;">Qty</th>
                                    <th style="width:70px;min-width:70px;" class="d-none">Loose</th>
                                    <th class="col-size">Size</th>
                                    <th class="col-color">Color</th>
                                    <th class="col-pieces boxes-col">Pcs</th>
                                    <th class="col-price-p price-pc-header">Price</th>
                                    <th class="col-disc">Disc</th>
                                    <th class="col-disc-amt">D.Amt</th>
                                    <th class="col-amount">Amount</th>
                                    <th class="col-action">×</th>
                                </tr>
                            </thead>
                            <tbody id="salesTableBody">

                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="9" class="text-end fw-bold" style="font-size:0.76rem;">Total:</td>
                                    <td class="text-end fw-bold" style="font-size:0.76rem;"><span id="totalAmount">0.00</span></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Totals + Receipts --}}
                <div class="row g-2 mt-2 align-items-stretch">
                    <!-- LEFT SIDE: Receipt Vouchers -->
                    <div class="col-lg-7" id="receiptVouchersSection">
                        <div class="card border-0 shadow-sm h-100 rounded-3">
                            <div class="card-header bg-light border-bottom-0 py-2 d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-secondary text-uppercase" style="font-size:0.7rem; letter-spacing:0.5px;">Receipt Vouchers</span>
                                <span class="badge bg-primary rounded-pill" style="font-size:0.75rem;">Total: <span id="receiptsTotalBadge">0.00</span></span>
                            </div>
                            <div class="card-body p-2 bg-white">
                                <div id="rvWrapper">
                                    <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                                        <select class="form-select form-select-sm rv-account bg-light" name="receipt_account_id[]" style="max-width: 280px; border-radius: 4px;">
                                            <option value="" disabled>Select account</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title), 'cash') ? 'selected' : '' }}>{{ $acc->title }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" step="0.01" class="form-control form-control-sm text-end rv-amount fw-bold" name="receipt_amount[]" placeholder="0.00" style="max-width:140px; border-radius: 4px;">
                                        <button type="button" class="btn btn-primary btn-sm px-2 rounded-2 shadow-sm" id="btnAddRV"><i class="fas fa-plus"></i> Add</button>
                                    </div>
                                    <div class="text-end d-none">
                                        <span class="me-2">Receipts Total:</span>
                                        <span class="fw-bold" id="receiptsTotal">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT SIDE: Totals -->
                    <div class="col-lg-5" id="totalsSection">
                        <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                            <!-- Walk-in View -->
                            <div id="totalsWalkinView" class="h-100 d-none align-items-center bg-white justify-content-between p-2 gap-2 flex-nowrap" style="overflow-x: auto;">
                                <!-- Net Total -->
                                <div class="d-flex align-items-center bg-light px-2 py-1 border rounded gap-1 flex-shrink-0">
                                    <span class="fw-bold text-secondary" style="font-size:0.78rem;">Net Total:</span>
                                    <span id="walkinNetTotal" class="fw-bold text-primary mb-0" style="font-size:1rem; line-height:1;">0.00</span>
                                </div>
                                
                                <!-- Discount -->
                                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                    <span class="text-muted fw-semibold" style="font-size:0.78rem;">Disc (Rs):</span>
                                    <input type="number" class="form-control form-control-sm text-end fw-bold text-danger border-danger" id="walkinDiscountRs" value="0" placeholder="0" style="width: 80px; background-color: #fef2f2; height: 26px !important; padding: 1px 4px;">
                                </div>
                                
                                <!-- Payments -->
                                <div class="d-flex align-items-center gap-1 px-2 border-start border-end" style="min-width: 320px;">
                                    <span class="text-muted fw-semibold mb-0 flex-shrink-0" style="font-size:0.78rem;">Payments:</span>
                                    <div id="walkinReceiptsContainer" class="flex-grow-1 w-100"></div>
                                </div>
                                
                                <!-- Change -->
                                <div class="d-flex align-items-center gap-1 px-2 flex-shrink-0">
                                    <span class="fw-bold text-uppercase text-secondary" style="font-size:0.78rem;">Change:</span>
                                    <span id="walkinChange" class="fw-bold text-warning mb-0" style="font-size:1.1rem; line-height:1;">0.00</span>
                                </div>
                            </div>

                            <!-- Customer View -->
                            <div id="totalsCustomerView" class="h-100 flex-column bg-light">
                                <div class="p-2 d-flex flex-column gap-1" style="font-size:0.8rem;">
                                    <div class="d-flex justify-content-between px-2">
                                        <span class="text-muted">Total Qty</span>
                                        <span class="fw-semibold text-dark" id="tQty">0</span>
                                    </div>
                                    <div class="d-flex justify-content-between px-2">
                                        <span class="text-muted">Invoice Gross</span>
                                        <span class="fw-semibold text-dark" id="tGross">0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between px-2">
                                        <span class="text-muted">Line Discount</span>
                                        <span class="fw-semibold text-danger" id="tLineDisc">0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between px-2 py-1 bg-white border rounded shadow-sm my-1">
                                        <span class="fw-bold">Sub-Total</span>
                                        <span class="fw-bold text-primary" id="tSub">0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center px-2">
                                        <span class="text-muted">Add. Discount %</span>
                                        <input type="number" class="form-control form-control-sm text-end p-1" id="discountPercent" value="0" style="width:80px; height:24px;">
                                    </div>
                                    <div class="d-flex justify-content-between px-2">
                                        <span class="text-muted">Add. Discount Rs</span>
                                        <span class="fw-semibold text-danger" id="tOrderDisc">0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between px-2 py-1 bg-white border border-primary border-opacity-25 rounded shadow-sm my-1">
                                        <span class="fw-bold text-primary">Current Bill</span>
                                        <span class="fw-bold text-primary" id="tCurrentBill">0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between px-2">
                                        <span class="text-muted">Previous Balance</span>
                                        <span class="fw-semibold text-danger" id="tPrev">0.00</span>
                                    </div>
                                </div>
                                <div class="mt-auto p-2 bg-dark text-white d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-uppercase" style="font-size:0.75rem; letter-spacing:1px;">Payable / Total</span>
                                    <span class="fs-5 fw-bold" id="tPayable">0.00</span>
                                </div>
                            </div>

                            {{-- hidden mirrors for backend --}}
                            <input type="hidden" name="subTotal1" id="subTotal1" value="0">
                            <input type="hidden" name="total_subtotal" id="subTotal2" value="0">
                            <input type="hidden" name="total_extra_cost" id="discountAmount" value="0">
                            <input type="hidden" name="total_net" id="totalBalance" value="0">
                            <input type="hidden" name="cash" value="0">
                            <input type="hidden" name="card" value="0">
                            <input type="hidden" name="change" id="backendChange" value="0">
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="d-flex flex-wrap gap-1 justify-content-center py-1 px-2 mt-1 border-top">
                    <button type="button" class="btn btn-action-primary bg-primary border-primary" id="btnSave"><i class="fas fa-bookmark me-1"></i>Booking</button>
                    <button type="button" class="btn btn-action-primary bg-success border-success" id="btnPosted" disabled><i class="fas fa-check-circle me-1"></i>Sale</button>
                    <button type="button" class="btn btn-action-secondary" id="btnPrint"><i class="fas fa-print me-1"></i>A4</button>
                    <button type="button" class="btn btn-action-secondary" id="btnEstimate"><i class="fas fa-file-invoice me-1"></i>Est</button>
                    <button type="button" class="btn btn-action-secondary" id="btnPrint2"><i class="fas fa-receipt me-1"></i>Thermal</button>
                    <button type="button" class="btn btn-action-secondary" id="btnDcThermal"><i class="fas fa-truck me-1"></i>DC</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">
                        <i class="fas fa-user-plus text-primary me-2"></i>New Customer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="ajaxAddCustomerForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Customer Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="customer_type" required>
                                    <option value="Main Customer">Main Customer</option>
                                    <option value="Walking Customer">Walking Customer</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_name" required placeholder="Customer Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mobile</label>
                                <input type="text" class="form-control" name="mobile" placeholder="0300-1234567">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Opening Balance</label>
                                <input type="number" step="0.01" class="form-control" name="opening_balance" value="0">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Address</label>
                                <input type="text" class="form-control" name="address" placeholder="Address">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnSaveAjaxCustomer">Save Customer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== QUICK ADD PRODUCT MODAL ===== --}}
<!-- <div class="modal fade" id="quickAddProductModal" tabindex="-1" aria-labelledby="quickAddProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 pb-2">
                <h5 class="modal-title fw-bold" id="quickAddProductModalLabel">
                    <i class="fa fa-plus-circle text-primary me-2"></i>Quick Add Product
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddProductForm">
                @csrf
                <div class="modal-body pt-2">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="product_name" required placeholder="Enter product name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="qap_category" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Sub Category</label>
                            <select class="form-select" name="sub_category_id" id="qap_subcategory">
                                <option value="">Select Sub Category</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Brand <span class="text-danger">*</span></label>
                            <select class="form-select" name="brand_id" id="qap_brand" required>
                                <option value="">Select Brand</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Model / Series</label>
                            <input type="text" class="form-control" name="model" placeholder="Optional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Size Mode <span class="text-danger">*</span></label>
                            <select class="form-select" name="size_mode" id="qap_size_mode" required>
                                <option value="by_cartons" selected>By Cartons</option>
                                <option value="by_pieces">By Pieces</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="qap_ppb_wrap">
                            <label class="form-label fw-bold small text-muted">Pieces Per Box</label>
                            <input type="number" class="form-control" name="pieces_per_box" id="qap_ppb" value="1" min="1" placeholder="e.g. 12">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Low Stock (Cartons)</label>
                            <input type="number" class="form-control" name="alert_carton_quantity" min="0" placeholder="e.g. 5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Purchase Price /pc</label>
                            <input type="number" step="0.01" class="form-control" name="purchase_price_per_piece" value="0" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Sale Price /pc</label>
                            <input type="number" step="0.01" class="form-control" name="sale_price_per_box" value="0" placeholder="0.00">
                        </div>
                    </div>
                    {{-- Hidden defaults for validation --}}
                    <input type="hidden" name="boxes_quantity" value="0">
                    <input type="hidden" name="loose_pieces" value="0">
                    <input type="hidden" name="piece_quantity" value="0">
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnQuickSaveProduct">
                        <i class="fa fa-save me-1"></i>Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle pieces_per_box field based on size mode
    $('#qap_size_mode').on('change', function() {
        if ($(this).val() === 'by_pieces') {
            $('#qap_ppb_wrap').hide();
            $('#qap_ppb').val(1);
        } else {
            $('#qap_ppb_wrap').show();
        }
    });

    // Load categories, brands, and subcategories immediately
    var $catSelect = $('#qap_category');
    var $brandSelect = $('#qap_brand');
    var $subCatSelect = $('#qap_subcategory');

    // Load categories if empty
    if ($catSelect.find('option').length <= 1) {
        $.get("{{ url('/get-categories') }}", function(data) {
            (data || []).forEach(function(cat) {
                $catSelect.append('<option value="'+ cat.id +'">'+ cat.name +'</option>');
            });
        }).fail(function() {
            console.error('Failed to load categories');
        });
    }

    // Load brands if empty
    if ($brandSelect.find('option').length <= 1) {
        $.get("{{ url('/get-brands') }}", function(data) {
            (data || []).forEach(function(brand) {
                $brandSelect.append('<option value="'+ brand.id +'">'+ brand.name +'</option>');
            });
        }).fail(function() {
            console.error('Failed to load brands');
        });
    }

    // Load all subcategories initially if empty
    if ($subCatSelect.find('option').length <= 1) {
        $.get("{{ url('/get-all-subcategories') }}", function(data) {
            (data || []).forEach(function(sub) {
                $subCatSelect.append('<option value="'+ sub.id +'">'+ sub.name +'</option>');
            });
        }).fail(function() {
            console.error('Failed to load subcategories');
        });
    }

    // Load subcategories when category changes
    $('#qap_category').on('change', function() {
        var categoryId = $(this).val();
        var $subCatSelect = $('#qap_subcategory');
        $subCatSelect.html('<option value="">Select Sub Category</option>');
        
        if (categoryId) {
            $.get("{{ url('/get-subcategories') }}/" + categoryId, function(data) {
                (data || []).forEach(function(sub) {
                    $subCatSelect.append('<option value="'+ sub.id +'">'+ sub.name +'</option>');
                });
            });
        }
    });

    // Submit Quick Add Product
    $('#quickAddProductForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnQuickSaveProduct');
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: "{{ route('store-product') }}",
            method: "POST",
            data: $(this).serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function(response) {
                $btn.prop('disabled', false).html(originalHtml);
                $('#quickAddProductForm')[0].reset();

                // Close modal
                var modal = bootstrap.Modal.getInstance(document.getElementById('quickAddProductModal'));
                if (modal) modal.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Product Added!',
                    text: response.message || 'Product created successfully. You can now search for it.',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalHtml);
                var msg = 'Error adding product.';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Error', msg, 'error');
            }
        });
    });
});
</script> -->

    <!-- {{-- Quick Add Product Modal --}}
    @include('admin_panel.partials.quick_add_product_modal') -->

   <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/bootstrap5/js/bootstrap.bundle.min.js') }}"></script>
  {{-- Quick Add Product Modal --}}
    @include('admin_panel.partials.quick_add_product_modal')
    {{-- sjadlfksal --}}

    <script>
        /* ========== DISCOUNT TOGGLE (% ↔ PKR) ========== */

        // $(document).on('click', '.discount-toggle', function () {

        //     const $btn = $(this);
        //     const currentType = $btn.data('type');

        //     if (currentType === 'percent') {
        //         // switch to PKR
        //         $btn.data('type', 'pkr');
        //         $btn.text('PKR');
        //     } else {
        //         // switch to %
        //         $btn.data('type', 'percent');
        //         $btn.text('%');
        //     }

        //     // focus back to input
        //     $btn.closest('.discount-wrapper')
        //         .find('.discount-value')
        //         .focus();
        // });
    </script>














    {{-- hajshdsadsdsksa --}}

    {{-- Shared Logic for Sales (Add/Edit) --}}
@endsection

@section('js')
    @include('admin_panel.sale.scripts.shared_logic')

    <script>
        $(document).ready(function() {
            // --- Initial Setup ---
            if ($('#salesTableBody tr').length === 0) {
                addNewRow();
            }
            updateGrandTotals();
            refreshPostedState();

            // --- Check if URL is for Booking Flow ---
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('type') === 'booking') {
                $('.header-text').html('<i class="fas fa-bookmark text-primary me-2"></i>Add Booking');
                $('#action').val('booking');
                $('#btnPosted').addClass('d-none');
                $('#btnHeaderPosted').addClass('d-none');
            }

            // ============================================================
            // CUSTOMER SELECT2 AJAX SEARCH (Name or Code)
            // ============================================================
            function getPartyType() {
                return $('input[name="partyType"]:checked').val() || 'Main Customer';
            }

            $('#customerSelect').select2({
                placeholder: 'Search by Name or Code...',
                allowClear: true,
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route('salecustomers.index') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            type: getPartyType(),
                            search: params.term || ''
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(c) {
                                return {
                                    id: c.id,
                                    text: (c.customer_id || '') + ' — ' + c.customer_name,
                                    customer: c
                                };
                            })
                        };
                    },
                    cache: false
                },
                templateResult: function(item) {
                    if (item.loading) return item.text;
                    if (!item.customer) return item.text;
                    const c = item.customer;
                    return $(`<div>
                        <strong>${c.customer_name}</strong>
                        <small class="text-muted ms-2">${c.customer_id || ''}</small>
                        ${c.mobile ? '<br><small class="text-muted">' + c.mobile + '</small>' : ''}
                    </div>`);
                },
                templateSelection: function(item) {
                    if (!item.customer) return item.text;
                    return item.customer.customer_id + ' — ' + item.customer.customer_name;
                }
            });

            // Set initial visibility state of Customer Select / Walk-in input
            $('#walkinToggle').trigger('change');

            // Party type change → reset customer
            $(document).on('change', 'input[name="partyType"]', function() {
                $('#customerSelect').val(null).trigger('change');
                clearCustomerInfo();
            });

            // Customer selected → load details
            $('#customerSelect').on('select2:select', function(e) {
                const id = e.params.data.id;
                if (!id) return;

                $.get("{{ url('sale/customers') }}/" + id + "?t=" + new Date().getTime(), function(d) {
                    // Fill hidden fields
                    $('#address').val(d.address || '');
                    $('#tel').val(d.mobile || '');
                    const prev = parseFloat(d.previous_balance || 0);
                    const range = parseFloat(d.balance_range || 0);
                    $('#previousBalance').val(prev.toFixed(2));
                    $('#rangeBalance').val(range.toFixed(2));

                    // Fill info card
                    $('#ci_code').text(d.customer_id || '—');
                    $('#ci_name').text(d.customer_name || '—');
                    $('#ci_mobile').text(d.mobile || '—');
                    $('#ci_address').text(d.address || '—');
                    $('#ci_prev_bal').text(prev.toFixed(2));
                    $('#ci_range_bal').text(range.toFixed(2));
                    $('#customerInfoCard').removeClass('d-none');

                    // Auto-fill Sales Officer if customer has one
                    if (d.sales_officer_id) {
                        $('#salesOfficerSelect').val(d.sales_officer_id);
                    }

                    if (typeof updateGrandTotals === 'function') updateGrandTotals();
                }).fail(function() {
                    showAlert('error', 'Failed to load customer details');
                });
            });

            // Customer cleared
            $('#customerSelect').on('select2:clear', function() {
                clearCustomerInfo();
                if (typeof updateGrandTotals === 'function') updateGrandTotals();
            });

            function clearCustomerInfo() {
                $('#address, #tel').val('');
                $('#previousBalance, #rangeBalance').val('0');
                $('#ci_code, #ci_name, #ci_mobile, #ci_address').text('—');
                $('#ci_prev_bal, #ci_range_bal').text('0.00');
                $('#customerInfoCard').addClass('d-none');
                $('#salesOfficerSelect').val('');
            }

            $('#clearCustomerData').on('click', function() {
                $('#customerSelect').val(null).trigger('change');
                clearCustomerInfo();
                if (typeof updateGrandTotals === 'function') updateGrandTotals();
            });

            $('#btnPrint').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/invoice', '_blank'));
            });
            $('#btnEstimate').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/invoice?type=estimate', '_blank'));
            });
            $('#btnPrint2').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/recepit', '_blank'));
            });
            $('#btnDcThermal').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/dc-thermal', '_blank'));
            });

            // AJAX Customer Submit
            $('#btnSaveAjaxCustomer').on('click', function() {
                let form = $('#ajaxAddCustomerForm');
                if (!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }
                
                let btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                
                $.ajax({
                    url: '{{ route('customers.store') }}',
                    type: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).text('Save Customer');
                        if (res.success) {
                            $('#addCustomerModal').modal('hide');
                            form[0].reset();
                            
                            // Make sure UI toggles map to the new customer's type
                            if (res.customer.customer_type === 'Walking Customer') {
                                $('#typeWalkin').prop('checked', true).trigger('change');
                            } else {
                                $('#typeCustomers').prop('checked', true).trigger('change');
                            }
                            
                            // Auto select new customer
                            let newOption = new Option(res.customer.customer_id + ' — ' + res.customer.customer_name, res.customer.id, true, true);
                            $('#customerSelect').append(newOption).trigger('change');
                            
                            // trigger select2 API selection to load customer details like Prev Bal
                            $('#customerSelect').trigger({
                                type: 'select2:select',
                                params: {
                                    data: {
                                        id: res.customer.id,
                                        text: res.customer.customer_id + ' — ' + res.customer.customer_name
                                    }
                                }
                            });
                            
                            showAlert('success', 'Customer added successfully!');
                        }
                    },
                    error: function(err) {
                        btn.prop('disabled', false).text('Save Customer');
                        showAlert('error', 'Error adding customer. Check inputs.');
                    }
                });
            });
        });
    </script>
@endsection
