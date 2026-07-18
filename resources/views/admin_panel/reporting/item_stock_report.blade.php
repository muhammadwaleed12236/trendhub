@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <div class="page-header row mb-3">
                <div class="page-title col-lg-6">
                    <h4>Item Stock Report</h4>
                    <h6>Track initial, purchased, sold and balance per product</h6>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form id="stockFilterForm" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" id="category_id" class="form-control">
                                <option value="all">-- All Categories --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Product</label>
                            <select name="product_id" id="product_id" class="form-control select2">
                                <option value="all">-- All Products --</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-danger w-100">Search</button>
                        </div>

                        <div class="col-md-4 text-end">
                            <button type="button" id="btnExportCsv" class="btn btn-outline-secondary">Export CSV</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="row g-3 mb-3 animate__animated animate__fadeIn" id="kpiSummaryRow">
                <!-- Grand Stock Value -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); border-radius: 12px; transition: transform 0.25s ease;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Grand Stock Value</h6>
                                    <h3 class="fw-bold mb-0" id="kpiGrandStockValue">0.00</h3>
                                </div>
                                <div class="bg-white bg-opacity-20 rounded-3 p-2">
                                    <i class="fas fa-coins text-white fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Current Stock (Pcs) -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; transition: transform 0.25s ease;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Current Stock</h6>
                                    <h3 class="fw-bold mb-0" id="kpiTotalStock">0</h3>
                                </div>
                                <div class="bg-white bg-opacity-20 rounded-3 p-2">
                                    <i class="fas fa-cubes text-white fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Sold Amount -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; transition: transform 0.25s ease;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Sold Amount</h6>
                                    <h3 class="fw-bold mb-0" id="kpiSoldAmount">0.00</h3>
                                </div>
                                <div class="bg-white bg-opacity-20 rounded-3 p-2">
                                    <i class="fas fa-shopping-cart text-white fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Returned Qty -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 12px; transition: transform 0.25s ease;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 text-uppercase fw-bold mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Returned Qty</h6>
                                    <h3 class="fw-bold mb-0" id="kpiReturnedQty">0</h3>
                                </div>
                                <div class="bg-white bg-opacity-20 rounded-3 p-2">
                                    <i class="fas fa-undo text-white fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="loader" style="display:none;text-align:center;margin-bottom:10px;">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>

                     <div class="table-responsive">
                        <table id="stockTable" class="table table-striped table-bordered" style="width:100%; table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th style="width: 3%;">#</th>
                                    <th style="width: 8%;">Item Code</th>
                                    <th style="width: 25%;">Item Name</th>
                                    <th style="width: 8%;">Initial Stock</th>
                                    <th style="width: 6%;">Purchased Qty</th>
                                    <th style="width: 6%;">Purchased Amount</th>
                                    <th style="width: 7%;">Sold Qty</th>
                                    <th style="width: 7%;">Sale Returned Qty</th>
                                    <th style="width: 7%;">Purch Returned Qty</th>
                                    <th style="width: 7%;">Sold Amount</th>
                                    <th style="width: 5%;">Cartons</th>
                                    <th style="width: 5%;">Loose Pcs</th>
                                    <th style="width: 8%;">Current Stock (Pcs)</th>
                                    <th style="width: 5%;">Avg Price</th>
                                    <th style="width: 7%;">Stock Value</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                                <!-- Filled by AJAX -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="14" class="text-end fw-bold" style="background-color:#f1f5f9;">Grand Stock Value:</th>
                                    <th id="grandStockValue" class="text-end fw-bold text-primary" style="background-color:#f1f5f9;">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#product_id').select2({
        placeholder: "-- All Products --",
        allowClear: true,
        width: '100%',
        ajax: {
            url: "{{ route('products.search') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            id: item.id,
                            text: item.item_code + ' - ' + item.item_name
                        }
                    })
                };
            },
            cache: true
        }
    });

    // To allow picking "all" when clearing
    $('#product_id').on('select2:unselect', function (e) {
        $(this).val('all').trigger('change');
    });

    var stockTable = $('#stockTable').DataTable({
        paging: true,
        searching: true,
        info: true,
        ordering: true,
        autoWidth: false,
        columns: [
            { data: 'index' },
            { data: 'item_code' },
            { data: 'item_name' },
            { data: 'initial_stock' },
            { data: 'purchased' },
            { data: 'purchase_amount' },
            { data: 'sold' },
            { data: 'returned_qty' },
            { data: 'purch_returned_qty' },
            { data: 'sale_amount' },
            { data: 'cartons' },
            { data: 'loose' },
            { data: 'balance' },
            { data: 'average_price' },
            { data: 'stock_value' }
        ]
    });

    function renderRows(rows, grandTotal) {
        if ($.fn.DataTable.isDataTable('#stockTable')) {
            stockTable.clear().draw();
        }

        let totalCurrentStock = 0;
        let totalSoldAmount = 0;
        let totalReturnedQty = 0;
        let totalStockValue = 0;

        rows.forEach(function(r, idx) {
            totalCurrentStock += parseFloat(r.balance) || 0;
            totalSoldAmount += parseFloat(r.sale_amount) || 0;
            totalReturnedQty += parseFloat(r.returned_qty) || 0;
            totalStockValue += parseFloat(r.stock_value) || 0;

            stockTable.row.add({
                index: idx + 1,
                item_code: r.item_code,
                item_name: r.item_name,
                initial_stock: parseFloat(r.initial_stock).toFixed(2),
                purchased: parseFloat(r.purchased).toFixed(2),
                purchase_amount: parseFloat(r.purchase_amount).toFixed(2),
                sold: parseFloat(r.sold).toFixed(2),
                returned_qty: parseFloat(r.returned_qty).toFixed(2),
                purch_returned_qty: parseFloat(r.purch_returned_qty).toFixed(2),
                sale_amount: parseFloat(r.sale_amount).toFixed(2),
                cartons: r.cartons,
                loose: parseFloat(r.loose).toFixed(2),
                balance: parseFloat(r.balance).toFixed(2),
                average_price: parseFloat(r.average_price).toFixed(2),
                stock_value: parseFloat(r.stock_value).toFixed(2)
            }).draw(false);
        });

        // Format to standard commas using toLocaleString
        const formattedStockValue = totalStockValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        $('#grandStockValue').text(formattedStockValue);
        
        // Update KPI card values with beautiful formats
        $('#kpiGrandStockValue').text(formattedStockValue);
        $('#kpiTotalStock').text(totalCurrentStock.toLocaleString('en-US', { maximumFractionDigits: 0 }));
        $('#kpiSoldAmount').text(totalSoldAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#kpiReturnedQty').text(totalReturnedQty.toLocaleString('en-US', { maximumFractionDigits: 0 }));
    }

    $('#btnSearch').on('click', function() { fetchReport(); });
    $('#product_id').on('keypress', function(e){ if(e.key==='Enter'){ e.preventDefault(); fetchReport(); } });

    function fetchReport() {
        var productId = $('#product_id').val();
        var categoryId = $('#category_id').val();
        $('#loader').show();
        $.ajax({
            url: "{{ route('report.item_stock.fetch') }}",
            type: "POST",
            data: { 
                _token: "{{ csrf_token() }}", 
                product_id: productId,
                category_id: categoryId
            },
            success: function(response) {
                $('#loader').hide();
                if (response.data && response.data.length) {
                    renderRows(response.data, response.grand_total);
                } else {
                    renderRows([], 0);
                }
            },
            error: function(xhr, status, err) {
                $('#loader').hide();
                alert('Error fetching report. See console.');
                console.error(xhr.responseText || err);
            }
        });
    }

    $('#btnExportCsv').on('click', function() {
        var productId = $('#product_id').val();
        var categoryId = $('#category_id').val();
        $('#loader').show();
        $.ajax({
            url: "{{ route('report.item_stock.fetch') }}",
            type: "POST",
            data: { 
                _token: "{{ csrf_token() }}", 
                product_id: productId,
                category_id: categoryId
            },
            success: function(response) {
                $('#loader').hide();
                if (!response.data || !response.data.length) { alert('No data to export'); return; }

                var csv = 'Item Code,Item Name,Initial Stock,Purchased Qty,Purchased Amount,Sold Qty,Sold Amount,Balance (Pcs),Cartons,Loose Pcs,Avg Price,Stock Value\n';
                response.data.forEach(function(r){
                    csv += `"${r.item_code}","${r.item_name}",${r.initial_stock},${r.purchased},${r.purchase_amount},${r.sold},${r.sale_amount},${r.balance},${r.cartons},${r.loose},${r.average_price},${r.stock_value}\n`;
                });

                var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'item_stock_report.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            },
            error: function() { $('#loader').hide(); alert('Export failed'); }
        });
    });

    // Initial load
    fetchReport();
});
</script>
