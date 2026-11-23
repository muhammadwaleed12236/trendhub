<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sale Return Voucher - {{ $return->return_invoice }}</title>
    <link rel="stylesheet" href="{{ asset('assets/fonts/poppins/poppins.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/all.min.css') }}">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px 0;
            font-size: 13px;
            color: #1e293b;
        }

        .action-bar {
            width: 840px;
            margin: 0 auto 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-print {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-print:hover { background: #1d4ed8; }

        .btn-back {
            background: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-back:hover { background: #f1f5f9; }

        .voucher-sheet {
            width: 840px;
            margin: 0 auto;
            background: #fff;
            padding: 30px 36px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }

        .vch-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .company-info h2 {
            margin: 0 0 4px;
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
        }

        .company-info p {
            margin: 0;
            font-size: 12px;
            color: #64748b;
        }

        .vch-title-box {
            text-align: right;
        }

        .vch-badge {
            display: inline-block;
            background: #dc2626;
            color: #ffffff;
            padding: 5px 12px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 4px;
            margin-bottom: 6px;
        }

        .vch-no {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
            background: #f8fafc;
            padding: 14px 16px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .meta-col p {
            margin: 0 0 5px;
            font-size: 12.5px;
        }
        .meta-col p:last-child { margin-bottom: 0; }
        .meta-col strong { color: #334155; }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12.5px;
        }

        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border: 1px solid #0f172a;
        }

        .items-table td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .totals-wrap {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 30px;
        }

        .remarks-box {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            font-size: 12px;
        }
        .remarks-box strong { display: block; margin-bottom: 4px; color: #475569; }

        .totals-table {
            width: 320px;
            border-collapse: collapse;
            font-size: 13px;
        }

        .totals-table td {
            padding: 6px 10px;
        }
        .totals-table tr.total-row {
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
        }

        .badge-cond-good {
            color: #166534;
            font-weight: 600;
            font-size: 11px;
        }
        .badge-cond-damaged {
            color: #991b1b;
            font-weight: 600;
            font-size: 11px;
        }

        .sig-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 15px;
            margin-top: 60px;
            text-align: center;
        }

        .sig-line {
            border-top: 1px solid #94a3b8;
            padding-top: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .voucher-sheet {
                width: 100%;
                border: none;
                box-shadow: none;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <a href="{{ route('sale.return.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Returns
        </a>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('sale.return.create') }}" class="btn-back">
                <i class="fas fa-plus"></i> New Return
            </a>
            <button onclick="window.print()" class="btn-print">
                <i class="fas fa-print"></i> Print Voucher
            </button>
        </div>
    </div>

    <div class="voucher-sheet">
        {{-- Header --}}
        <div class="vch-header">
            <div class="company-info">
                <h2>{{ \App\Models\Setting::get('company_name', 'TrendHub ERP') }}</h2>
                <p>{{ \App\Models\Setting::get('company_address', '') }}</p>
                <p>Phone: {{ \App\Models\Setting::get('company_phone', '') }}</p>
            </div>
            <div class="vch-title-box">
                <span class="vch-badge">Credit Note / Sale Return</span>
                <p class="vch-no">{{ $return->return_invoice }}</p>
            </div>
        </div>

        {{-- Meta Grid --}}
        <div class="meta-grid">
            <div class="meta-col">
                <p><strong>Customer:</strong> {{ $return->customer->customer_name ?? 'Walking Customer' }}</p>
                <p><strong>Phone:</strong> {{ $return->customer->mobile ?? 'N/A' }}</p>
                @if (!empty($return->customer->address))
                    <p><strong>Address:</strong> {{ $return->customer->address }}</p>
                @endif
                <p><strong>Warehouse:</strong> {{ $return->warehouse->warehouse_name ?? 'Default Warehouse' }}</p>
            </div>
            <div class="meta-col" style="text-align: right;">
                <p><strong>Return Date:</strong> {{ \Carbon\Carbon::parse($return->return_date)->format('d/m/Y') }}</p>
                @if ($return->sale)
                    <p><strong>Original Invoice #:</strong> {{ $return->sale->invoice_no }}</p>
                    <p><strong>Invoice Date:</strong> {{ $return->sale->created_at ? $return->sale->created_at->format('d/m/Y') : 'N/A' }}</p>
                @endif
                <p><strong>Refund Method:</strong> 
                    @if ($return->refund_type === 'cash')
                        Cash Refund
                    @elseif ($return->refund_type === 'bank')
                        Bank Refund
                    @else
                        Customer Ledger Adjustment
                    @endif
                </p>
            </div>
        </div>

        {{-- Items Table --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px; text-align: center;">#</th>
                    <th>Product Description</th>
                    <th style="width: 80px; text-align: center;">Qty (Pcs)</th>
                    <th style="width: 70px; text-align: center;">Boxes</th>
                    <th style="width: 90px; text-align: right;">Rate</th>
                    <th style="width: 70px; text-align: right;">Disc</th>
                    <th style="width: 100px;">Reason</th>
                    <th style="width: 100px;">Condition</th>
                    <th style="width: 100px; text-align: right;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($return->items as $index => $item)
                    @php
                        $variant = [];
                        if (!empty($item->color)) {
                            $b64Decoded = base64_decode($item->color, true);
                            if ($b64Decoded !== false) {
                                $json = json_decode($b64Decoded, true);
                                if (is_array($json)) $variant = $json;
                            }
                            if (empty($variant)) {
                                $json = json_decode($item->color, true);
                                if (is_array($json)) $variant = $json;
                            }
                        }
                        $vSize = $variant['size'] ?? ($variant['size_val'] ?? '');
                        $vColor = $variant['color'] ?? ($variant['color_val'] ?? '');
                        $vName = $variant['name'] ?? '';
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->product->product_name ?? $item->product->item_name ?? 'Unknown' }}</strong>
                            @if (!empty($vName) || !empty($vSize) || !empty($vColor))
                                <br><small style="color: #64748b;">Variant: {{ $vName }} ({{ $vSize }} - {{ $vColor }})</small>
                            @endif
                            @if (!empty($item->product->product_code))
                                <br><small style="color: #94a3b8;">Code: {{ $item->product->product_code }}</small>
                            @endif
                        </td>
                        <td style="text-align: center; font-weight: 700;">{{ number_format($item->qty, 0) }}</td>
                        <td style="text-align: center;">{{ number_format($item->boxes, 2) }}</td>
                        <td style="text-align: right;">{{ number_format($item->price, 2) }}</td>
                        <td style="text-align: right; color: #dc2626;">{{ number_format($item->item_discount, 2) }}</td>
                        <td>{{ $item->return_reason ?? $return->return_reason ?? '-' }}</td>
                        <td>
                            @if ($item->stock_condition === 'damaged')
                                <span class="badge-cond-damaged"><i class="fas fa-exclamation-triangle"></i> Damaged</span>
                            @else
                                <span class="badge-cond-good"><i class="fas fa-check"></i> Good Stock</span>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: 700;">{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals and Remarks --}}
        <div class="totals-wrap">
            <div class="remarks-box">
                <strong>Return Reason / Remarks:</strong>
                <p style="margin: 0;">{{ $return->remarks ?? $return->return_reason ?? 'None' }}</p>
                <div style="margin-top: 10px; font-size: 11px; color: #64748b;">
                    <strong>Stock Adjustment Note:</strong>
                    Good stock items have been credited back to available warehouse inventory. Damaged items are quarantined.
                </div>
            </div>

            <table class="totals-table">
                <tr>
                    <td>Items Subtotal:</td>
                    <td style="text-align: right; font-weight: 600;">Rs. {{ number_format($return->bill_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Item Discounts:</td>
                    <td style="text-align: right; color: #dc2626;">- Rs. {{ number_format($return->item_discount, 2) }}</td>
                </tr>
                @if ($return->extra_discount > 0)
                    <tr>
                        <td>Extra Discount:</td>
                        <td style="text-align: right; color: #dc2626;">- Rs. {{ number_format($return->extra_discount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td>Net Return Amount:</td>
                    <td style="text-align: right;">Rs. {{ number_format($return->net_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Refund Paid:</td>
                    <td style="text-align: right; font-weight: 600; color: #166534;">Rs. {{ number_format($return->paid, 2) }}</td>
                </tr>
                <tr>
                    <td>Customer Credit/Balance:</td>
                    <td style="text-align: right; font-weight: 600;">Rs. {{ number_format($return->balance, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- Signatures --}}
        <div class="sig-grid">
            <div class="sig-line">Prepared By</div>
            <div class="sig-line">Checked By</div>
            <div class="sig-line">Customer Signature</div>
            <div class="sig-line">Authorized Signature</div>
        </div>
    </div>

</body>
</html>
