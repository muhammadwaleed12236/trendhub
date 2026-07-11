@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Force main template to be static and scroll-free on viewport */
    html, body {
        overflow: hidden !important;
        height: 100% !important;
        background-color: #f3f4f6;
    }
    
    .page-container, .main-panel, .content-wrapper, .main-content-inner, .main-content {
        height: calc(100vh - 60px) !important;
        overflow: hidden !important;
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }
    
    footer {
        display: none !important;
    }
    
    /* POS LAYOUT WRAPPER */
    .pos-wrapper {
        display: flex;
        gap: 20px;
        height: calc(100vh - 165px);
        margin-top: 10px;
        overflow: hidden;
    }
    
    /* LEFT PANEL: PRODUCTS GRID */
    .pos-products-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        padding: 20px;
        overflow: hidden;
        height: 100%;
    }
    
    .pos-search-box {
        position: relative;
        margin-bottom: 15px;
    }
    
    .pos-search-box input {
        width: 100%;
        padding: 12px 40px 12px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        background-color: #f9fafb;
        transition: all 0.3s ease;
    }
    
    .pos-search-box input:focus {
        background-color: #ffffff;
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        outline: none;
    }
    
    .pos-search-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 16px;
    }
    
    .pos-grid-container {
        flex: 1;
        overflow-y: auto;
        padding-right: 5px;
    }
    
    .pos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 12px;
    }
    
    /* PRODUCT CARD STYLING */
    .product-card {
        background: #ffffff;
        border: 1px solid #f3f4f6;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        position: relative;
    }
    
    .product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.05);
        border-color: #4f46e5;
    }
    
    .product-card-image {
        height: 85px;
        width: 100%;
        background-color: #f9fafb;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-bottom: 1px solid #f3f4f6;
        position: relative;
    }
    
    .product-card-image img {
        height: 100%;
        width: 100%;
        object-fit: cover;
    }
    
    .product-card-image i {
        font-size: 32px;
        color: #cbd5e1;
    }
    
    .product-card-body {
        padding: 8px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    
    .product-card-title {
        font-size: 11.5px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 4px;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 30px;
    }
    
    .product-card-variants-badge {
        font-size: 9.5px;
        font-weight: 700;
        color: #4f46e5;
        background-color: #eeebff;
        padding: 1px 6px;
        border-radius: 99px;
        align-self: flex-start;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .product-card-footer {
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .product-card-price {
        font-size: 12.5px;
        font-weight: 800;
        color: #111827;
    }
    
    .product-card-stock {
        font-size: 9.5px;
        font-weight: 700;
        color: #10b981;
    }
    
    .product-card-stock.out {
        color: #ef4444;
    }
    
    /* RIGHT PANEL: CART & CHECKOUT */
    .pos-cart-panel {
        width: 400px;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        overflow: hidden;
        height: 100%;
        min-height: 0;
    }
    
    .pos-cart-header {
        padding: 10px 15px;
        background-color: #1f2937;
        color: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .pos-cart-header h5 {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .pos-cart-header .cart-count {
        background-color: #4f46e5;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 99px;
    }
    
    /* CART ITEMS LIST */
    .pos-cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 10px;
        background-color: #f9fafb;
    }
    
    .cart-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        min-height: 100px;
        color: #9ca3af;
        padding: 15px 0;
    }
    
    .cart-empty i {
        font-size: 32px;
        margin-bottom: 8px;
        color: #d1d5db;
    }
    
    .cart-item {
        background: #ffffff;
        border-radius: 10px;
        padding: 6px 10px;
        margin-bottom: 6px;
        border: 1px solid #f3f4f6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.01);
        display: flex;
        flex-direction: column;
        position: relative;
    }
    
    .cart-item-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 3px;
    }
    
    .cart-item-name {
        font-size: 12.5px;
        font-weight: 700;
        color: #1f2937;
        max-width: 85%;
        line-height: 1.2;
    }
    
    .cart-item-remove {
        color: #9ca3af;
        cursor: pointer;
        font-size: 14px;
        transition: color 0.2s;
    }
    
    .cart-item-remove:hover {
        color: #ef4444;
    }
    
    .cart-item-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 4px;
    }
    
    /* QUANTITY SELECTORS */
    .qty-controls {
        display: flex;
        align-items: center;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        overflow: hidden;
        background: #ffffff;
    }
    
    .qty-btn {
        border: none;
        background: #f9fafb;
        color: #4b5563;
        width: 28px;
        height: 28px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    
    .qty-btn:hover {
        background: #e5e7eb;
    }
    
    .qty-input {
        border: none;
        border-left: 1px solid #e5e7eb;
        border-right: 1px solid #e5e7eb;
        width: 36px;
        height: 28px;
        text-align: center;
        font-size: 13px;
        font-weight: 700;
    }
    
    .qty-input:focus {
        outline: none;
    }
    
    .cart-item-price {
        font-size: 13.5px;
        font-weight: 800;
        color: #111827;
    }
    
    /* CART BILL SUMMARY */
    .pos-cart-summary {
        background-color: #ffffff;
        border-top: 1px solid #e5e7eb;
        padding: 8px 15px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 12.5px;
        margin-bottom: 4px;
        font-weight: 500;
        color: #4b5563;
    }
    
    .summary-row.payable {
        border-top: 1px dashed #cbd5e1;
        padding-top: 4px;
        margin-top: 4px;
        font-size: 15px;
        font-weight: 800;
        color: #111827;
    }
    
    /* CUSTOMER & PAYMENT FIELDS */
    .pos-checkout-section {
        background-color: #ffffff;
        border-top: 1px solid #e5e7eb;
        padding: 8px 15px;
    }
    
    .form-group {
        margin-bottom: 4px;
    }
    
    .form-group label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 2px;
        display: block;
    }
    
    .pos-input {
        width: 100%;
        padding: 4px 8px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        height: 30px;
    }
    
    .pos-input:focus {
        border-color: #4f46e5;
        outline: none;
    }
    
    .btn-checkout {
        width: 100%;
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 8px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        transition: all 0.2s;
        margin-top: 4px;
    }
    
    .btn-checkout:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
    }
    
    .btn-checkout:disabled {
        background: #cbd5e1;
        box-shadow: none;
        cursor: not-allowed;
        transform: none;
    }
    
    .customer-toggle-btns {
        display: flex;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 4px;
    }
    
    .toggle-btn {
        flex: 1;
        border: none;
        background: #f9fafb;
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
    }
    
    .toggle-btn.active {
        background: #1f2937;
        color: #ffffff;
    }
    
    /* MODAL STYLE OVERRIDES */
    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .variant-add-btn {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 5px 12px;
        border-radius: 6px;
    }
