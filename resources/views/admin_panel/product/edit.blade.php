@extends('admin_panel.layout.app')

@section('content')
    {{-- 
        SUCCESS: Horizontal Compact Layout Redesign for Edit Page
        Matches Create Product UI
    --}}
    
    {{-- External Resources --}}
    <link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendors/select2/css/select2.min.css') }}" rel="stylesheet" />
    {{-- line-awesome replaced by Font Awesome 6 (local) --}}
    <link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius-md: 10px;
            --radius-lg: 16px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
        }

        .page-container {
            max-width: 1350px;
            margin: 0 auto;
            padding: 15px;
        }

        .section-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.06);
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .card-header-pro {
            padding: 12px 20px;
            border-bottom: 1px solid var(--border-color);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title-pro {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .card-body-pro {
            padding: 20px;
        }

        /* --- Form Styling --- */
        .form-label-pro {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 4px;
            letter-spacing: 0.02em;
        }

        .form-control-pro {
            display: block;
            width: 100%;
            padding: 8px 12px;
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--text-main);
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control-pro:focus {
            border-color: var(--primary);
            outline: 0;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-select-pro {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        /* --- Image Uploader --- */
        .img-uploader {
            width: 100%;
            border: 2px dashed #cbd5e1;
            border-radius: var(--radius-md);
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.2s;
        }

        .img-uploader:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .img-uploader img {
            max-width: 100%;
            object-fit: contain;
            padding: 5px;
        }

        /* Select2 Style tweaks */
        .select2-container--default .select2-selection--multiple {
            border-radius: var(--radius-md) !important;
            border-color: var(--border-color) !important;
            padding: 2px 6px !important;
        }
    </style>

    <div class="page-container">
        
        {{-- Page Title --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('product') }}" class="btn btn-white border shadow-sm rounded-circle p-0" style="width: 36px; height: 36px; display: grid; place-items: center;">
                    <i class="las la-arrow-left"></i>
                </a>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Edit Product</h5>
                    <small class="text-muted" style="font-size:0.8rem;">Update item settings and stock values</small>
                </div>
            </div>
        </div>

        <form id="productForm" action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                
                {{-- MAIN COLUMN: Full Width --}}
                <div class="col-12">
                    
                    {{-- CARD 1: Identity & Categorization --}}
                    <div class="section-card">
                        <div class="card-header-pro">
                            <h5 class="card-title-pro"><i class="las la-tag text-primary"></i> Product Identity</h5>
                        </div>
                        <div class="card-body-pro">
                            <div class="row g-3">
                                
                                {{-- Sub-grid for left content, image on the right --}}
                                <div class="col-md-9">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label-pro">Product Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control-pro fs-6 fw-bold" name="product_name" required value="{{ $product->item_name }}" placeholder="e.g. Ceramic Floor Tile 60x60">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label-pro">Barcode Auto-Gen</label>
                                            <div class="d-flex">
                                                <input type="text" class="form-control-pro" id="barcodeInput" name="barcode_path" value="{{ $product->barcode_path }}" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                                <button type="button" class="btn btn-light border" id="generateBarcodeBtn" style="border-left: 0; border-top-left-radius: 0; border-bottom-left-radius: 0; border-top-right-radius: var(--radius-md); border-bottom-right-radius: var(--radius-md);"><i class="las la-magic"></i></button>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label class="form-label-pro">Category <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-control-pro form-select-pro" id="category-dropdown" name="category_id" required>
                                                    <option value="">Select...</option>
                                                    @foreach ($categories as $cat)
                                                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-light border px-2 shadow-sm" data-toggle="modal" data-target="#categoryModal">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label-pro">Sub Category</label>
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-control-pro form-select-pro" id="subcategory-dropdown" name="sub_category_id">
                                                    <option value="">Select...</option>
                                                    @foreach ($subcategories as $subCat)
                                                        @if ($subCat->category_id == $product->category_id)
                                                            <option value="{{ $subCat->id }}" {{ $product->sub_category_id == $subCat->id ? 'selected' : '' }}>{{ $subCat->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-light border px-2 shadow-sm" data-toggle="modal" data-target="#subcategoryModal">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label-pro">Brand</label>
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-control-pro form-select-pro" name="brand_id" required>
                                                    <option value="">Select...</option>
                                                    @foreach ($brands as $brand)
                                                        <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-light border px-2 shadow-sm" data-toggle="modal" data-target="#brandModal">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Image Uploader side-by-side (Right) --}}
                                <div class="col-md-3">
                                    <label class="form-label-pro">Product Image</label>
                                    <input type="file" id="imageInput" name="image" class="d-none" accept="image/*">
                                    <div class="img-uploader" style="height: 110px;" onclick="document.getElementById('imageInput').click()">
                                        <button type="button" id="clearImageBtn" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 {{ $product->image ? '' : 'd-none' }} rounded-circle" style="width:20px;height:20px;padding:0;z-index:10; font-size:12px; line-height:1;">&times;</button>
                                        @if($product->image)
                                            <img id="preview" src="{{ asset('uploads/products/' . $product->image) }}" style="max-height: 100px;">
                                            <div id="uploadPlaceholder" class="text-center p-2 d-none">
                                                <i class="las la-camera fs-3 text-primary"></i>
                                                <div class="fw-bold" style="font-size: 11px;">Upload</div>
                                            </div>
                                        @else
                                            <img id="preview" class="d-none" style="max-height: 100px;">
                                            <div id="uploadPlaceholder" class="text-center p-2">
                                                <i class="las la-camera fs-3 text-primary"></i>
                                                <div class="fw-bold" style="font-size: 11px;">Upload</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-12 mt-3 pt-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="form-label-pro text-primary mb-0">Product Variants (Optional)</h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="enableVariantsBtn">+ Add Variants</button>
                                    </div>
                                    <div id="variantsContainer" class="d-none">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm align-middle mb-1" id="variantsTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 150px;">Variant Name</th>
                                                        <th>Size</th>
                                                        <th>Color</th>
                                                        <th style="width: 100px;">Stock</th>
                                                        <th style="width: 100px;">Sale Price</th>
                                                        <th style="width: 100px;">Purch Price</th>
                                                        <th style="width: 80px;">Alert</th>
                                                        <th style="width: 140px;">Barcode</th>
                                                        <th style="width: 90px; text-align:center;">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="variantsBody">
                                                    <!-- rows injected by JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- CARD 2: Stock Specifications & Pricing --}}
                    <div class="section-card" style="display:none !important;">
                        <div class="card-header-pro d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <h5 class="card-title-pro"><i class="las la-box-open text-info"></i> Stock & Pricing</h5>
                            
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <div class="bg-light px-3 py-1 rounded border d-flex gap-3 text-nowrap align-items-center" style="font-size: 0.82rem;">
                                    <div>
                                        <span class="text-muted fw-bold">Total Stock:</span>
                                        <strong class="text-primary fs-6" id="total_stock_display">0</strong> <span class="text-muted small">pcs</span>
                                    </div>
                                    <div class="border-start ps-3">
                                        <span class="text-muted fw-bold">Est. Value:</span>
                                        <span class="text-success fw-bold">PKR <span id="sale_total_display">0.00</span></span>
                                    </div>
                                </div>

                                {{-- Switcher --}}
                                <div class="btn-group btn-group-sm" role="group">
                                    <input type="radio" class="btn-check" name="size_mode" id="mode_carton" value="by_cartons" {{ $product->size_mode == 'by_cartons' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary px-3 fw-bold" for="mode_carton"><i class="las la-box me-1"></i> Carton</label>

                                    <input type="radio" class="btn-check" name="size_mode" id="mode_piece" value="by_pieces" {{ $product->size_mode == 'by_pieces' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary px-3 fw-bold" for="mode_piece"><i class="las la-puzzle-piece me-1"></i> Piece</label>
                                    
                                    {{-- BY SIZE SWITCHER HIDE / COMMENTED --}}
                                    <input type="radio" class="d-none" name="size_mode" id="mode_size" value="by_size" {{ $product->size_mode == 'by_size' || !$product->size_mode ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <div class="card-body-pro">
                            <div class="row g-3">
                                
                                {{-- Dynamic Stock Fields --}}
                                <div class="col-md-12">
                                    <div class="row g-3">
                                        {{-- Carton Mode Fields --}}
                                        <div class="col-md-3 group-by-size">
                                            <label class="form-label-pro">Pcs / Carton</label>
                                            <input type="number" class="form-control-pro" name="pieces_per_box" id="pieces_per_box" value="{{ $product->pieces_per_box }}" placeholder="0">
                                        </div>
                                        <div class="col-md-3 group-by-size">
                                            <label class="form-label-pro">In-Stock Cartons</label>
                                            <input type="number" class="form-control-pro border-primary text-primary fw-bold" name="boxes_quantity" id="boxes_quantity" value="{{ $product->boxes_quantity }}" placeholder="0">
                                        </div>
                                        <div class="col-md-3 group-loose">
                                            <label class="form-label-pro text-warning">Loose Pieces (Extra)</label>
                                            <input type="number" class="form-control-pro border-warning" name="loose_pieces" id="loose_pieces" value="{{ $product->loose_pieces }}" placeholder="0">
                                        </div>

                                        {{-- Piece Mode Fields --}}
                                        <div class="col-md-6 group-piece-only d-none">
                                            <label class="form-label-pro text-primary">Total In-Stock Quantity (Pieces)</label>
                                            <input type="number" class="form-control-pro border-primary text-primary fw-bold" name="piece_quantity" id="piece_quantity" value="{{ $product->piece_quantity }}" placeholder="0">
                                        </div>

                                        {{-- Common Alert Quantity --}}
                                        <div class="col-md-3">
                                            <label class="form-label-pro text-danger">Low Stock Alert (Cartons)</label>
                                            <input type="number" class="form-control-pro border-danger" name="alert_carton_quantity" id="alert_carton_quantity" min="0" value="{{ $product->alert_carton_quantity }}" placeholder="e.g. 5">
                                        </div>
                                    </div>
                                </div>

                                {{-- Pricing Group --}}
                                <div class="col-md-12 pt-3 border-top">
                                    <h6 class="form-label-pro text-primary mb-2">Unit Price Settings (Rs.)</h6>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label-pro text-success">Sale Price <span class="unit-label text-muted fw-normal">(pc)</span></label>
                                            <input type="number" class="form-control-pro fw-bold text-success" name="sale_price_per_box" id="sale_price_per_box" step="0.01" value="{{ $product->sale_price_per_piece }}" placeholder="0.00">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label-pro text-secondary">Purchase Price <span class="unit-label text-muted fw-normal">(pc)</span></label>
                                            <input type="number" class="form-control-pro text-muted" name="purchase_price_per_piece" id="purchase_price_per_piece" step="0.01" value="{{ $product->purchase_price_per_piece }}" placeholder="0.00">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label-pro">Sale Disc (%)</label>
                                            <input type="number" class="form-control-pro" name="sale_discount_percent" step="0.01" value="{{ $product->sale_discount_percent ?? 0 }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label-pro">Purch Disc (%)</label>
                                            <input type="number" class="form-control-pro" name="purchase_discount_percent" step="0.01" value="{{ $product->purchase_discount_percent ?? 0 }}">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- INLINE ACTIONS ROW --}}
                    <div class="d-flex justify-content-end align-items-center bg-white p-3 rounded shadow-sm border mb-4 gap-2">
                        <a href="{{ route('product') }}" class="btn btn-outline-secondary px-4 py-2" style="border-radius: var(--radius-md); font-size: 0.9rem;">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" style="background: var(--primary); border: none; border-radius: var(--radius-md); font-size: 0.9rem;">
                            <i class="las la-check-circle me-1"></i> UPDATE PRODUCT
                        </button>
                    </div>

                </div>

            </div>

            {{-- HIDDEN BY-SIZE FORM CONTROLS FOR MIGRATION BACKWARD COMPATIBILITY --}}
            <div style="display:none !important;">
                <input type="number" name="height" id="height" step="0.01" value="{{ $product->height }}">
                <input type="number" name="width" id="width" step="0.01" value="{{ $product->width }}">
                <input type="number" name="price_per_m2" id="price_per_m2" step="0.01" value="{{ $product->price_per_m2 }}">
                <input type="number" name="purchase_price_per_m2" id="purchase_price_per_m2" step="0.01" value="{{ $product->purchase_price_per_m2 }}">
            </div>

        </form>

        {{-- Modals --}}
        <div id="categoryModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md);">
                    <form action="{{ route('store.category') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-bold">New Category</h6>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="mb-3">
                                <label class="form-label-pro">Category Name</label>
                                <input type="text" name="name" class="form-control-pro" required placeholder="e.g. Ceramics">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">Create Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="subcategoryModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md);">
                    <form action="{{ route('store.subcategory') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-bold">New Subcategory</h6>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="mb-3">
                                <label class="form-label-pro">Parent Category</label>
                                <select name="category_id" class="form-select form-control-pro">
                                    @foreach ($categories as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-pro">Name</label>
                                <input type="text" name="name" class="form-control-pro" required placeholder="e.g. Floor Tiles">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">Create Subcategory</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Brand Modal --}}
        <div id="brandModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md);">
                    <form action="{{ route('store.Brand') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-bold">New Brand</h6>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="mb-3">
                                <label class="form-label-pro">Brand Name</label>
                                <input type="text" name="name" class="form-control-pro" required placeholder="e.g. Johnson">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">Create Brand</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- UI Elements ---
            const form = document.getElementById('productForm');
            const modeRadios = document.querySelectorAll('input[name="size_mode"]');

            // Containers
            const grpBySize = document.querySelectorAll('.group-by-size');
            const grpLoose = document.querySelectorAll('.group-loose');
            const grpPieceOnly = document.querySelectorAll('.group-piece-only');

            function toggleGroup(els, hide) {
                els.forEach(el => hide ? el.classList.add('d-none') : el.classList.remove('d-none'));
            }

            // Labels
            const unitLabels = document.querySelectorAll('.unit-label');

            // --- Logic Update Mode ---
            function updateMode() {
                const modeEl = document.querySelector('input[name="size_mode"]:checked');
                if(!modeEl) return;
                const mode = modeEl.value;

                // Sync UI Switcher labels highlight
                modeRadios.forEach(r => {
                    const lbl = document.querySelector(`label[for="${r.id}"]`);
                    if(lbl) {
                        if(r.checked) {
                            lbl.classList.remove('btn-outline-primary');
                            lbl.classList.add('btn-primary');
                        } else {
                            lbl.classList.remove('btn-primary');
                            lbl.classList.add('btn-outline-primary');
                        }
                    }
                });

                // Hide ALL dynamic wrappers
                toggleGroup(grpBySize, true);
                toggleGroup(grpLoose, true);
                toggleGroup(grpPieceOnly, true);

                if (mode === 'by_cartons') {
                    toggleGroup(grpBySize, false);
                    toggleGroup(grpLoose, false);

                    setRequired(['pieces_per_box', 'boxes_quantity', 'sale_price_per_box', 'purchase_price_per_piece'], true);
                    setRequired(['piece_quantity'], false);

                } else if (mode === 'by_pieces') {
                    toggleGroup(grpPieceOnly, false);

                    setRequired(['piece_quantity', 'sale_price_per_box', 'purchase_price_per_piece'], true);
                    setRequired(['pieces_per_box', 'boxes_quantity'], false);
                }

                calculate();
            }

            function setRequired(ids, isReq) {
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) isReq ? el.setAttribute('required', 'required') : el.removeAttribute('required');
                });
            }

            function calculate() {
                const modeEl = document.querySelector('input[name="size_mode"]:checked');
                if(!modeEl) return;
                const mode = modeEl.value;

                const v = (id) => parseFloat(document.getElementById(id)?.value) || 0;
                let stock = 0;
                let saleVal = 0;

                if (mode === 'by_cartons') {
                    stock = (v('pieces_per_box') * v('boxes_quantity')) + v('loose_pieces');
                    saleVal = stock * v('sale_price_per_box');

                } else if (mode === 'by_pieces') {
                    stock = v('piece_quantity');
                    saleVal = stock * v('sale_price_per_box');
                }

                setText('total_stock_display', stock);
                setText('sale_total_display', saleVal.toLocaleString(undefined, { minimumFractionDigits: 2 }));
            }

            function setText(id, val) {
                const el = document.getElementById(id);
                if (el) el.innerText = val;
            }

            // Events
            modeRadios.forEach(r => r.addEventListener('change', function() {
                // Do NOT resetInputs() on edit page - keep pre-saved details intact unless modified!
                updateMode();
            }));
            form.querySelectorAll('input').forEach(i => i.addEventListener('input', calculate));

            updateMode();

            // Image Handler
            const imgInput = document.getElementById('imageInput');
            const preview = document.getElementById('preview');
            const ph = document.getElementById('uploadPlaceholder');
            const clr = document.getElementById('clearImageBtn');

            imgInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const r = new FileReader();
                    r.onload = (e) => {
                        preview.src = e.target.result;
                        preview.classList.remove('d-none');
                        if(ph) ph.classList.add('d-none');
                        clr.classList.remove('d-none');
                    };
                    r.readAsDataURL(this.files[0]);
                }
            });

            clr.addEventListener('click', (e) => {
                e.stopPropagation();
                imgInput.value = '';
                
                // For Edit: Revert to original if exists
                @if($product->image)
                    preview.src = "{{ asset('uploads/products/' . $product->image) }}";
                    preview.classList.remove('d-none');
                    if(ph) ph.classList.add('d-none');
                @else
                    preview.classList.add('d-none');
                    if(ph) ph.classList.remove('d-none');
                    clr.classList.add('d-none');
                @endif
            });

            // AJAX Submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                // --- Sync Variants data to hidden main fields for backend compatibility ---
                const vStocks = document.querySelectorAll('input[name="variant_stock[]"]');
                const vSale = document.querySelectorAll('input[name="variant_sale_price[]"]');
                const vPurch = document.querySelectorAll('input[name="variant_purchase_price[]"]');
                const vAlert = document.querySelectorAll('input[name="variant_alert_qty[]"]');

                let totalStock = 0;
                vStocks.forEach(el => totalStock += (parseFloat(el.value) || 0));

                let firstSale = vSale.length > 0 ? (parseFloat(vSale[0].value) || 0) : 0;
                let firstPurch = vPurch.length > 0 ? (parseFloat(vPurch[0].value) || 0) : 0;
                let firstAlert = vAlert.length > 0 ? (parseFloat(vAlert[0].value) || 0) : 0;

                const modeEl = document.querySelector('input[name="size_mode"]:checked');
                const mode = modeEl ? modeEl.value : 'by_pieces';
                
                if (vStocks.length > 0) {
                    if(mode === 'by_cartons') {
                        document.getElementById('boxes_quantity').value = totalStock;
                        document.getElementById('pieces_per_box').value = 1;
                        document.getElementById('loose_pieces').value = 0;
                        document.getElementById('piece_quantity').value = 0;
                    } else {
                        document.getElementById('piece_quantity').value = totalStock;
                        document.getElementById('boxes_quantity').value = 0;
                    }
                    document.getElementById('sale_price_per_box').value = firstSale;
                    document.getElementById('purchase_price_per_piece').value = firstPurch;
                    document.getElementById('alert_carton_quantity').value = firstAlert;
                }
                // ------------------------------------------------------------------------

                const btn = document.querySelector('button[type="submit"]');
                const originalContent = btn.innerHTML;
                btn.innerHTML = '<i class="las la-spinner la-spin"></i> Updating...';
                btn.disabled = true;

                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST', // Method POST because we use _method=PUT in formData
                    headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
                    body: formData
                })
                .then(r => r.json().then(data => ({status: r.status, body: data})))
                .then(({status, body}) => {
                    if (status === 200 || body.status === 'success') {
                         Swal.fire({
                            icon: 'success', title: 'Updated!',
                            text: 'Product updated successfully', timer: 1500, showConfirmButton: false
                        }).then(() => window.location.href = "{{ route('product') }}");
                    } else {
                        const msg = body.errors ? Object.values(body.errors).flat().join('<br>') : (body.message || 'Error');
                        Swal.fire({icon: 'error', title: 'Error', html: msg});
                    }
                })
                .catch(err => Swal.fire({icon: 'error', title: 'Error', text: 'Server Error'}))
                .finally(() => {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                });
            });

            // Barcode
            const barIn = document.getElementById('barcodeInput');
            const barBtn = document.getElementById('generateBarcodeBtn');
            const barcodeUrl = '{{ route('generate-barcode-image') }}';
            
            if(barBtn) barBtn.addEventListener('click', () => fetch(barcodeUrl).then(r => r.json()).then(d => barIn.value = d.barcode_number));
            
            $('#category-dropdown').on('change', function() {
                var cid = $(this).val();
                if (cid) {
                    $.get('/get-subcategories/' + cid, function(d) {
                        $('#subcategory-dropdown').empty().append('<option value="">Select...</option>');
                        $.each(d, function(_, v) {
                            $('#subcategory-dropdown').append('<option value="' + v.id + '">' + v.name + '</option>');
                        });
                    });
                }
            });

            // Quick Add AJAX Handlers
            function handleQuickAdd(modalId, selectSelector) {
                $('#' + modalId + ' form').on('submit', function(e) {
                    e.preventDefault();
                    let form = $(this);
                    let btn = form.find('button[type="submit"]');
                    let originalText = btn.text();
                    btn.text('Saving...').prop('disabled', true);
                    
                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: form.serialize(),
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        success: function(res) {
                            if(res.success) {
                                $(selectSelector).append(new Option(res.name, res.id, true, true)).trigger('change');
                                $('#' + modalId).modal('hide');
                                form[0].reset();
                                Swal.fire({icon: 'success', title: 'Added successfully', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500});
                            }
                        },
                        error: function() {
                            Swal.fire({icon: 'error', title: 'Error', text: 'Something went wrong!'});
                        },
                        complete: function() {
                            btn.text(originalText).prop('disabled', false);
                        }
                    });
                });
            }

            handleQuickAdd('categoryModal', '#category-dropdown, #subcategoryModal select[name="category_id"]');
            handleQuickAdd('subcategoryModal', '#subcategory-dropdown');
            handleQuickAdd('brandModal', 'select[name="brand_id"]');

            // Variants Logic
            const enableVariantsBtn = document.getElementById('enableVariantsBtn');
            const variantsContainer = document.getElementById('variantsContainer');
            const variantsBody = document.getElementById('variantsBody');

            function generateRandomBarcode() {
                return Math.floor(100000 + Math.random() * 900000).toString();
            }

            function addVariantRow(v = null) {
                const tr = document.createElement('tr');
                const productName = document.querySelector('input[name="product_name"]').value || '';
                
                const nameVal = v ? (v.name || '') : productName;
                const sizeVal = v ? (v.size || '') : '';
                const colorVal = v ? (v.color || '') : '';
                const stockVal = v ? (v.stock || 0) : '';
                const saleVal = v ? (v.sale_price || 0) : '';
                const purchVal = v ? (v.purch_price || 0) : '';
                const alertVal = v ? (v.alert || 0) : '';
                const barcodeVal = v ? (v.barcode || '') : generateRandomBarcode();

                tr.innerHTML = `
                    <td><input type="text" class="form-control-pro form-control-sm" name="variant_name[]" value="${nameVal}" placeholder="Name"></td>
                    <td><input type="text" class="form-control-pro form-control-sm" name="variant_size[]" value="${sizeVal}" placeholder="Size"></td>
                    <td><input type="text" class="form-control-pro form-control-sm" name="variant_color[]" value="${colorVal}" placeholder="Color"></td>
                    <td><input type="number" class="form-control-pro form-control-sm" name="variant_stock[]" value="${stockVal}" placeholder="0"></td>
                    <td><input type="number" class="form-control-pro form-control-sm" name="variant_sale_price[]" value="${saleVal}" step="0.01" placeholder="0.00"></td>
                    <td><input type="number" class="form-control-pro form-control-sm" name="variant_purchase_price[]" value="${purchVal}" step="0.01" placeholder="0.00"></td>
                    <td><input type="number" class="form-control-pro form-control-sm" name="variant_alert_qty[]" value="${alertVal}" placeholder="0"></td>
                    <td>
                        <div class="d-flex">
                            <input type="text" class="form-control-pro form-control-sm" name="variant_barcode[]" value="${barcodeVal}" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <button type="button" class="btn btn-sm btn-light border gen-var-barcode px-2" style="border-left: 0; border-top-left-radius: 0; border-bottom-left-radius: 0;" title="Generate New"><i class="las la-sync-alt"></i></button>
                        </div>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-success add-var-btn px-2 py-1" title="Add"><i class="las la-check"></i></button>
                        <button type="button" class="btn btn-danger remove-var-btn px-2 py-1" title="Remove"><i class="las la-times"></i></button>
                    </td>
                `;
                variantsBody.appendChild(tr);
            }

            enableVariantsBtn.addEventListener('click', function() {
                if (variantsContainer.classList.contains('d-none')) {
                    variantsContainer.classList.remove('d-none');
                    this.innerHTML = '- Remove Variants';
                    this.classList.replace('btn-outline-primary', 'btn-outline-danger');
                    if(variantsBody.children.length === 0) addVariantRow();
                } else {
                    variantsContainer.classList.add('d-none');
                    this.innerHTML = '+ Add Variants';
                    this.classList.replace('btn-outline-danger', 'btn-outline-primary');
                    variantsBody.innerHTML = ''; // clear all rows
                }
            });

            variantsBody.addEventListener('click', function(e) {
                const addBtn = e.target.closest('.add-var-btn');
                const remBtn = e.target.closest('.remove-var-btn');
                const genBtn = e.target.closest('.gen-var-barcode');

                if (addBtn) {
                    addVariantRow();
                } else if (remBtn) {
                    const row = remBtn.closest('tr');
                    if (variantsBody.children.length > 1) {
                        row.remove();
                    } else {
                        // If it's the last row, clear inputs instead of removing
                        row.querySelectorAll('input').forEach(inp => inp.value = '');
                        row.querySelector('input[name="variant_barcode[]"]').value = generateRandomBarcode();
                    }
                } else if (genBtn) {
                    const input = genBtn.closest('td').querySelector('input');
                    input.value = generateRandomBarcode();
                }
            });

            // Initialize existing variants
            const existingColorStr = `{!! addslashes($product->color ?? 'null') !!}`;
            try {
                const parsed = JSON.parse(existingColorStr);
                if (Array.isArray(parsed) && parsed.length > 0 && typeof parsed[0] === 'object') {
                    // It's the new variants format
                    enableVariantsBtn.click(); // opens the container
                    variantsBody.innerHTML = ''; // clear default empty row
                    parsed.forEach(v => addVariantRow(v));
                }
            } catch(e) {}

        });
    </script>
@endsection
