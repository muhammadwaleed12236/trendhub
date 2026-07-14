@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <div class="page-header row mb-3">
                <div class="page-title col-lg-6">
                    <h4>Sale Report</h4>
                    <h6>View Sales by date range with details</h6>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form id="SaleFilterForm" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Start Date & Time</label>
                            <input type="datetime-local" name="start_date" id="start_date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date & Time</label>
                            <input type="datetime-local" name="end_date" id="end_date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search Product / Size / Invoice</label>
                            <input type="text" id="search_product" class="form-control" placeholder="Search by name, size, invoice...">
                        </div>
                        <div class="col-md-1.5">
                            <button type="button" id="btnSearch" class="btn btn-primary w-100">Search</button>
                        </div>
                        <div class="col-md-1.5 text-end">
                            <button type="button" id="btnExportCsv" class="btn btn-danger w-100">Export CSV</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="loader" style="display:none;text-align:center;margin-bottom:10px;">
                        <div class="spinner-border" role="status"></div>
                    </div>

                    <div class="table-responsive">
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered" id="saleReport">
                                <thead class="bg-gray">
                                    <tr>
                                        <th>#</th>
                                        <th>Date & Time</th>
                                        <th>Invoice</th>
                                        <th>Customer</th>
                                        <th>Reference</th>
                                        <th>Products</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Net</th>
                                        <th>Returns</th>
                                    </tr>
                                </thead>
                                <tbody id="saleBody"></tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).on('click', '#btnSearch', function() {
        let start = $('#start_date').val();
        let end = $('#end_date').val();
        $('#search_product').val(''); // Clear search input on new range search

        $("#loader").show();
        $.ajax({
            url: "{{ route('report.sale.fetch') }}",
            type: "GET",
            data: {
                start_date: start,
                end_date: end
            },
            success: function(res) {
                $("#loader").hide();
                let html = "";
                let grandQty = 0,
                    grandTotal = 0,
                    grandNet = 0,
                    grandReturn = 0;

                res.forEach((s, i) => {
                    let products = s.product.split(',').join('<br>');
                    let qtyArr = s.qty.split(',');
                    let qtyPiecesArr = s.total_pieces ? s.total_pieces.split(',') : (s.qty_decimal ? s.qty_decimal.split(',') : qtyArr);
                    let price = s.per_price.split(',').join('<br>');
                    let total = s.per_total.split(',').join('<br>');

                    // qty total per row (calculation only using pieces)
                    let rowQty = qtyPiecesArr.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
                    grandQty += rowQty;

                    // calculate totals
                    let rowTotal = s.per_total.split(',').reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
                    grandTotal += parseFloat(rowTotal);
                    grandNet += parseFloat(s.total_net);

                    // returns
                    let returnHtml = "";
                    let returnTotal = 0;
                    if (s.returns && s.returns.length > 0) {
                        s.returns.forEach(r => {
                            returnHtml += `${r.product} (${r.qty}) - ${r.per_total}<br>`;
                            returnTotal += parseFloat(r.per_total);
                        });
                    }
                    grandReturn += returnTotal;

                    html += `<tr data-qty="${rowQty}" data-total="${rowTotal}" data-net="${s.total_net}" data-return="${returnTotal}">
                    <td>${i+1}</td>
                    <td>${s.created_at}</td>
                    <td>INVSLE-${s.id}</td>
                    <td>${s.customer_name ?? '-'}</td>
                    <td>${s.reference}</td>
                    <td>${products}</td>
                    <td>${qtyArr.join('<br>')}</td>
                    <td>${price}</td>
                    <td>${total}</td>
                    <td>${parseFloat(s.total_net).toFixed(2)}</td>
                    <td>${returnHtml || '-'}</td>
                </tr>`;
                });

                // Grand total row
                html += `<tr class="fw-bold" id="grandTotalRow">
                <td colspan="6" class="text-end">Grand Total:</td>
                <td id="grandQty">${grandQty.toFixed(2)}</td>
                <td>-</td>
                <td id="grandTotal">${grandTotal.toFixed(2)}</td>
                <td id="grandNet">${grandNet.toFixed(2)}</td>
                <td id="grandReturn">${grandReturn.toFixed(2)}</td>
            </tr>`;

                $('#saleBody').html(html);
            }
        });
    });

    // Real-time Search Filter Handler
    $(document).on('input', '#search_product', function() {
        let val = $(this).val().toLowerCase();

        $('#saleBody tr').each(function() {
            // Skip the Grand Total row
            if ($(this).attr('id') === 'grandTotalRow') {
                return;
            }

            let productText = $(this).find('td:eq(5)').text().toLowerCase();
            let invoiceText = $(this).find('td:eq(2)').text().toLowerCase();
            let customerText = $(this).find('td:eq(3)').text().toLowerCase();
            let refText = $(this).find('td:eq(4)').text().toLowerCase();

            if (productText.indexOf(val) > -1 || invoiceText.indexOf(val) > -1 || customerText.indexOf(val) > -1 || refText.indexOf(val) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        // Recalculate grand totals from visible rows
        let newQty = 0, newTotal = 0, newNet = 0, newReturn = 0;
        $('#saleBody tr').each(function() {
            if ($(this).attr('id') === 'grandTotalRow') return;
            if ($(this).is(':visible')) {
                newQty += parseFloat($(this).data('qty')) || 0;
                newTotal += parseFloat($(this).data('total')) || 0;
                newNet += parseFloat($(this).data('net')) || 0;
                newReturn += parseFloat($(this).data('return')) || 0;
            }
        });

        // Update grand total elements
        $('#grandQty').text(newQty.toFixed(2));
        $('#grandTotal').text(newTotal.toFixed(2));
        $('#grandNet').text(newNet.toFixed(2));
        $('#grandReturn').text(newReturn.toFixed(2));
    });


    // Ensure DOM is loaded
    $(document).ready(function() {
        // Initialize default dates based on shop shift (2:00 PM to 12:00 PM next day)
        let now = new Date();
        let startDt = new Date();
        let endDt = new Date();

        if (now.getHours() < 12) {
            // Before noon: we are in the shift that started yesterday at 2 PM
            startDt.setDate(now.getDate() - 1);
            startDt.setHours(14, 0, 0, 0);
            
            endDt.setHours(12, 0, 0, 0); // ends today at 12 PM
        } else {
            // After noon: the shift started today at 2 PM
            startDt.setHours(14, 0, 0, 0);
            
            endDt.setDate(now.getDate() + 1);
            endDt.setHours(12, 0, 0, 0); // ends tomorrow at 12 PM
        }

        function formatDateTimeLocal(date) {
            let year = date.getFullYear();
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let day = String(date.getDate()).padStart(2, '0');
            let hours = String(date.getHours()).padStart(2, '0');
            let minutes = String(date.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        $('#start_date').val(formatDateTimeLocal(startDt));
        $('#end_date').val(formatDateTimeLocal(endDt));

        // Auto trigger search
        $('#btnSearch').trigger('click');

        // CSV export
        $(document).on('click', '#btnExportCsv', function() {
            let csv = [];
            $("#saleReport tr").each(function() {
                let row = [];
                $(this).find('th,td').each(function() {
                    let cellHtml = $(this).html();

                    // <br> ko "|" ya comma se replace kardo
                    let cellText = cellHtml
                        .replace(/<br\s*\/?>/gi, " | ")
                        .replace(/&nbsp;/gi, " ")
                        .replace(/<[^>]*>/g, "")
                        .trim();

                    row.push('"' + cellText.replace(/"/g, '""') + '"');
                });
                csv.push(row.join(","));
            });

            let csvString = csv.join("\n");
            let blob = new Blob([csvString], {
                type: 'text/csv;charset=utf-8;'
            });

            let link = document.createElement("a");
            if (link.download !== undefined) {
                let url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "sale_report.csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
    });
</script>