@extends('admin_panel.layout.app')
@section('content')
    <style>
        div.dataTables_wrapper div.dataTables_length select {
            width: 75px !important
        }

        /* Bulk Edit Checkbox & Label Alignment */
        .bulk-edit-controls-card .form-check-inline {
            display: inline-flex !important;
            align-items: center !important;
            margin-right: 1.5rem !important;
            margin-left: 0 !important;
            padding-left: 0 !important;
            cursor: pointer;
        }
        .bulk-edit-controls-card .form-check-input {
            margin: 0 !important;
            cursor: pointer;
            width: 1rem;
            height: 1rem;
        }
        .bulk-edit-controls-card .form-check-label {
            cursor: pointer;
            user-select: none;
            line-height: 1 !important;
            margin-bottom: 0 !important;
            margin-left: 6px !important;
            padding-left: 0 !important;
        }

        /* Fine Styling Refinements */
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
            overflow: hidden;
            border: 1px solid #e2e8f0 !important;
        }
        .card-header {
            background-color: #ffffff !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 1rem 1.5rem !important;
        }
        .card-header h5 {
            color: #1e293b;
            font-size: 1.15rem;
        }

        /* Filter inputs */
        #filterForm input, #filterForm select {
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 0.35rem 0.5rem;
            font-size: 0.85rem;
        }
        #filterForm input:focus, #filterForm select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        /* Table custom appearance */
        #productTable {
            border-collapse: collapse !important;
            margin-top: 1rem !important;
            margin-bottom: 1rem !important;
            border: 1px solid #cbd5e1 !important;
        }
        #productTable thead th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            border: 1px solid #cbd5e1 !important;
            border-bottom: 2px solid #94a3b8 !important;
        }
        #productTable tbody td {
            padding: 10px 14px;
            border: 1px solid #e2e8f0 !important;
            font-size: 0.88rem;
            color: #334155;
            vertical-align: middle;
        }
        #productTable tbody tr {
            transition: background-color 0.15s ease;
        }
        #productTable tbody tr:hover {
            background-color: #f8fafc !important;
        }

        /* Zoom-in thumbnail preview */
        #productTable img.rounded {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        #productTable img.rounded:hover {
            transform: scale(1.2);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
            z-index: 10;
            position: relative;
        }

        /* Status & Alert Badges */
        .badge.bg-success {
            background-color: #ecfdf5 !important;
            color: #065f46 !important;
            border: 1px solid #a7f3d0 !important;
        }
        .badge.bg-danger {
            background-color: #fef2f2 !important;
            color: #991b1b !important;
            border: 1px solid #fecaca !important;
        }
        .badge.bg-danger-subtle {
            background-color: #fef2f2 !important;
            color: #b91c1c !important;
            border: 1px solid #fee2e2 !important;
        }
        .badge.bg-light {
            background-color: #f8fafc !important;
            color: #64748b !important;
            border: 1px solid #e2e8f0 !important;
        }

        /* Clean action buttons styling */
        .btn-sm {
            border-radius: 6px;
            padding: 0.3rem 0.6rem;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .btn-warning {
            background-color: #fffbeb !important;
            color: #d97706 !important;
            border-color: #fde68a !important;
        }
        .btn-warning:hover {
            background-color: #fef3c7 !important;
            color: #b45309 !important;
            border-color: #fcd34d !important;
        }
        .btn-outline-primary {
            color: #4f46e5 !important;
            border-color: #c7d2fe !important;
        }
        .btn-outline-primary:hover {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
            border-color: #4f46e5 !important;
        }
        .btn-outline-success {
            color: #10b981 !important;
            border-color: #a7f3d0 !important;
        }
        .btn-outline-success:hover {
            background-color: #10b981 !important;
            color: #ffffff !important;
            border-color: #10b981 !important;
        }
        .btn-outline-danger {
            color: #ef4444 !important;
            border-color: #fecaca !important;
        }
        .btn-outline-danger:hover {
            background-color: #ef4444 !important;
            color: #ffffff !important;
            border-color: #ef4444 !important;
        }
    </style>



    <div class="card shadow-sm border-0">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">📦 Product List</h5>
                <small class="text-muted">Manage all products here</small>
            </div>
            <div class="d-flex justify-content-between align-items-end gap-2 flex-wrap">

                {{-- Import / Export Buttons --}}
                <a href="{{ route('products.template') }}"
                   class="btn btn-outline-secondary btn-sm"
                   title="Download blank CSV template">
                    <i class="las la-file-csv"></i> Template
                </a>

                <a href="{{ route('products.export') }}"
                   class="btn btn-success btn-sm"
                   title="Export all products to CSV">
                    <i class="las la-file-download"></i> Export CSV
                </a>

                @if (auth()->user()->can('products.create') || auth()->user()->email === 'admin@admin.com')
                    <button type="button" class="btn btn-warning btn-sm" id="openImportModalBtn"
                            title="Import products from CSV">
                        <i class="las la-file-upload"></i> Import CSV
                    </button>
                    <a href="create_prodcut" class="btn btn-primary btn-sm">
                        <i class="las la-plus"></i> Add Product
                    </a>
                @endif

            </div>
        </div>

    {{-- ══════════════════════════════════════════════════════════
         IMPORT MODAL  (Bootstrap 4 compatible)
    ══════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">

                {{-- Header --}}
                <div class="modal-header" style="background: linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff;">
                    <div>
                        <h5 class="modal-title fw-bold" id="importModalLabel">
                            <i class="las la-file-upload me-2"></i>Import Products from CSV
                        </h5>
                        <small class="opacity-75">New products will be created. Existing ones (matched by Barcode or Item Code) will be updated.</small>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:1; font-size:1.5rem; background:none; border:none;">&times;</button>
                </div>

                {{-- Body --}}
                <div class="modal-body p-4">

                    {{-- Instructions --}}
                    <div class="alert alert-info d-flex gap-2 align-items-start mb-4" style="font-size:0.85rem;">
                        <i class="las la-info-circle fs-5 mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>How to use:</strong><br>
                            1. Download the <a href="{{ route('products.template') }}" class="alert-link">CSV Template</a> first.<br>
                            2. Fill in your data in Excel and save as <strong>CSV</strong>.<br>
                            3. Upload here — existing products (matched by Barcode or Item Code) will be <strong>updated</strong>; new rows will be <strong>created</strong>.<br>
                            4. Stock differences will be automatically adjusted and logged.
                        </div>
                    </div>

                    {{-- File Drop Zone --}}
                    <div id="dropZone" class="border border-2 border-dashed rounded-3 text-center p-5 mb-3"
                         style="border-color:#c7d2fe !important; background:#f5f3ff; cursor:pointer; transition: all 0.2s;"
                         onclick="document.getElementById('csvFileInput').click()"
                         ondragover="event.preventDefault(); this.style.background='#ede9fe';"
                         ondragleave="this.style.background='#f5f3ff';"
                         ondrop="handleDrop(event)">
                        <i class="las la-cloud-upload-alt" style="font-size:3rem; color:#7c3aed;"></i>
                        <div class="fw-bold mt-2 text-primary" style="font-size:1rem;">Click to browse or drag & drop CSV file here</div>
                        <div class="text-muted mt-1" id="fileNameDisplay" style="font-size:0.82rem;">No file selected — Max 5 MB</div>
                    </div>
                    <input type="file" id="csvFileInput" class="d-none" accept=".csv,.txt">

                    {{-- Progress --}}
                    <div id="importProgress" class="d-none mb-3">
                        <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-1">
                            <div class="spinner-border spinner-border-sm"></div>
                            Processing your file, please wait...
                        </div>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width:100%"></div>
                        </div>
                    </div>

                    {{-- Results --}}
                    <div id="importResults" class="d-none">
                        <hr>
                        <h6 class="fw-bold mb-3">📊 Import Results</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-4">
                                <div class="text-center p-3 rounded-3" style="background:#d1fae5;">
                                    <div style="font-size:2rem; font-weight:800; color:#065f46;" id="resultCreated">0</div>
                                    <div class="text-success fw-bold" style="font-size:0.8rem;">CREATED</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-3 rounded-3" style="background:#dbeafe;">
                                    <div style="font-size:2rem; font-weight:800; color:#1e40af;" id="resultUpdated">0</div>
                                    <div class="text-primary fw-bold" style="font-size:0.8rem;">UPDATED</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-3 rounded-3" style="background:#fef3c7;">
                                    <div style="font-size:2rem; font-weight:800; color:#92400e;" id="resultSkipped">0</div>
                                    <div class="text-warning fw-bold" style="font-size:0.8rem;">SKIPPED</div>
                                </div>
                            </div>
                        </div>
                        <div id="importErrorsList" class="d-none">
                            <div class="alert alert-warning p-2" style="font-size:0.82rem; max-height:150px; overflow-y:auto;">
                                <strong>⚠ Skipped Rows:</strong>
                                <ul class="mb-0 mt-1" id="errorItemsList"></ul>
                            </div>
                        </div>
                        <div id="importSuccessMsg" class="alert alert-success d-none" style="font-size:0.85rem;">
                            ✅ Import completed successfully! <a href="{{ route('product') }}" class="alert-link">Refresh product list</a>
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="modal-footer bg-light gap-2">
                    <a href="{{ route('products.template') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="las la-download me-1"></i>Download Template
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning fw-bold px-4" id="startImportBtn" disabled
                            style="min-width:130px;">
                        <i class="las la-file-upload me-1"></i> Start Import
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Import JS --}}
    <script>
    $(document).ready(function () {

        var selectedFile = null;

        var fileInput   = document.getElementById('csvFileInput');
        var dropZone    = document.getElementById('dropZone');
        var fileDisplay = document.getElementById('fileNameDisplay');
        var startBtn    = document.getElementById('startImportBtn');
        var progressEl  = document.getElementById('importProgress');
        var resultsEl   = document.getElementById('importResults');

        // ── Open modal via jQuery (Bootstrap 4) ──
        $('#openImportModalBtn').on('click', function () {
            $('#importModal').modal('show');
        });

        // ── File selection ──
        function setFile(file) {
            if (!file || !file.name.match(/\.(csv|txt)$/i)) {
                Swal.fire('Invalid File', 'Please select a valid CSV file.', 'warning');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire('File Too Large', 'File size exceeds 5 MB limit.', 'warning');
                return;
            }
            selectedFile = file;
            fileDisplay.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            fileDisplay.style.color = '#4f46e5';
            startBtn.disabled = false;
            resultsEl.classList.add('d-none');
        }

        $(fileInput).on('change', function () {
            if (this.files[0]) setFile(this.files[0]);
        });

        window.handleDrop = function (e) {
            e.preventDefault();
            dropZone.style.background = '#f5f3ff';
            if (e.dataTransfer.files[0]) setFile(e.dataTransfer.files[0]);
        };

        // ── Start Import ──
        $('#startImportBtn').on('click', function () {
            if (!selectedFile) return;

            var formData = new FormData();
            formData.append('csv_file', selectedFile);
            formData.append('_token', '{{ csrf_token() }}');

            startBtn.disabled = true;
            progressEl.classList.remove('d-none');
            resultsEl.classList.add('d-none');

            fetch('{{ route("products.import") }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                progressEl.classList.add('d-none');
                resultsEl.classList.remove('d-none');

                if (!data.success && !data.created && !data.updated) {
                    Swal.fire('Error', data.message || 'Import failed.', 'error');
                    startBtn.disabled = false;
                    return;
                }

                document.getElementById('resultCreated').textContent = data.created  || 0;
                document.getElementById('resultUpdated').textContent = data.updated  || 0;
                document.getElementById('resultSkipped').textContent = data.skipped  || 0;

                var errList  = document.getElementById('errorItemsList');
                var errBlock = document.getElementById('importErrorsList');
                var sucMsg   = document.getElementById('importSuccessMsg');

                errList.innerHTML = '';
                if (data.errors && data.errors.length > 0) {
                    data.errors.forEach(function(msg) {
                        var li = document.createElement('li');
                        li.textContent = msg;
                        errList.appendChild(li);
                    });
                    errBlock.classList.remove('d-none');
                } else {
                    errBlock.classList.add('d-none');
                }

                sucMsg.classList.remove('d-none');
                startBtn.disabled = false;
            })
            .catch(function () {
                progressEl.classList.add('d-none');
                Swal.fire('Server Error', 'Something went wrong. Please try again.', 'error');
                startBtn.disabled = false;
            });
        });

        // ── Reset on modal close ──
        $('#importModal').on('hidden.bs.modal', function () {
            selectedFile = null;
            fileDisplay.textContent = 'No file selected — Max 5 MB';
            fileDisplay.style.color = '';
            startBtn.disabled = true;
            progressEl.classList.add('d-none');
            resultsEl.classList.add('d-none');
            fileInput.value = '';
        });

    });
    </script>

        <div class="card-body">
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    ✅ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- ── FILTER BAR ── --}}
            <form method="GET" action="{{ route('product') }}" id="filterForm" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.78rem;">🔍 Search</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control form-control-sm"
                            placeholder="Item name or code...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.78rem;">📂 Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.78rem;">🏷 Brand</label>
                        <select name="brand_id" class="form-select form-select-sm">
                            <option value="">All Brands</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.78rem;">⚡ Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('product') }}" class="btn btn-outline-secondary btn-sm w-100">
                            ✕ Clear
                        </a>
                    </div>
                </div>
                @if(request()->hasAny(['search','category_id','brand_id','status']))
                    <div class="mt-2">
                        <small class="text-muted">
                            Showing <strong>{{ $products->total() }}</strong> result(s)
                            @if(request('search')) for "<strong>{{ request('search') }}</strong>" @endif
                        </small>
                    </div>
                @endif
            </form>

            {{-- Bulk Edit Checkboxes Controls Panel --}}
            <div class="bulk-edit-controls-card p-3 mb-3 border rounded bg-light d-flex align-items-center justify-content-between flex-wrap gap-3 shadow-sm" style="border-radius:10px;">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="fw-bold text-secondary" style="font-size:0.85rem;"><i class="las la-edit fs-5"></i> Bulk Edit Columns:</span>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input bulk-column-toggle" type="checkbox" id="toggleCategory" data-target="category">
                        <label class="form-check-label fw-semibold text-dark mb-0" for="toggleCategory" style="font-size:0.85rem; cursor:pointer;">Category</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input bulk-column-toggle" type="checkbox" id="toggleTradePrice" data-target="trade-price">
                        <label class="form-check-label fw-semibold text-dark mb-0" for="toggleTradePrice" style="font-size:0.85rem; cursor:pointer;">Purchase Price</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input bulk-column-toggle" type="checkbox" id="toggleRetailPrice" data-target="retail-price">
                        <label class="form-check-label fw-semibold text-dark mb-0" for="toggleRetailPrice" style="font-size:0.85rem; cursor:pointer;">Sale Price</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input bulk-column-toggle" type="checkbox" id="toggleDiscount" data-target="discount">
                        <label class="form-check-label fw-semibold text-dark mb-0" for="toggleDiscount" style="font-size:0.85rem; cursor:pointer;">Discount</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input bulk-column-toggle" type="checkbox" id="toggleMinQty" data-target="minqty">
                        <label class="form-check-label fw-semibold text-dark mb-0" for="toggleMinQty" style="font-size:0.85rem; cursor:pointer;">Min Qty</label>
                    </div>
                </div>
                <div>
                    <button type="submit" form="bulkEditForm" id="bulkSaveBtn" class="btn btn-primary btn-sm px-4 fw-bold d-none shadow-sm" style="border-radius:6px; background-color:#4f46e5 !important; border-color:#4f46e5 !important;">
                        <i class="las la-save fs-5"></i> Save Changes
                    </button>
                </div>
            </div>

            <form id="bulkEditForm" method="POST" action="{{ route('products.bulk-update') }}">
                @csrf
                <div class="table-responsive">
                    <table id="productTable" class="table table-striped table-bordered align-middle nowrap" style="width:100%">

                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>#</th>
                            <th>Image</th>
                            <th>Item Details</th>
                            <th>Stock</th>
                            <th>Purchase Price</th>
                            <th>Sale Price</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $key => $product)
                            <tr id="product-row-{{ $product->id }}" class="{{ $product->is_active ? '' : 'table-secondary opacity-75' }}">
                                <td><input type="checkbox" class="selectProduct" value="{{ $product->id }}"></td>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    @if ($product->image)
                                        <img src="{{ asset('uploads/products/' . $product->image) }}" alt="Product"
                                            width="50" height="50" class="rounded border">
                                    @else
                                        <span class="badge bg-secondary">No Img</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $product->item_name }}</strong><br>
                                    <small class="text-muted">{{ $product->item_code }} | {{ $product->category_relation->name ?? '-' }} | {{ $product->brand->name ?? '-' }}</small>
                                </td>
                                @php
                                    $stockPieces = (float) ($product->warehouse_stocks_sum_total_pieces ?? 0);
                                    $ppb = $product->pieces_per_box > 0 ? $product->pieces_per_box : 1;
                                    if (($product->size_mode === 'by_cartons' || $product->size_mode === 'by_size') && $ppb > 1) {
                                        $boxes = floor($stockPieces / $ppb);
                                        $loose = $stockPieces % $ppb;
                                        $stockDisplay = $loose > 0 ? "{$boxes}.{$loose} <small class='text-muted'>(Box.Loose)</small>" : "{$boxes} <small class='text-muted'>Boxes</small>";
                                    } else {
                                        $stockDisplay = "{$stockPieces} <small class='text-muted'>Pcs</small>";
                                    }
                                    $tradePrice = 0;
                                    $retailPrice = 0;
                                    if ($product->size_mode === 'by_size') {
                                        $m2PerPiece = ($product->height * $product->width) / 10000;
                                        $tradePrice = $m2PerPiece * (float)$product->purchase_price_per_m2;
                                        $retailPrice = $m2PerPiece * (float)$product->price_per_m2;
                                    } else {
                                        $tradePrice = (float)$product->purchase_price_per_piece;
                                        $retailPrice = (float)$product->sale_price_per_piece ?: (float)$product->sale_price_per_box;
                                    }
                                @endphp
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.85rem;">{!! $stockDisplay !!}</span>
                                </td>
                                <td class="text-secondary">Rs. {{ number_format($tradePrice, 2) }}</td>
                                <td class="fw-bold text-success">Rs. {{ number_format($retailPrice, 2) }}</td>
                                <td class="text-center">
                                    @if($product->is_active)
                                        <span class="badge bg-success" id="status-badge-{{ $product->id }}">Active</span>
                                    @else
                                        <span class="badge bg-danger" id="status-badge-{{ $product->id }}">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-info viewProductBtn text-white"
                                        data-id="{{ $product->id }}" title="View Detail">
                                        👁 View
                                    </button>

                                    @if (auth()->user()->can('products.edit') || auth()->user()->email === 'admin@admin.com')
                                        <a href="{{ route('products.edit', $product->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            ✏ Edit
                                        </a>
                                    @endif

                                    <a href="{{ route('generate-barcode-image', $product->id) }}"
                                        class="btn btn-sm btn-outline-success">
                                        🏷 Barcode
                                    </a>

                                    @if (auth()->user()->can('products.edit') || auth()->user()->email === 'admin@admin.com')
                                        <button type="button"
                                            class="btn btn-sm {{ $product->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} toggle-active-btn"
                                            data-id="{{ $product->id }}"
                                            data-active="{{ $product->is_active ? '1' : '0' }}"
                                            data-name="{{ $product->item_name }}"
                                            title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">
                                            {{ $product->is_active ? '🔴' : '🟢' }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </form>
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} products</small>
                {{ $products->links() }}
            </div>
        </div>
    </div>

    {{-- add product modal --}}

    <div class="modal fade bd-example-modal-lg" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger">Please use the main "Add Product" page for the new per-m² flow.</p>
                </div>
            </div>
        </div>
    </div>



    <!-- Variant View Modal -->
    <div class="modal fade" id="productViewModal" tabindex="-1" aria-labelledby="productViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom bg-white">
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="productViewModalLabel">
                            <span id="view_item_name">Product</span>
                        </h5>
                        <small class="text-muted" id="view_item_subtext">CODE</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="modalLoadingSpinner" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="modalContentRow" class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.9rem;">
                            <thead class="table-light text-secondary small text-uppercase">
                                <tr>
                                    <th class="text-start ps-3">Variant Name</th>
                                    <th>Size</th>
                                    <th>Color</th>
                                    <th>Stock</th>
                                    <th>Sale Price</th>
                                    <th>Purch Price</th>
                                    <th>Alert</th>
                                    <th class="text-end pe-3">Barcode</th>
                                </tr>
                            </thead>
                            <tbody id="variantTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-white py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- View Modal JS --}}
    <script>
        $(document).on('click', '.viewProductBtn', function() {
            let productId = $(this).data('id');
            $('#modalContentRow').addClass('d-none');
            $('#modalLoadingSpinner').removeClass('d-none');
            $('#productViewModal').modal('show');

            $.ajax({
                url: "/productview/" + productId,
                type: "GET",
                success: function(product) {
                    $('#modalLoadingSpinner').addClass('d-none');
                    $('#modalContentRow').removeClass('d-none');

                    $('#view_item_name').text(product.item_name ?? 'Unknown');
                    $('#view_item_subtext').text((product.item_code ?? '') + ' | ' + (product.category_relation?.name ?? '') + ' | ' + (product.brand?.name ?? ''));

                    let tbody = $('#variantTableBody');
                    tbody.empty();

                    let colorList = ['-'];
                    let variants = [];

                    if (product.color) {
                        try {
                            let parsed = JSON.parse(product.color);
                            if (Array.isArray(parsed) && parsed.length > 0) {
                                if (typeof parsed[0] === 'object') {
                                    variants = parsed;
                                } else {
                                    colorList = parsed;
                                }
                            } else if (typeof parsed === 'string') {
                                colorList = [parsed];
                            }
                        } catch (e) { colorList = [product.color]; }
                    }

                    let sizeStr = '-';
                    if (product.size_mode === 'by_size') {
                        sizeStr = (product.height || 0) + ' x ' + (product.width || 0) + ' cm';
                    }

                    let stock = product.calculated_total_stock_qty ?? 0;
                    let alertQtyDefault = (product.alert_carton_quantity != null) ? product.alert_carton_quantity + ' ' : '-';
                    let salePrice = product.size_mode === 'by_size' ? product.price_per_m2 : (product.sale_price_per_piece || product.sale_price_per_box || 0);
                    let purchPrice = product.size_mode === 'by_size' ? product.purchase_price_per_m2 : (product.purchase_price_per_piece || 0);
                    let priceLabel = product.size_mode === 'by_size' ? '/m²' : '/pc';

                    if (variants.length > 0) {
                        variants.forEach((v, index) => {
                            let barcode = v.barcode || (product.barcode_path ?? product.item_code);
                            let colorBadge = (v.color && v.color !== '-') ? `<span class="badge bg-secondary">${v.color}</span>` : '<span class="text-muted">-</span>';
                            let isLow = v.stock > 0 && v.alert != null && v.stock <= v.alert;
                            let stockBadge = `<span class="badge ${isLow ? 'bg-danger' : 'bg-success'} bg-opacity-10 text-${isLow ? 'danger' : 'success'} border border-${isLow ? 'danger' : 'success'}-subtle px-2 py-1">${v.stock}</span>`;
                            let alertQty = (v.alert != null && v.alert != 0) ? v.alert + '' : '-';

                            tbody.append(`
                                <tr>
                                    <td class="text-start ps-3 fw-semibold">${v.name || product.item_name}</td>
                                    <td>${v.size || '-'}</td>
                                    <td>${colorBadge}</td>
                                    <td>${stockBadge}</td>
                                    <td class="fw-bold text-success">Rs. ${parseFloat(v.sale_price||0).toFixed(2)} <small class="text-muted fw-normal">${priceLabel}</small></td>
                                    <td class="text-muted">Rs. ${parseFloat(v.purch_price||0).toFixed(2)} <small>${priceLabel}</small></td>
                                    <td><span class="badge bg-light text-danger border">${alertQty}</span></td>
                                    <td class="text-end pe-3"><code class="bg-light px-2 py-1 rounded border text-dark small">${barcode}</code></td>
                                </tr>
                            `);
                        });
                    } else {
                        colorList.forEach((color, index) => {
                            let barcode = (product.barcode_path ?? product.item_code ?? '') + (index > 0 ? '-' + String(index + 1).padStart(2, '0') : '');
                            let colorBadge = (color && color !== '-') ? `<span class="badge bg-secondary">${color}</span>` : '<span class="text-muted">-</span>';
                            let isLow = stock > 0 && product.alert_carton_quantity != null && stock <= product.alert_carton_quantity;
                            let stockBadge = `<span class="badge ${isLow ? 'bg-danger' : 'bg-success'} bg-opacity-10 text-${isLow ? 'danger' : 'success'} border border-${isLow ? 'danger' : 'success'}-subtle px-2 py-1">${stock}</span>`;

                            tbody.append(`
                                <tr>
                                    <td class="text-start ps-3 fw-semibold">${product.item_name}</td>
                                    <td>${sizeStr}</td>
                                    <td>${colorBadge}</td>
                                    <td>${stockBadge}</td>
                                    <td class="fw-bold text-success">Rs. ${parseFloat(salePrice||0).toFixed(2)} <small class="text-muted fw-normal">${priceLabel}</small></td>
                                    <td class="text-muted">Rs. ${parseFloat(purchPrice||0).toFixed(2)} <small>${priceLabel}</small></td>
                                    <td><span class="badge bg-light text-danger border">${alertQtyDefault}</span></td>
                                    <td class="text-end pe-3"><code class="bg-light px-2 py-1 rounded border text-dark small">${barcode}</code></td>
                                </tr>
                            `);
                        });
                    }
                },
                error: function() {
                    $('#modalLoadingSpinner').addClass('d-none');
                    Swal.fire('Error', 'Could not fetch product details.', 'error');
                }
            });
        });
    </script>

    {{-- Toggle Active JS --}}
    <script>
        $(document).on('click', '.toggle-active-btn', function () {
            const btn = $(this);
            const productId = btn.data('id');
            const isActive = btn.data('active') == '1';
            const productName = btn.data('name');
            const actionText = isActive ? 'Deactivate' : 'Activate';
            const actionIcon = isActive ? 'warning' : 'success';

            Swal.fire({
                title: actionText + ' Product?',
                html: `<b>${productName}</b><br><small class="text-muted">${isActive ? 'Product will be hidden from Sale/Purchase forms.' : 'Product will be visible in Sale/Purchase forms.'}</small>`,
                icon: actionIcon,
                showCancelButton: true,
                confirmButtonText: 'Yes, ' + actionText,
                confirmButtonColor: isActive ? '#dc3545' : '#28a745',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/product/${productId}/toggle-active`,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (res) {
                            if (res.success) {
                                const row = $(`#product-row-${productId}`);
                                const badge = $(`#status-badge-${productId}`);

                                if (res.is_active) {
                                    // Activated
                                    row.removeClass('table-secondary opacity-75');
                                    badge.removeClass('bg-danger').addClass('bg-success').text('Active');
                                    btn.removeClass('btn-outline-success').addClass('btn-outline-danger')
                                       .text('🔴 Deactivate').data('active', '1')
                                       .attr('title', 'Deactivate Product');
                                } else {
                                    // Deactivated
                                    row.addClass('table-secondary opacity-75');
                                    badge.removeClass('bg-success').addClass('bg-danger').text('Inactive');
                                    btn.removeClass('btn-outline-danger').addClass('btn-outline-success')
                                       .text('🟢 Activate').data('active', '0')
                                       .attr('title', 'Activate Product');
                                }

                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: res.message,
                                    showConfirmButton: false,
                                    timer: 2500,
                                    timerProgressBar: true,
                                });
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Could not update product status.', 'error');
                        }
                    });
                }
            });
        });
    </script>

    {{-- product model --}}
    <script>
        $(document).on('click', '.viewProductBtn', function() {
            let productId = $(this).data('id');

            // 1. Reset & Loading State
            $('#modalContentRow').addClass('d-none');
            $('#modalLoadingSpinner').removeClass('d-none');
            $('#productViewModal').modal('show');

            $.ajax({
                url: "/productview/" + productId,
                type: "GET",
                success: function(product) {

                    // 2. Hide Spinner, Show Content
                    $('#modalLoadingSpinner').addClass('d-none');
                    $('#modalContentRow').removeClass('d-none');

                    // --- Basic ---
                    $('#view_item_name').text(product.item_name ?? 'Unknown Product');
                    $('#view_item_code').text(product.item_code ?? 'N/A');
                    $('#view_cat_sub').text((product.category_relation?.name ?? '') + (product
                        .sub_category_relation ? ' • ' + product.sub_category_relation.name : ''
                    ));
                    $('#view_brand_model').text((product.brand?.name ?? '-') + (product.model ? ' / ' +
                        product.model : ''));

                    $('#view_created_at').text(product.created_at ? new Date(product.created_at)
                        .toLocaleDateString() : '-');

                    // --- Image ---
                    if (product.image) {
                        $('#view_image_preview').attr('src', '/uploads/products/' + product.image)
                            .removeClass('d-none');
                        $('#view_image_placeholder').addClass('d-none');
                    } else {
                        $('#view_image_preview').addClass('d-none');
                        $('#view_image_placeholder').removeClass('d-none');
                    }

                    // --- Colors ---
                    if (product.color) {
                        try {
                            let colors = JSON.parse(product.color);
                            $('#view_color').text(Array.isArray(colors) ? colors.join(', ') : colors);
                        } catch (e) {
                            $('#view_color').text(product.color);
                        }
                    } else {
                        $('#view_color').text('-');
                    }

                    // --- Mode & Layout Switching ---
                    let mode = product.size_mode ?? 'by_size';

                    // Defaults
                    $('#sec_by_size, #sec_packing, #sec_by_piece').addClass('d-none');

                    let calcBoxes = product.calculated_boxes_quantity ?? 0;
                    let calcLoose = product.calculated_loose_pieces ?? 0;
                    let calcTotal = product.calculated_total_stock_qty ?? 0;

                    let salePrice = 0;
                    let purchPrice = 0;
                    let estSaleVal = 0;
                    let estPurchVal = 0;

                    if (mode === 'by_size') {
                        $('#view_size_mode_badge').text('By Size').removeClass('bg-info bg-warning')
                            .addClass('bg-light text-primary border-primary');
                        $('#sec_by_size').removeClass('d-none');

                        // Fill Size Data
                        $('#view_dimensions').text((product.height ?? 0) + ' x ' + (product.width ??
                            0));
                        let m2Piece = ((product.height * product.width) / 10000).toFixed(4);
                        $('#view_m2_piece').text(m2Piece);
                        $('#view_boxes_qty_size').text(calcBoxes); // Box count for Size mode
                        $('#view_pcs_box_size').text(product.pieces_per_box ?? 0);
                        $('#view_total_m2').text(parseFloat(product.total_m2 ?? 0).toFixed(2));

                        // Stock
                        $('#view_total_stock_qty').text(calcTotal);

                        // Price Labels
                        $('#lbl_price_unit').text('Price per m²');
                        $('#lbl_purch_unit').text('Cost per m²');
                        salePrice = product.price_per_m2;
                        purchPrice = product.purchase_price_per_m2;

                        estSaleVal = (product.total_m2 ?? 0) * calcBoxes * salePrice;
                        estPurchVal = (product.total_m2 ?? 0) * calcBoxes * purchPrice;

                    } else if (mode === 'by_cartons') {
                        $('#view_size_mode_badge').text('By Box').removeClass(
                            'bg-light text-primary border-primary bg-warning').addClass(
                            'bg-info text-white border-0');
                        $('#sec_packing').removeClass('d-none');

                        $('#view_boxes_qty').text(calcBoxes);
                        $('#view_loose_pcs').text(calcLoose);
                        $('#view_pcs_box').text(product.pieces_per_box ?? '-');

                        // Stock
                        $('#view_total_stock_qty').text(calcTotal);

                        // Price Labels
                        $('#lbl_price_unit').text('Price per Box');
                        $('#lbl_purch_unit').text('Cost per Piece');
                        salePrice = product.sale_price_per_box;
                        purchPrice = product.purchase_price_per_piece;

                        // Calc Value
                        // Sale Value: Boxes * SalePricePerBox + Loose * (SalePricePerBox/PcsPerBox)
                        let ppb = product.pieces_per_box > 0 ? product.pieces_per_box : 1;
                        let pricePerPieceScale = salePrice / ppb;
                        estSaleVal = calcTotal * pricePerPieceScale;
                        estPurchVal = calcTotal * purchPrice;

                    } else { // by_pieces
                        $('#view_size_mode_badge').text('By Piece').removeClass(
                            'bg-light text-primary border-primary bg-info text-white').addClass(
                            'bg-warning text-dark border-0');
                        $('#sec_by_piece').removeClass('d-none');

                        // Stock
                        $('#view_total_stock_qty').text(calcTotal);

                        // Price Labels
                        $('#lbl_price_unit').text('Price per Piece');
                        $('#lbl_purch_unit').text('Cost per Piece');
                        salePrice = product.sale_price_per_box;
                        purchPrice = product.purchase_price_per_piece;

                        estSaleVal = calcTotal * salePrice;
                        estPurchVal = calcTotal * purchPrice;
                    }

                    // Format Financials
                    $('#view_price_unit').text('Rs. ' + parseFloat(salePrice || 0).toFixed(2));
                    $('#view_purch_unit').text('Rs. ' + parseFloat(purchPrice || 0).toFixed(2));
                    $('#view_purchase_discount').text((product.purchase_discount_percent ?? 0) + '%');
                    $('#view_sale_discount').text((product.sale_discount_percent ?? 0) + '%');
                    $('#view_sale_total').text('Rs. ' + parseFloat(estSaleVal || 0).toLocaleString(
                        'en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                    $('#view_purch_total').text('Rs. ' + parseFloat(estPurchVal || 0).toLocaleString(
                        'en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));

                    $('#view_alert_quantity').text(product.alert_carton_quantity !== null && product.alert_carton_quantity !== undefined ? product.alert_carton_quantity + ' Ctns' : 'Not Set');

                    $('#productViewModal').modal('show');
                },
                error: function() {
                    $('#modalLoadingSpinner').addClass('d-none');
                    Swal.fire('Error', 'Could not fetch details', 'error');
                }
            });
        });
    </script>


    <script>
        $(document).ready(function() {

            // Select/Deselect all checkboxes
            $('#selectAll').click(function() {
                $('.selectProduct').prop('checked', this.checked);
            });

            // On "Create Discount" click
            $('#createDiscountBtn').click(function() {
                var selected = [];
                $('.selectProduct:checked').each(function() {
                    selected.push($(this).val());
                });

                if (selected.length === 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Please select at least one product!",

                    });
                    return;
                }

                // Redirect with product IDs as query param
                window.location.href = "{{ route('discount.create') }}" + "?products=" + selected.join(
                    ',');
            });
        });
    </script>

    <script>
        $(document).ready(function() {

            function debounce(func, delay) {
                let timer;
                return function(...args) {
                    clearTimeout(timer);
                    timer = setTimeout(() => func.apply(this, args), delay);
                }
            }

            // DataTable: no built-in search (we use server-side filter bar)
            let table = $('#productTable').DataTable({
                responsive: true,
                paging: false,
                ordering: true,
                info: false,
                order: [[1, 'asc']],
                dom: 'rt',  // only table rows, no search/filter UI
                columnDefs: [{
                    targets: [0, 11],
                    orderable: false,
                    searchable: false
                }]
            });

            // Column toggling logic
            $('.bulk-column-toggle').on('change', function() {
                let target = $(this).data('target');
                let isChecked = $(this).is(':checked');

                if (isChecked) {
                    $(`.${target}-view-mode`).addClass('d-none');
                    $(`.${target}-edit-mode`).removeClass('d-none');
                    $(`.${target}-edit-mode input, .${target}-edit-mode select`).removeAttr('disabled');
                } else {
                    $(`.${target}-view-mode`).removeClass('d-none');
                    $(`.${target}-edit-mode`).addClass('d-none');
                    $(`.${target}-edit-mode input, .${target}-edit-mode select`).attr('disabled', 'disabled');
                }

                // Show/hide Bulk Save Button
                if ($('.bulk-column-toggle:checked').length > 0) {
                    $('#bulkSaveBtn').removeClass('d-none');
                } else {
                    $('#bulkSaveBtn').addClass('d-none');
                }
            });

            // AJAX form submission
            $('#bulkEditForm').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let btn = $('#bulkSaveBtn');
                let originalHtml = btn.html();

                btn.html('<i class="las la-spinner la-spin"></i> Saving...').prop('disabled', true);

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Something went wrong.'
                            });
                            btn.html(originalHtml).prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON;
                        let msg = errors && errors.message ? errors.message : 'Server error occurred.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                        btn.html(originalHtml).prop('disabled', false);
                    }
                });
            });

        });
    </script>

    {{-- Toggle Active JS --}}
    <script>
        $(document).on('click', '.toggle-active-btn', function () {
            const btn = $(this);
            const productId = btn.data('id');
            const isActive = btn.data('active') == '1';
            const productName = btn.data('name');
            const actionText = isActive ? 'Deactivate' : 'Activate';
            const actionIcon = isActive ? 'warning' : 'success';

            Swal.fire({
                title: actionText + ' Product?',
                html: `<b>${productName}</b><br><small class="text-muted">${isActive ? 'Product will be hidden from Sale/Purchase forms.' : 'Product will be visible in Sale/Purchase forms.'}</small>`,
                icon: actionIcon,
                showCancelButton: true,
                confirmButtonText: 'Yes, ' + actionText,
                confirmButtonColor: isActive ? '#dc3545' : '#28a745',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/product/${productId}/toggle-active`,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (res) {
                            if (res.success) {
                                const row = $(`#product-row-${productId}`);
                                const badge = $(`#status-badge-${productId}`);

                                if (res.is_active) {
                                    row.removeClass('table-secondary opacity-75');
                                    badge.removeClass('bg-danger').addClass('bg-success').text('Active');
                                    btn.removeClass('btn-outline-success').addClass('btn-outline-danger')
                                       .text('🔴').data('active', '1')
                                       .attr('title', 'Deactivate Product');
                                } else {
                                    row.addClass('table-secondary opacity-75');
                                    badge.removeClass('bg-success').addClass('bg-danger').text('Inactive');
                                    btn.removeClass('btn-outline-danger').addClass('btn-outline-success')
                                       .text('🟢').data('active', '0')
                                       .attr('title', 'Activate Product');
                                }

                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: res.message,
                                    showConfirmButton: false,
                                    timer: 2500,
                                    timerProgressBar: true,
                                });
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Could not update product status.', 'error');
                        }
                    });
                }
            });
        });
    </script>

    <!-- DataTables CSS -->