</style>

<div class="main-content">
    <div class="container-fluid p-2">
        <div class="pos-wrapper">
            
            <!-- LEFT PANEL: Products Grid -->
            <div class="pos-products-panel">
                <div class="pos-search-box">
                    <input type="text" id="posSearch" placeholder="Search base product by name or code...">
                    <i class="fas fa-search pos-search-icon"></i>
                </div>
                
                <div class="pos-grid-container">
                    <div class="pos-grid" id="productsGrid">
                        @foreach ($posProducts as $product)
                            @php
                                $hasVariants = count($product['variants']) > 0;
                                $isOut = $product['stock_pieces'] <= 0;
                            @endphp
                            <div class="product-card" 
                                 data-id="{{ $product['id'] }}" 
                                 data-name="{{ $product['name'] }}" 
                                 data-sku="{{ $product['sku'] }}" 
                                 data-price="{{ $product['price'] }}"
                                 data-stock-pieces="{{ $product['stock_pieces'] }}"
                                 data-stock="{{ $product['stock'] }}"
                                 data-pieces-per-box="{{ $product['pieces_per_box'] }}"
                                 data-size-mode="{{ $product['size_mode'] }}"
                                 data-has-variants="{{ $hasVariants ? '1' : '0' }}"
                                 data-variants="{{ json_encode($product['variants']) }}">
                                 
                                <div class="product-card-image">
                                    @if ($product['image'])
                                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}">
                                    @else
                                        <i class="fas fa-box-open"></i>
                                    @endif
                                </div>
                                <div class="product-card-body">
                                    <div class="product-card-title">{{ $product['name'] }}</div>
                                    @if ($hasVariants)
                                        <div class="product-card-variants-badge">
                                            {{ count($product['variants']) }} Variants
                                        </div>
                                    @else
                                        <div style="height: 24px;"></div>
                                    @endif
                                    <div class="product-card-footer">
                                        <div class="product-card-price">
                                            @if ($hasVariants)
                                                Rs {{ number_format($product['variants'][0]['price'] ?? $product['price'], 0) }}
                                            @else
                                                Rs {{ number_format($product['price'], 0) }}
                                            @endif
                                        </div>
                                        <div class="product-card-stock {{ $isOut ? 'out' : '' }}">
                                            Stock: {{ $product['stock'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- RIGHT PANEL: Cart & Checkout -->
            <div class="pos-cart-panel">
                <form id="posCheckoutForm" autocomplete="off" style="height: 100%; display: flex; flex-direction: column; margin-bottom: 0; min-height: 0; overflow: hidden;">
                    @csrf
                    <!-- Store action mirror -->
                    <input type="hidden" name="action" value="post">
                    
                    <div class="pos-cart-header">
                        <h5>Selected Items</h5>
                        <span class="cart-count" id="cartCountBadge">0</span>
                    </div>
                    
                    <!-- CART LIST -->
                    <div class="pos-cart-items" id="cartItemsList">
                        <div class="cart-empty" id="cartEmptyPlaceholder">
                            <i class="fas fa-shopping-cart"></i>
                            <div class="fw-bold">Cart is Empty</div>
                            <small class="text-muted mt-1">Click on any product card to add it</small>
                        </div>
                        <!-- Items populated via JavaScript -->
                    </div>
                    
                    <!-- BILL SUMMARY -->
                    <div class="pos-cart-summary">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="summarySubtotal">Rs 0.00</span>
                        </div>
                        <div class="summary-row align-items-center">
                            <span>Discount (Rs):</span>
                            <input type="number" name="total_extra_cost" id="summaryDiscount" class="form-control form-control-sm text-end" value="0" style="width: 100px; height: 26px !important; padding: 2px 8px;">
                        </div>
                        <div class="summary-row payable">
                            <span>Total Payable:</span>
                            <span id="summaryPayable">Rs 0.00</span>
                        </div>
                    </div>
                    
                    <!-- CHECKOUT SECTION -->
                    <div class="pos-checkout-section">
                        <!-- Customer Toggle -->
                        <div class="customer-toggle-btns">
                            <button type="button" class="toggle-btn active" id="btnToggleWalkin">Walk-in</button>
                            <button type="button" class="toggle-btn" id="btnToggleRegistered">Registered</button>
                        </div>
                        <input type="hidden" name="is_walkin" id="isWalkinInput" value="1">
                        
                        <div class="row g-2">
                            <!-- Customer Selection -->
                            <div class="col-6">
                                <div class="form-group" id="walkinNameGroup">
                                    <label>Customer Name</label>
                                    <input type="text" name="walkin_name" id="walkinNameInput" class="pos-input" placeholder="Name" value="Walking Customer">
                                </div>
                                <div class="form-group d-none" id="registeredCustomerGroup">
                                    <label>Customer</label>
                                    <select class="form-select select2" name="customer" id="customerSelect" style="width: 100%;">
                                        <option value="" selected disabled>Select</option>
                                        @foreach ($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Payment Account -->
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Payment Account</label>
                                    <select class="form-select pos-input" name="receipt_account_id[]" required style="height: 30px; padding: 4px 8px; font-size: 12px;">
                                        @foreach ($accounts as $acc)
                                            <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title), 'cash') ? 'selected' : '' }}>{{ $acc->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cash Received & Change -->
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Received Cash</label>
                                    <input type="number" name="receipt_amount[]" id="receivedCash" class="pos-input text-end fw-bold" placeholder="0.00" value="" style="height: 30px; padding: 4px 8px;">
                                    <input type="hidden" name="cash" id="backendCash">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Change</label>
                                    <input type="text" id="returnedChange" class="pos-input text-end fw-bold bg-light" readonly value="0.00" style="height: 30px; padding: 4px 8px;">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden values for backend submission -->
                        <input type="hidden" name="subTotal1" id="backendSubTotal1" value="0">
                        <input type="hidden" name="total_subtotal" id="backendSubTotal2" value="0">
                        <input type="hidden" name="total_net" id="backendTotalNet" value="0">
                        <input type="hidden" name="warehouse_id[]" id="backendWarehouseId" value="1">
                        
                        <button type="submit" class="btn-checkout" id="btnPOSSubmit" disabled>
                            <i class="fas fa-check-circle me-1"></i> Submit & Print
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</div>

<!-- VARIANTS SELECTION POPUP MODAL -->
<div class="modal fade" id="variantsModal" tabindex="-1" aria-labelledby="variantsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title fs-6" id="variantsModalLabel">Select Product Variant</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="border: none; background: transparent; font-size: 24px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="font-size: 12px;">Variant Size / Color</th>
                                <th class="text-center" style="font-size: 12px;">Stock</th>
                                <th class="text-end" style="font-size: 12px;">Price</th>
                                <th class="text-center" style="width: 140px; font-size: 12px;">Quantity</th>
                                <th class="text-center" style="width: 120px; font-size: 12px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="variantsModalList">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        let cart = [];
        
        // Init Select2 for customer
        $('#customerSelect').select2({
            width: '100%',
            dropdownParent: $('#registeredCustomerGroup')
        });

        // Toggle Customer Type
        $('#btnToggleWalkin').on('click', function() {
            $(this).addClass('active');
            $('#btnToggleRegistered').removeClass('active');
            $('#isWalkinInput').val('1');
            $('#walkinNameGroup').removeClass('d-none');
            $('#registeredCustomerGroup').addClass('d-none');
            $('#customerSelect').val('').trigger('change');
            $('#walkinNameInput').val('Walking Customer');
        });

        $('#btnToggleRegistered').on('click', function() {
            $(this).addClass('active');
            $('#btnToggleWalkin').removeClass('active');
            $('#isWalkinInput').val('0');
            $('#registeredCustomerGroup').removeClass('d-none');
            $('#walkinNameGroup').addClass('d-none');
            $('#walkinNameInput').val('');
        });

        // Filter Grid Products via Search Input
        $('#posSearch').on('input', function() {
            let term = $(this).val().toLowerCase();
            $('.product-card').each(function() {
                let name = $(this).data('name').toLowerCase();
                let sku = $(this).data('sku').toLowerCase();
                if (name.includes(term) || sku.includes(term)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Click on Product Card
        $('.product-card').on('click', function() {
            let hasVariants = $(this).data('has-variants') === 1;
            let stockPieces = parseFloat($(this).data('stock-pieces'));
            
            if (stockPieces <= 0) {
                Swal.fire('Out of Stock', 'This product has no stock available.', 'warning');
                return;
            }

            if (hasVariants) {
                // Open variants selection popup modal
                let variants = $(this).data('variants');
                let productName = $(this).data('name');
                let sizeMode = $(this).data('size-mode');
                let piecesPerBox = $(this).data('pieces-per-box') || 1;
                let idBase = $(this).data('id');
                
                $('#variantsModalLabel').text(`Select Variant - ${productName}`);
                let $tbody = $('#variantsModalList');
                $tbody.empty();
                
                variants.forEach((v) => {
                    let isOut = v.stock_pieces <= 0;
                    let desc = `${v.size_val !== '-' ? v.size_val : ''} ${v.color_val !== '-' ? '(' + v.color_val + ')' : ''}`;
                    if (desc.trim() === '') {
                        desc = 'Standard Variant';
                    }
                    
                    let row = `
                        <tr data-id="${v.id}" 
                            data-name="${v.name}" 
                            data-price="${v.price}" 
                            data-stock-pieces="${v.stock_pieces}"
                            data-size-mode="${sizeMode}"
                            data-pieces-per-box="${piecesPerBox}"
                            data-variant-data="${v.variant_data}">
                            <td class="ps-3 fw-bold" style="font-size: 13px;">${desc}</td>
                            <td class="text-center">
                                <span class="badge ${isOut ? 'bg-danger' : 'bg-success'}" style="font-size: 11px;">
                                    ${v.stock}
                                </span>
                            </td>
                            <td class="text-end fw-bold">Rs ${parseFloat(v.price).toLocaleString()}</td>
                            <td class="text-center">
                                <div class="qty-controls mx-auto" style="width: 100px;">
                                    <button type="button" class="qty-btn modal-qty-minus" ${isOut ? 'disabled' : ''}>-</button>
                                    <input type="number" class="qty-input modal-qty-val" value="${isOut ? 0 : 1}" min="1" max="${v.stock_pieces}" ${isOut ? 'disabled' : ''}>
                                    <button type="button" class="qty-btn modal-qty-plus" ${isOut ? 'disabled' : ''}>+</button>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-primary btn-sm variant-add-btn add-to-cart-modal-btn" ${isOut ? 'disabled' : ''}>
                                    <i class="fas fa-plus me-1"></i> Add
                                </button>
                            </td>
                        </tr>
                    `;
                    $tbody.append(row);
                });
                
                $('#variantsModal').modal('show');
            } else {
                // Add base product directly
                let id = $(this).data('id');
                let name = $(this).data('name');
                let price = parseFloat($(this).data('price'));
                let stockPieces = parseFloat($(this).data('stock-pieces'));
                let sizeMode = $(this).data('size-mode');
                let piecesPerBox = parseFloat($(this).data('pieces-per-box')) || 1;
                
                addToCart(id, name, price, stockPieces, 1, sizeMode, piecesPerBox, '');
            }
        });

        // Modal quantity increment/decrement
        $(document).on('click', '.modal-qty-plus', function() {
            let $input = $(this).siblings('.modal-qty-val');
            let max = parseInt($input.attr('max')) || 1;
            let current = parseInt($input.val()) || 1;
            if (current < max) {
                $input.val(current + 1);
            }
        });

        $(document).on('click', '.modal-qty-minus', function() {
            let $input = $(this).siblings('.modal-qty-val');
            let current = parseInt($input.val()) || 1;
            if (current > 1) {
                $input.val(current - 1);
            }
        });

        // Add to cart from Modal
        $(document).on('click', '.add-to-cart-modal-btn', function() {
            let $row = $(this).closest('tr');
            let id = $row.data('id');
            let name = $row.data('name');
            let price = parseFloat($row.data('price'));
            let stockPieces = parseFloat($row.data('stock-pieces'));
            let sizeMode = $row.data('size-mode');
            let piecesPerBox = parseFloat($row.data('pieces-per-box')) || 1;
            let variantData = $row.data('variant-data');
            let qty = parseInt($row.find('.modal-qty-val').val()) || 1;

            if (qty > stockPieces) {
                Swal.fire('Limit Exceeded', 'You cannot add more than the available stock.', 'warning');
                return;
            }

            addToCart(id, name, price, stockPieces, qty, sizeMode, piecesPerBox, variantData);
            
            // Show added feedback
            let $btn = $(this);
            $btn.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> Added');
            setTimeout(() => {
                $btn.removeClass('btn-success').addClass('btn-primary').html('<i class="fas fa-plus me-1"></i> Add');
            }, 1000);
        });

        // Core addToCart Helper
        function addToCart(id, name, price, stockPieces, qty, sizeMode, piecesPerBox, variantData) {
            let cartItem = cart.find(item => item.id === id);
            if (cartItem) {
                if (cartItem.qty + qty <= stockPieces) {
                    cartItem.qty += qty;
                } else {
                    cartItem.qty = stockPieces;
                    Swal.fire('Limit Exceeded', 'Adjusted to maximum available stock.', 'warning');
                }
            } else {
                cart.push({
                    id: id,
                    product_id: id.toString().split('|')[0],
                    name: name,
                    price: price,
                    qty: qty,
                    stock: stockPieces,
                    sizeMode: sizeMode,
                    piecesPerBox: piecesPerBox,
                    variantData: variantData
                });
            }
            renderCart();
        }

        // Render Cart items list
        function renderCart() {
            let $list = $('#cartItemsList');
            let $empty = $('#cartEmptyPlaceholder');
            
            if (cart.length === 0) {
                $list.html($empty);
                $('#cartCountBadge').text('0');
                $('#btnPOSSubmit').prop('disabled', true);
                updateBillSummary();
                return;
            }
            
            $empty.detach();
            $list.empty();
            
            cart.forEach((item, index) => {
                let html = `
                    <div class="cart-item" data-index="${index}">
                        <div class="cart-item-header">
                            <span class="cart-item-name">${item.name}</span>
                            <span class="cart-item-remove remove-cart-item"><i class="fas fa-trash-alt"></i></span>
                        </div>
                        <div class="cart-item-details">
                            <div class="qty-controls">
                                <button type="button" class="qty-btn btn-qty-minus">-</button>
                                <input type="number" class="qty-input cart-qty-val" value="${item.qty}" min="1" max="${item.stock}">
                                <button type="button" class="qty-btn btn-qty-plus">+</button>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="me-1" style="font-size: 13.5px; font-weight: 800; color: #111827;">Rs</span>
                                <input type="number" name="price_per_piece[]" class="form-control form-control-sm text-end cart-price-val" style="width: 80px; padding: 2px 4px; height: 26px; font-size: 13.5px; font-weight: 800; color: #111827; border: 1px solid #e5e7eb; border-radius: 4px;" value="${item.price}" step="any" min="0">
                            </div>
                        </div>
                        <!-- Hidden inputs for backend form serialization -->
                        <input type="hidden" name="product_id[]" value="${item.product_id}">
                        <input type="hidden" name="qty[]" value="${item.qty}">
                        <input type="hidden" name="total_pieces[]" value="${item.qty}">
                        <input type="hidden" name="color[]" value="${item.variantData}">
                    </div>
                `;
                $list.append(html);
            });
            
            $('#cartCountBadge').text(cart.length);
            $('#btnPOSSubmit').prop('disabled', false);
            updateBillSummary();
        }

        // Cart Actions: Remove, Quantity Plus, Minus, Manual change
        $(document).on('input', '.cart-price-val', function() {
            let index = $(this).closest('.cart-item').data('index');
            if (cart[index]) {
                let val = parseFloat($(this).val()) || 0;
                if (val < 0) val = 0;
                cart[index].price = val;
                updateBillSummary();
            }
        });

        $(document).on('click', '.remove-cart-item', function() {
            let index = $(this).closest('.cart-item').data('index');
            cart.splice(index, 1);
            renderCart();
        });

        $(document).on('click', '.btn-qty-plus', function() {
            let index = $(this).closest('.cart-item').data('index');
            let item = cart[index];
            if (item.qty < item.stock) {
                item.qty++;
                renderCart();
            } else {
                Swal.fire('Limit Exceeded', 'You cannot add more than the available stock.', 'warning');
            }
        });

        $(document).on('click', '.btn-qty-minus', function() {
            let index = $(this).closest('.cart-item').data('index');
            let item = cart[index];
            if (item.qty > 1) {
                item.qty--;
                renderCart();
            }
        });

        $(document).on('change', '.cart-qty-val', function() {
            let index = $(this).closest('.cart-item').data('index');
            let item = cart[index];
            let val = parseInt($(this).val()) || 1;
            
            if (val <= 0) val = 1;
            if (val > item.stock) {
                Swal.fire('Limit Exceeded', 'Adjusted to maximum available stock.', 'warning');
                val = item.stock;
            }
            
            item.qty = val;
            renderCart();
        });

        // Live Discount and Cash calculation
        $('#summaryDiscount, #receivedCash').on('input', function() {
            updateBillSummary();
        });

        function updateBillSummary() {
            let subtotal = 0;
            cart.forEach(item => {
                subtotal += item.qty * item.price;
            });
            
            let discount = parseFloat($('#summaryDiscount').val()) || 0;
            let payable = Math.max(0, subtotal - discount);
            
            $('#summarySubtotal').text('Rs ' + subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#summaryPayable').text('Rs ' + payable.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            // Received cash and Change
            let received = parseFloat($('#receivedCash').val()) || 0;
            let change = Math.max(0, received - payable);
            $('#returnedChange').val(change.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            // Populating hidden backend mirrors
            $('#backendSubTotal1').val(subtotal.toFixed(2));
            $('#backendSubTotal2').val(subtotal.toFixed(2));
            $('#backendTotalNet').val(payable.toFixed(2));
            $('#backendCash').val(received.toFixed(2));
        }

        // Form Submit
        $('#posCheckoutForm').on('submit', function(e) {
            e.preventDefault();
            
            // Validate Cash Received (Avoid checkout without payment unless registered)
            let isWalkin = $('#isWalkinInput').val() === '1';
            let payable = parseFloat($('#backendTotalNet').val()) || 0;
            let received = parseFloat($('#receivedCash').val()) || 0;
            
            if (isWalkin && received < payable) {
                Swal.fire('Incomplete Payment', 'Walk-in customer must pay the full bill amount.', 'warning');
                return;
            }

            if (!isWalkin && $('#customerSelect').val() === null) {
                Swal.fire('Customer Required', 'Please select a registered customer.', 'warning');
                return;
            }

            let $btn = $('#btnPOSSubmit');
            let origHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');

            $.ajax({
                url: '{{ route("sales.store") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $btn.prop('disabled', false).html(origHtml);
                    if (response.ok) {
                        // Open Invoice receipt in new tab
                        if (response.invoice_url) {
                            window.open(response.invoice_url, '_blank');
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Sale Posted!',
                            text: 'POS sale checkout completed successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Reset POS form and state
                            cart = [];
                            $('#summaryDiscount').val(0);
                            $('#receivedCash').val('');
                            $('#posCheckoutForm')[0].reset();
                            $('#btnToggleWalkin').trigger('click');
                            renderCart();
                            
                            // Reload screen stock levels dynamically to show latest counts
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to complete sale.', 'error');
                    }
                },
                error: function(err) {
                    $btn.prop('disabled', false).html(origHtml);
                    let msg = 'Failed to submit POS sale transaction.';
                    if (err.responseJSON && err.responseJSON.message) {
                        msg = err.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                }
            });
        });
    });
</script>
@endsection
