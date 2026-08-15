<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Invoice - {{ $sale->invoice_no }}</title>
    <link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            background-color: #f1f5f9;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 20px 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .invoice-card {
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 25px 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .print-btn-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .header-table td {
            vertical-align: top;
            padding: 2px 4px;
        }
        .company-title {
            font-size: 22px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000;
            margin-bottom: 2px;
        }
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            border: 1.5px solid #000;
        }
        .inv-table th, .inv-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 12px;
            color: #000;
        }
        .inv-table th {
            background-color: #f8f9fa;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            font-size: 12px;
            padding: 6px 4px;
        }
        .inv-table tbody tr td {
            height: 24px;
        }
        .summary-box {
            width: 320px;
            border: 1.5px solid #000;
            margin-left: auto;
            margin-top: 15px;
            background: #fff;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 10px;
            border-bottom: 1px solid #000;
            font-size: 13px;
        }
        .summary-row:last-child {
            border-bottom: none;
            font-weight: 800;
            font-size: 14px;
            background-color: #f8f9fa;
        }
        .signatures-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 60px;
            padding: 0 10px;
            font-size: 13px;
            font-weight: 700;
        }
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .invoice-card {
                max-width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 5mm 8mm !important;
                box-shadow: none !important;
                border: none !important;
            }
            .print-btn-container, .no-print {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 6mm;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Action Buttons -->
    <div class="print-btn-container no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-3 shadow fw-bold">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill me-1" viewBox="0 0 16 16">
                <path d="M0 9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V9zm4-6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2H4V3z"/>
                <path d="M2.5 14.5A1.5 1.5 0 0 1 1 13V9a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v4a1.5 1.5 0 0 1-1.5 1.5h-13z"/>
            </svg>
            Print A4 Invoice
        </button>
        <a href="{{ route('sale.index') }}" class="btn btn-secondary btn-sm px-3 shadow ms-2 fw-bold">Back</a>
    </div>

    <div class="invoice-card">
        <!-- Header Information -->
        <table class="header-table">
            <tr>
                <!-- Company / Customer Info Left -->
                <td style="width: 58%;">
                    <div class="company-title">{{ \App\Models\Setting::get('company_name', 'PROWAVE TECHNOLOGIES') }}</div>
                    <div style="font-size: 12px; line-height: 1.3; margin-bottom: 6px;">
                        <div>{{ \App\Models\Setting::get('company_address', 'Main Auto Bhan Road, Hyderabad') }}</div>
                        <div><strong>Phone:</strong> {{ \App\Models\Setting::get('company_phone', '+92 325-9385085') }}</div>
                    </div>
                    
                    <div style="border-top: 1px dashed #000; padding-top: 4px; font-size: 12px;">
                        <div><strong>M/S:</strong> {{ $sale->walkin_name ?? ($sale->customer_relation->customer_name ?? 'Walk-in Customer') }}</div>
                        <div><strong>Address:</strong> {{ $sale->customer_relation->address ?? '-' }}</div>
                        <div><strong>Telepo / Mobile:</strong> {{ $sale->customer_relation->mobile ?? ($sale->customer_relation->phone ?? '-') }}</div>
                    </div>
                </td>

                <!-- Invoice Meta Right -->
                <td style="width: 42%; text-align: right;">
                    <h4 style="font-weight: 800; text-transform: uppercase; margin: 0 0 6px 0; letter-spacing: 1px;">
                        {{ ($isEstimate ?? false) ? 'ESTIMATE' : 'SALES INVOICE' }}
                    </h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-left: auto;">
                        <tr>
                            <td style="text-align: right; font-weight: bold; width: 45%; padding: 2px;">INVOICE NO:</td>
                            <td style="text-align: left; padding: 2px; font-weight: 800;">{{ $sale->invoice_no }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: right; font-weight: bold; padding: 2px;">D/C NO:</td>
                            <td style="text-align: left; padding: 2px;">{{ $sale->reference ?: $sale->id }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: right; font-weight: bold; padding: 2px;">DATE:</td>
                            <td style="text-align: left; padding: 2px;">{{ $sale->created_at ? $sale->created_at->format('d/m/Y') : date('d/m/Y') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Main Items Table -->
        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width: 5%;">S.No.</th>
                    <th style="width: 37%; text-align: left; padding-left: 8px;">Particulars</th>
                    <th style="width: 8%;">CTN</th>
                    <th style="width: 9%;">PCS</th>
                    <th style="width: 9%; text-align: right; padding-right: 6px;">Rate</th>
                    <th style="width: 11%; text-align: right; padding-right: 6px;">Gross Amount</th>
                    <th style="width: 10%; text-align: right; padding-right: 6px;">Discount</th>
                    <th style="width: 11%; text-align: right; padding-right: 6px;">Net Amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalCtn = 0;
                    $totalPcs = 0;
                    $totalGross = 0;
                    $totalDisc = 0;
                    $totalNet = 0;
                @endphp

                @foreach ($saleItems as $index => $item)
                    @php
                        $totalPieces = (float) ($item['total_pieces'] ?? 0);
                        $ppb = (int) ($item['pieces_per_box'] ?? 1);
                        if ($ppb <= 0) $ppb = 1;
                        
                        $isCarton = ($item['size_mode'] ?? '') === 'by_cartons' || ($item['variant_unit'] ?? '') === 'Carton';
                        
                        if ($isCarton && $ppb > 1) {
                            $boxes = floor($totalPieces / $ppb);
                            $loose = $totalPieces % $ppb;
                            $ctnVal = $boxes + ($loose / $ppb);
                            $ctnDisplay = $loose > 0 ? ($boxes . '.' . $loose) : ($boxes > 0 ? $boxes : '0');
                            $totalCtn += $ctnVal;
                        } else {
                            $ctnDisplay = ($item['qty_box'] > 0) ? $item['qty_box'] : '-';
                            if ($item['qty_box'] > 0) $totalCtn += (float)$item['qty_box'];
                        }

                        $totalPcs += $totalPieces;
                        $rate = (float) ($item['price'] ?? 0);
                        $net = (float) ($item['total'] ?? 0);

                        if ($rate > 0 && $totalPieces > 0) {
                            if ($isCarton && $ppb > 1) {
                                $gross = ($totalPieces / $ppb) * $rate;
                            } else {
                                $gross = $totalPieces * $rate;
                            }
                        } else {
                            $gross = $net;
                        }

                        if ($gross < $net) $gross = $net;
                        $disc = max(0, $gross - $net);

                        $totalGross += $gross;
                        $totalDisc += $disc;
                        $totalNet += $net;

                        $variantInfo = [];
                        if (!empty($item['size_val']) && $item['size_val'] !== '-') $variantInfo[] = $item['size_val'];
                        if (!empty($item['color_val']) && $item['color_val'] !== '-') $variantInfo[] = $item['color_val'];
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td style="text-align: left; padding-left: 8px;">
                            <strong>{{ $item['item_name'] }}</strong>
                            @if(count($variantInfo) > 0)
                                <span style="font-size: 11px; color: #333;">({{ implode(' | ', $variantInfo) }})</span>
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $ctnDisplay }}</td>
                        <td style="text-align: center;">{{ $totalPieces }}</td>
                        <td style="text-align: right; padding-right: 6px;">{{ number_format($rate, 2) }}</td>
                        <td style="text-align: right; padding-right: 6px;">{{ number_format($gross, 2) }}</td>
                        <td style="text-align: right; padding-right: 6px;">{{ $disc > 0 ? number_format($disc, 2) : '' }}</td>
                        <td style="text-align: right; padding-right: 6px;">{{ number_format($net, 2) }}</td>
                    </tr>
                @endforeach

                <!-- Blank filler rows for clean look if less than 8 items -->
                @for ($i = count($saleItems); $i < 6; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor

                <!-- TOTAL ROW -->
                <tr style="font-weight: 800; background-color: #f8f9fa;">
                    <td colspan="2" style="text-align: center; letter-spacing: 1px; font-size: 13px;">TOTAL</td>
                    <td style="text-align: center; font-size: 13px;">{{ $totalCtn > 0 ? (fmod($totalCtn, 1) !== 0.0 ? number_format($totalCtn, 2) : (int)$totalCtn) : '-' }}</td>
                    <td style="text-align: center;"></td>
                    <td></td>
                    <td style="text-align: right; padding-right: 6px; font-size: 13px;">{{ number_format($totalGross, 2) }}</td>
                    <td style="text-align: right; padding-right: 6px; font-size: 13px;">{{ $totalDisc > 0 ? number_format($totalDisc, 2) : '0.00' }}</td>
                    <td style="text-align: right; padding-right: 6px; font-size: 13px;">{{ number_format($totalNet, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Summary Calculation Box (Right Aligned) -->
        <div class="summary-box">
            <div class="summary-row">
                <span>Net Total Rs.</span>
                <span>{{ number_format($sale->total_net, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Previous Balance</span>
                <span>{{ number_format($previousBalance ?? 0, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Balance Rs.</span>
                <span>{{ number_format(($currentBalance ?? ($sale->total_net + ($previousBalance ?? 0))), 2) }}</span>
            </div>
        </div>

        <!-- Signatures Area -->
        <div class="signatures-container">
            <div>
                Authorized Signature _______________________________
            </div>
            <div>
                Prepared By: {{ auth()->user()->name ?? 'Administrator' }}
            </div>
        </div>
    </div>

</body>
</html>