@endsection
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let cartonQuantityInput = document.getElementById("carton_quantity");
        let piecesPerCartonInput = document.getElementById("pieces_per_carton");
        let initialStockInput = document.getElementById("initial_stock");

        if (cartonQuantityInput && piecesPerCartonInput && initialStockInput) {
            function updateInitialStock() {
                let cartonQuantity = parseInt(cartonQuantityInput.value) || 0;
                let piecesPerCarton = parseInt(piecesPerCartonInput.value) || 0;
                initialStockInput.value = cartonQuantity * piecesPerCarton;
            }

            cartonQuantityInput.addEventListener("input", updateInitialStock);
            piecesPerCartonInput.addEventListener("input", updateInitialStock);
        }
    });


    $(document).ready(function() {
        // Add Product Modal: Fetch Subcategories on Category Change
        $('#categorySelect').change(function() {
            var categoryId = $(this).val();

            $('#subCategorySelect').html('<option value="">Loading...</option>');

            if (categoryId) {
                $.ajax({
                    url: "/get-subcategories/" + categoryId,

                    type: "GET",
                    data: {
                        category_id: categoryId
                    },
                    success: function(data) {
                        $('#subCategorySelect').html(
                            '<option value="">Select Sub-Category</option>');
                        $.each(data, function(key, subCategory) {
                            $('#subCategorySelect').append('<option value="' +
                                subCategory.id + '">' +
                                subCategory.name + '</option>');
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                $('#subCategorySelect').html('<option value="">Select Sub-Category</option>');
            }
        });

        // Edit Product Modal: Fetch Subcategories when Category is Changed
        $('#edit_category').change(function() {
            var categoryId = $(this).val();
            $('#edit_sub_category').html('<option value="">Loading...</option>');

            if (categoryId) {
                $.ajax({
                    url: "/get-subcategories/" + categoryId,

                    type: "GET",
                    data: {
                        category_id: categoryId
                    },
                    success: function(data) {
                        $('#edit_sub_category').html(
                            '<option value="">Select Sub-Category</option>');
                        $.each(data, function(key, subCategory) {
                            $('#edit_sub_category').append('<option value="' +
                                subCategory.sub_category_name + '">' +
                                subCategory.sub_category_name + '</option>');
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                $('#edit_sub_category').html('<option value="">Select Sub-Category</option>');
            }
        });
    });
</script>
