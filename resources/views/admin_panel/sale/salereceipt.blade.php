<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Receipt - {{ $sale->invoice_no }}</title>
    <style>
        @media print {
            @page {
                margin: 2mm 3mm 2mm 2mm !important;
            }
            body {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                color: #000 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-container {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 4px 0 0 !important;
                width: 96% !important;
                max-width: 96% !important;
            }
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 5px 0;
            color: #000;
            font-size: 11px;
            font-weight: 400;
            line-height: 1.3;
            -webkit-print-color-adjust: exact;
        }

        .receipt-container {
            width: 100%;
            max-width: 76mm;
            margin: 0 auto;
            background: #fff;
            padding: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        h1, h2, h3, p { margin: 0; padding: 0; }

        .company-name {
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            color: #000;
        }

        .company-info {
            font-size: 11.5px;
            text-align: center;
            color: #000;
            font-weight: 400;
            line-height: 1.4;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .company-info div {
            margin-bottom: 1px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        /* Meta Grid */
        .meta-grid {
            display: flex;
            flex-direction: column;
            gap: 3px;
            margin-bottom: 2px;
            color: #000;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 400;
        }

        .meta-label { font-weight: 400; color: #000; }
        .meta-value { font-weight: 400; color: #000; }

        /* Items Table */
        .items-table {
            width: 98%;
            border-collapse: collapse;
            font-size: 11px;
            color: #000;
            margin: 3px 0;
        }

        .items-table th {
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 4px 0;
            vertical-align: top;
            border-bottom: 1px dashed #ccc;
        }

        .item-name {
            font-weight: 500;
            font-size: 11px;
            color: #000;
            display: block;
        }

        .item-variant {
            font-size: 10px;
            color: #000;
            font-weight: 400;
            margin-top: 1px;
            display: block;
        }

        .text-end { text-align: right !important; }
        .text-center { text-align: center !important; }

        /* Totals */
        .totals-section {
            font-size: 11px;
            margin-top: 5px;
            color: #000;
        }

        .tot-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }

        .tot-row span:first-child { font-weight: 400; color: #000; }
        .tot-row span:last-child { font-weight: 400; }

        .tot-row.grand-total {
            font-size: 13px;
            font-weight: 700;
            margin: 3px 0;
            padding: 5px 0;
            border-top: 1.5px solid #000;
            border-bottom: 2px double #000;
            color: #000;
        }

        .tot-row.grand-total span:first-child { font-weight: 700; }
        .tot-row.grand-total span:last-child { font-weight: 700; }

        /* Balances */
        .balance-section {
            font-size: 11px;
            margin-top: 4px;
            border-top: 1px dashed #000;
            padding-top: 4px;
        }

        .balance-section .tot-row.closing-bal {
            font-weight: 600;
            background-color: #f8f9fa;
            padding: 3px 2px;
            border: 1px solid #000;
            margin-top: 2px;
        }

        .balance-section .tot-row.closing-bal span:first-child { font-weight: 600; }
        .balance-section .tot-row.closing-bal span:last-child { font-weight: 600; }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 12px;
            color: #000;
        }

        .footer p {
            font-size: 11px;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .footer .soft-credit {
            font-size: 9px;
            color: #000;
            margin-top: 3px;
            font-weight: 400;
        }

        /* Controls */
        .print-controls {
            width: 76mm;
            margin: 0 auto 15px auto;
            display: flex;
            gap: 10px;
        }

        .btn {
            flex: 1;
            padding: 10px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary { background: #000; color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
    </style>
</head>

<body>

    <div class="print-controls no-print">
        <a href="javascript:window.print()" class="btn btn-primary">🖨️ Print Receipt</a>
        <a href="{{ route('sale.index') }}" class="btn btn-secondary">← Back</a>
    </div>

    <div class="receipt-container">
        <!-- Header -->
        <div class="company-name">{{ \App\Models\Setting::get('company_name', 'Three Stars Medical') }}</div>
        <div class="company-info">
            <div>{{ \App\Models\Setting::get('company_address', 'Hyderabad') }}</div>
            <div>Ph: {{ \App\Models\Setting::get('company_phone', '0327-9226901') }}</div>
        </div>
        @if(\App\Models\Setting::get('facebook_link') || \App\Models\Setting::get('tiktok_link') || \App\Models\Setting::get('instagram_link') || \App\Models\Setting::get('website_link'))
        <div style="text-align: left; font-size: 10px; line-height: 1.4; word-wrap: break-word; overflow-wrap: break-word; margin-top: 4px;">
            @if(\App\Models\Setting::get('facebook_link'))
                <div style="margin-bottom: 2px;"><strong>Facebook:</strong> {{ \App\Models\Setting::get('facebook_link') }}</div>
            @endif
            @if(\App\Models\Setting::get('tiktok_link'))
                <div style="margin-bottom: 2px;"><strong>TikTok:</strong> {{ \App\Models\Setting::get('tiktok_link') }}</div>
            @endif
            @if(\App\Models\Setting::get('instagram_link'))
                <div style="margin-bottom: 2px;"><strong>Instagram:</strong> {{ \App\Models\Setting::get('instagram_link') }}</div>
            @endif
            @if(\App\Models\Setting::get('website_link'))
                <div style="margin-bottom: 2px;"><strong>Website:</strong> {{ \App\Models\Setting::get('website_link') }}</div>
            @endif
        </div>
        @endif

        <div class="divider"></div>

        <!-- Meta Grid -->
        @php
            $isWalkin = empty($sale->customer_id);
        @endphp
        <div class="meta-grid">
            <div class="meta-row">
                <span class="meta-label">Invoice #:</span>
                <span class="meta-value">{{ $sale->invoice_no }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Date:</span>
                <span class="meta-value">{{ $sale->created_at->format('d/m/Y h:i A') }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Customer:</span>
                <span class="meta-value">{{ Str::limit($sale->walkin_name ?? ($sale->customer_relation->customer_name ?? 'Walking Customer'), 22) }}</span>
            </div>
            @if (auth()->check())
            <div class="meta-row">
                <span class="meta-label">Salesperson:</span>
                <span class="meta-value">{{ auth()->user()->name }}</span>
            </div>
            @endif
            @if($sale->reference)
            <div class="meta-row">
                <span class="meta-label">Remarks:</span>
                <span class="meta-value">{{ $sale->reference }}</span>
            </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 6%;">S.</th>
                    <th style="width: 53%;">Description</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 13%;" class="text-end">Rate</th>
                    <th style="width: 18%;" class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($saleItems as $item)
                    @php
                        $sizeMode = $item['size_mode'] ?? 'std';
                        $totalPieces = (int) $item['total_pieces'];

                        $qtyDisplay = $totalPieces;
                        if ($sizeMode == 'by_cartons' || $sizeMode == 'by_size') {
                            $piecesPerBox = (int)($item['pieces_per_box'] ?? 1);
                            if ($piecesPerBox <= 0) $piecesPerBox = 1;
                            $boxes = floor($totalPieces / $piecesPerBox);
                            $loose = $totalPieces % $piecesPerBox;

                            if ($boxes > 0 && $loose > 0) {
                                $qtyDisplay = "$boxes.$loose";
                            } elseif ($boxes > 0) {
                                $qtyDisplay = $boxes;
                            } else {
                                $qtyDisplay = $loose;
                            }
                        }

                        $sizeStr = '';
                        if (!empty($item['size_val']) && $item['size_val'] !== '-') {
                            $sizeStr = $item['size_val'];
                        } elseif (($item['size_mode'] ?? '') == 'by_size' && ($item['height'] ?? 0) > 0 && ($item['width'] ?? 0) > 0) {
                            $sizeStr = number_format($item['width'], 0) . 'x' . number_format($item['height'], 0);
                        }
                        $colorStr = '';
                        if (!empty($item['color_val']) && $item['color_val'] !== '-') {
                            $colorStr = $item['color_val'];
                        }
                        $variantStr = implode(' | ', array_filter([$sizeStr, $colorStr]));
                    @endphp
                    <tr>
                        <td style="width: 6%;">{{ $loop->iteration }}</td>
                        <td style="width: 53%;">
                            <span class="item-name">{{ $item['item_name'] }}</span>
                            @if($variantStr)
                                <span class="item-variant">{{ $variantStr }}</span>
                            @endif
                        </td>
                        <td style="width: 10%;" class="text-center">{{ $qtyDisplay }}</td>
                        <td style="width: 13%;" class="text-end">{{ number_format($item['price'], 0) }}</td>
                        <td style="width: 18%;" class="text-end">{{ number_format($item['total'], 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="tot-row">
                <span>Sub Total:</span>
                <span>{{ number_format($sale->total_bill_amount, 0) }}</span>
            </div>

            @if ($sale->total_extradiscount > 0)
                <div class="tot-row">
                    <span>Discount:</span>
                    <span>- {{ number_format($sale->total_extradiscount, 0) }}</span>
                </div>
            @endif

            <div class="tot-row grand-total">
                <span>TOTAL PAYABLE:</span>
                <span>{{ number_format($sale->total_net, 0) }}</span>
            </div>
        </div>

        <!-- Ledger -->
        <div class="balance-section">
            @if(!$isWalkin)
            <div class="tot-row">
                <span>Prev Balance:</span>
                <span>{{ number_format(abs($previousBalance), 0) }} {{ $previousBalance >= 0 ? 'Dr' : 'Cr' }}</span>
            </div>
            @endif
            <div class="tot-row">
                <span>Paid Amount:</span>
                <span>{{ number_format($sale->cash, 0) }}</span>
            </div>
            @if($sale->change > 0)
            <div class="tot-row">
                <span>Change:</span>
                <span>{{ number_format($sale->change, 0) }}</span>
            </div>
            @endif

            @if(!$isWalkin)
            @php
                $finalBalance = $previousBalance + $sale->total_net - $sale->cash;
            @endphp
            <div class="tot-row closing-bal">
                <span>CLOSING BALANCE:</span>
                <span>{{ number_format(abs($finalBalance), 0) }} {{ $finalBalance >= 0 ? 'Dr' : 'Cr' }}</span>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for shopping with us!</p>
            <div class="soft-credit">Powered by Prowave Technologies<br>📞 +92 317 3836 223</div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });

        window.onafterprint = function() {
            setTimeout(() => {
                try {
                    if (window.opener) {
                        window.close();
                    } else {
                        window.location.href = "{{ route('pos.index') }}";
                    }
                } catch (e) {
                    window.location.href = "{{ route('pos.index') }}";
                }
            }, 500);
        };
    </script>
</body>

</html>
