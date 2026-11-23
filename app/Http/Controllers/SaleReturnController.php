<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\WarehouseStock;
use App\Models\StockMovement;
use App\Models\Account;
use App\Models\VoucherMaster;
use App\Services\VoucherService;
use App\Services\BalanceService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleReturnController extends Controller
{
    /**
     * Display all sale returns (Returns History)
     */
    public function saleReturnIndex()
    {
        $returns = SaleReturn::with(['customer', 'sale', 'warehouse', 'items.product'])
            ->latest('id')
            ->get();

        // Calculate updated financial details for display
        $returns->each(function ($return) {
            if ($return->sale) {
                $sale = $return->sale;
                $return->original_net_amount = (float)$sale->total_net;
                
                $totalReturned = (float)SaleReturn::where('sale_id', $sale->id)->sum('net_amount');
                $return->total_returned = $totalReturned;
                $return->new_net_amount = max(0, $sale->total_net - $totalReturned);

                $originalDue = max(0, (float)$sale->total_net - ((float)$sale->cash + (float)$sale->card));
                $return->new_due_amount = max(0, $originalDue - $totalReturned);
            }
        });

        return view('admin_panel.sale.sale_return.index', compact('returns'));
    }

    /**
     * Show the Sales Return Entry form
     */
    public function createReturn(Request $request)
    {
        // Auto-generate Return Invoice Number preview
        $lastReturnId = SaleReturn::max('id') ?? 0;
        $nextReturnNumber = 'SR-' . str_pad($lastReturnId + 1, 4, '0', STR_PAD_LEFT);

        // Fetch active customers
        $customers = Customer::orderBy('customer_name')->get();

        // Warehouses
        $warehouses = Warehouse::all();

        // Ensure default Cash & Bank accounts exist
        $cashId = app(BalanceService::class)->getCashAccountId();
        $bankAccount = Account::where('title', 'like', '%Bank%')
            ->orWhere('account_code', 'BANK')
            ->first();
        if (!$bankAccount) {
            Account::create([
                'title' => 'Main Bank Account',
                'account_code' => 'BANK',
                'type' => 'Debit',
                'head_id' => null,
                'opening_balance' => 0,
                'status' => 1,
                'is_active' => 1,
            ]);
        }

        // Cash / Bank accounts for refund options
        $accounts = Account::where(function ($q) {
            $q->whereHas('head', function ($h) {
                $h->whereIn('name', ['Cash', 'Bank']);
            })
            ->orWhere('title', 'like', '%Cash%')
            ->orWhere('title', 'like', '%Bank%')
            ->orWhereIn('account_code', ['CASH', 'BANK']);
        })->where('status', 1)->orderBy('title')->get();

        // Recent sales for dropdown
        $recentSales = Sale::where('sale_status', '!=', 'cancelled')
            ->latest('id')
            ->take(50)
            ->get();

        $selectedSaleId = $request->get('sale_id');

        return view('admin_panel.sale.sale_return.create', compact(
            'nextReturnNumber',
            'customers',
            'warehouses',
            'accounts',
            'recentSales',
            'selectedSaleId'
        ));
    }

    /**
     * Backward-compatible entry point for sale/return/{id}
     */
    public function showReturnForm($id)
    {
        if ($id === 'create') {
            return redirect()->route('sale.return.create');
        }

        return redirect()->route('sale.return.create', ['sale_id' => $id]);
    }

    /**
     * AJAX endpoint: Get sales invoices by Customer ID
     */
    public function getInvoicesByCustomer($customerId)
    {
        $query = Sale::query()->where('sale_status', '!=', 'cancelled');

        if ($customerId && $customerId !== 'all') {
            $cust = Customer::find($customerId);
            if ($cust && ($cust->customer_type === 'Walking Customer' || $cust->customer_id === 'CUST-WALK')) {
                $query->where(function ($q) use ($customerId) {
                    $q->where('customer_id', $customerId)->orWhereNull('customer_id');
                });
            } else {
                $query->where('customer_id', $customerId);
            }
        }

        $sales = $query->latest('id')
            ->take(100)
            ->get(['id', 'invoice_no', 'customer_id', 'created_at', 'total_net', 'sale_status']);

        return response()->json([
            'status' => true,
            'sales' => $sales->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_no' => $sale->invoice_no,
                    'date' => $sale->created_at ? $sale->created_at->format('d/m/Y') : '',
                    'total_net' => (float)$sale->total_net,
                    'sale_status' => $sale->sale_status,
                ];
            })
        ]);
    }

    /**
     * AJAX endpoint: Get sold products and returnable quantities for a specific sale invoice
     */
    public function getInvoiceItems($saleId)
    {
        $sale = Sale::resolveByIdOrInvoice($saleId, ['customer_relation', 'items.product.brand']);
        if (!$sale) {
            return response()->json(['status' => false, 'message' => 'Sale invoice not found.'], 404);
        }

        // Calculate already returned quantities for each item in this sale
        $pastReturns = SaleReturn::where('sale_id', $sale->id)->with('items')->get();
        $returnedQtyMap = [];
        foreach ($pastReturns as $sr) {
            foreach ($sr->items as $srItem) {
                $key = $srItem->product_id . '_' . ($srItem->color ?? '');
                $returnedQtyMap[$key] = ($returnedQtyMap[$key] ?? 0) + (float)$srItem->qty;
            }
        }

        $items = [];
        $allReturned = true;

        foreach ($sale->items as $item) {
            $product = $item->product;
            $key = $item->product_id . '_' . ($item->color ?? '');
            $alreadyReturned = (float)($returnedQtyMap[$key] ?? 0);

            // Total pieces sold
            $soldPieces = (float)($item->total_pieces ?? $item->qty ?? 0);
            $remaining = max(0, $soldPieces - $alreadyReturned);

            if ($remaining > 0) {
                $allReturned = false;
            }

            // Extract variant data
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

            $sizeStr = $variant['size'] ?? ($variant['size_val'] ?? '');
            $colorStr = $variant['color'] ?? ($variant['color_val'] ?? '');
            $variantName = $variant['name'] ?? '';
            $variantLabel = trim(($variantName ? "$variantName " : '') . ($sizeStr || $colorStr ? "($sizeStr - $colorStr)" : ''));

            $price = (!empty($item->price_per_piece) && $item->price_per_piece > 0)
                ? (float)$item->price_per_piece
                : (float)($item->price ?? $item->per_price ?? 0);

            $ppb = (int)($product ? ($product->pieces_per_box ?? $product->packet_size ?? 1) : 1);
            if ($ppb <= 0) $ppb = 1;

            $items[] = [
                'sale_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name ?? ($product ? ($product->item_name ?? $product->product_name ?? 'Unknown') : 'Unknown'),
                'product_code' => $product ? ($product->item_code ?? $product->product_code ?? '') : '',
                'brand' => ($product && $product->brand) ? ($product->brand->name ?? $product->brand_name ?? '') : '',
                'color' => $item->color,
                'variant_label' => $variantLabel,
                'pieces_per_box' => $ppb,
                'sold_total_pieces' => $soldPieces,
                'already_returned' => $alreadyReturned,
                'remaining_returnable' => $remaining,
                'price' => $price,
                'discount' => (float)($item->discount_amount ?? $item->discount ?? 0),
                'unit' => $item->unit ?? 'pc',
                'is_returnable' => ($remaining > 0),
            ];
        }

        $customer = $sale->customer_relation;

        return response()->json([
            'status' => true,
            'sale' => [
                'id' => $sale->id,
                'invoice_no' => $sale->invoice_no,
                'customer_id' => $sale->customer_id,
                'customer_name' => $customer->customer_name ?? $sale->walkin_name ?? 'Walking Customer',
                'customer_balance' => (float)($customer->previous_balance ?? 0),
                'warehouse_id' => $sale->items->first()->warehouse_id ?? 1,
                'date' => $sale->created_at ? $sale->created_at->format('d/m/Y') : '',
                'total_net' => (float)$sale->total_net,
                'all_returned' => $allReturned,
            ],
            'items' => $items,
        ]);
    }

    /**
     * Process and store the Sales Return
     */
    public function processSaleReturn(Request $request)
    {
        // 1. Idempotency & Duplicate Submission Protection
        $formToken = $request->input('form_token');
        if ($formToken) {
            $lockKey = 'sr_submit_' . $formToken;
            $acquired = \Illuminate\Support\Facades\Cache::add($lockKey, true, 120);
            if (!$acquired) {
                return redirect()->route('sale.return.index')->with('warning', 'This return has already been submitted or is currently processing. Duplicate submission prevented.');
            }
        }

        $validated = $request->validate([
            'sale_id' => 'nullable|exists:sales,id',
            'customer_id' => 'nullable',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'refund_type' => 'required|in:ledger,cash,bank',
            'refund_account_id' => 'nullable|exists:accounts,id',
            'return_reason' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
            'extra_discount' => 'nullable|numeric|min:0',
            'product_id' => 'required|array',
            'product_id.*' => 'required|exists:products,id',
            'qty' => 'required|array',
            'qty.*' => 'required|numeric|min:0',
            'price' => 'required|array',
            'price.*' => 'required|numeric|min:0',
            'item_disc' => 'nullable|array',
            'color' => 'nullable|array',
            'stock_condition' => 'nullable|array',
            'item_reason' => 'nullable|array',
        ]);

        // Validate that at least one item has return quantity > 0
        $hasItems = false;
        foreach ($request->product_id as $idx => $productId) {
            $qty = (float)($request->qty[$idx] ?? 0);
            if ($qty > 0) {
                $hasItems = true;
                break;
            }
        }
        if (!$hasItems) {
            return back()->withInput()->with('error', 'Please enter a return quantity greater than 0 for at least one item.');
        }

        // Validate that return quantity does NOT exceed remaining returnable quantity for this invoice
        $sale = !empty($validated['sale_id']) ? Sale::where('id', $validated['sale_id'])->lockForUpdate()->first() : null;
        if ($sale) {
            $pastReturns = SaleReturn::where('sale_id', $sale->id)->with('items')->get();
            $returnedQtyMap = [];
            foreach ($pastReturns as $sr) {
                foreach ($sr->items as $srItem) {
                    $key = $srItem->product_id . '_' . ($srItem->color ?? '');
                    $returnedQtyMap[$key] = ($returnedQtyMap[$key] ?? 0) + (float)$srItem->qty;
                }
            }

            foreach ($request->product_id as $idx => $productId) {
                $qty = (float)($request->qty[$idx] ?? 0);
                if ($qty <= 0) continue;

                $color = $request->color[$idx] ?? null;
                $key = $productId . '_' . ($color ?? '');
                $alreadyReturned = (float)($returnedQtyMap[$key] ?? 0);

                // Find sold quantity for this item in this sale
                $soldPieces = (float)SaleItem::where('sale_id', $sale->id)
                    ->where('product_id', $productId)
                    ->when($color, function ($q) use ($color) {
                        $q->where('color', $color);
                    })
                    ->sum('total_pieces');

                $maxAllowed = max(0, $soldPieces - $alreadyReturned);

                if ($qty > $maxAllowed) {
                    $prod = Product::find($productId);
                    $prodName = $prod ? ($prod->product_name ?? $prod->item_name) : "Product #{$productId}";
                    return back()->withInput()->with('error', "Return quantity ({$qty}) exceeds the remaining returnable quantity ({$maxAllowed}) for '{$prodName}'.");
                }
            }
        }

        DB::beginTransaction();

        try {
            // Generate Return Invoice Number
            $lastReturnId = SaleReturn::max('id') ?? 0;
            do {
                $lastReturnId++;
                $nextInvoice = 'SR-' . str_pad($lastReturnId, 4, '0', STR_PAD_LEFT);
            } while (SaleReturn::where('return_invoice', $nextInvoice)->exists());

            // Resolve Customer ID (fallback to sale's customer or Walking Customer)
            $customerId = $validated['customer_id'] ?? null;
            if (!$customerId || $customerId === 'all') {
                $customerId = $sale ? $sale->customer_id : null;
            }
            if (!$customerId) {
                $walkingCustomer = Customer::where('customer_type', 'Walking Customer')->first();
                if ($walkingCustomer) {
                    $customerId = $walkingCustomer->id;
                } else {
                    $walkingCustomer = Customer::create([
                        'customer_id' => 'CUST-WALK',
                        'customer_name' => 'Walking Customer',
                        'customer_type' => 'Walking Customer',
                        'mobile' => '-',
                        'status' => 'active',
                        'opening_balance' => 0,
                    ]);
                    $customerId = $walkingCustomer->id;
                }
            }
            $customer = Customer::find($customerId);

            // Create Sale Return Header
            $return = SaleReturn::create([
                'sale_id' => $validated['sale_id'] ?? null,
                'return_invoice' => $nextInvoice,
                'customer_id' => $customerId,
                'warehouse_id' => $validated['warehouse_id'],
                'return_date' => $validated['return_date'],
                'refund_type' => $validated['refund_type'],
                'return_reason' => $validated['return_reason'] ?? null,
                'remarks' => $validated['remarks'] ?? $validated['return_reason'] ?? null,
                'status' => 'posted',
            ]);

            $now = Carbon::now();
            $movements = [];
            $grossSubtotal = 0;
            $totalItemDiscount = 0;

            // Process Each Return Item
            foreach ($request->product_id as $idx => $productId) {
                $qty = (float)($request->qty[$idx] ?? 0); // Total pieces
                if ($qty <= 0) continue;

                $price = (float)($request->price[$idx] ?? 0);
                $itemDisc = (float)($request->item_disc[$idx] ?? 0);
                $condition = $request->stock_condition[$idx] ?? 'good';
                $itemReason = $request->item_reason[$idx] ?? $validated['return_reason'] ?? 'Return';

                $product = Product::find($productId);
                $ppb = (int)($product ? ($product->pieces_per_box > 0 ? $product->pieces_per_box : 1) : 1);

                $itemGross = round($qty * $price, 2);
                $lineTotal = max(0, round($itemGross - $itemDisc, 2));

                $boxes = floor($qty / $ppb);
                $loosePieces = $qty % $ppb;

                // Save Return Item
                SaleReturnItem::create([
                    'sale_return_id' => $return->id,
                    'product_id' => $productId,
                    'color' => $request->color[$idx] ?? null,
                    'warehouse_id' => $validated['warehouse_id'],
                    'qty' => $qty,
                    'boxes' => $boxes + ($loosePieces / $ppb),
                    'loose_pieces' => $loosePieces,
                    'price' => $price,
                    'item_discount' => $itemDisc,
                    'unit' => 'pc',
                    'line_total' => $lineTotal,
                    'return_reason' => $itemReason,
                    'stock_condition' => $condition,
                ]);

                // Inventory Update Logic:
                // Good Stock: Added back to salable inventory
                // Damaged Stock: Do not add to salable stock; track separately
                if ($condition === 'good') {
                    $stock = WarehouseStock::where('warehouse_id', $validated['warehouse_id'])
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();

                    if ($stock) {
                        $currentTotalPieces = $stock->total_pieces;
                        if ($currentTotalPieces == 0 && $stock->quantity > 0) {
                            $currentTotalPieces = $stock->quantity * $ppb;
                        }
                        $newTotalPieces = $currentTotalPieces + $qty;
                        $stock->total_pieces = $newTotalPieces;
                        $stock->quantity = $newTotalPieces / $ppb;
                        $stock->save();
                    } else {
                        WarehouseStock::create([
                            'warehouse_id' => $validated['warehouse_id'],
                            'product_id' => $productId,
                            'total_pieces' => $qty,
                            'quantity' => $qty / $ppb,
                        ]);
                    }

                    $movements[] = [
                        'product_id' => $productId,
                        'type' => 'in',
                        'qty' => $qty,
                        'ref_type' => 'SALE_RETURN',
                        'ref_id' => $return->id,
                        'note' => "Return #{$nextInvoice} (Good Stock)",
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                } else {
                    // Damaged Stock: Do NOT add to available WarehouseStock
                    $movements[] = [
                        'product_id' => $productId,
                        'type' => 'in',
                        'qty' => $qty,
                        'ref_type' => 'SALE_RETURN_DAMAGED',
                        'ref_id' => $return->id,
                        'note' => "Return #{$nextInvoice} (Damaged Stock - Not Restocked)",
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $grossSubtotal += $itemGross;
                $totalItemDiscount += $itemDisc;
            }

            // Insert stock movements
            if (!empty($movements)) {
                DB::table('stock_movements')->insert($movements);
            }

            $extraDiscount = (float)($request->extra_discount ?? 0);
            $netSubtotal = max(0, $grossSubtotal - $totalItemDiscount);
            $netAmount = max(0, $netSubtotal - $extraDiscount);

            $refundType = $validated['refund_type'];
            $totalPaid = 0;
            $voucherService = app(VoucherService::class);
            $balanceService = app(BalanceService::class);
            $arAccountId = $balanceService->getAccountsReceivableId();
            $salesRevenueId = $balanceService->getSalesRevenueId();

            // 1. Double-Entry Accounting: Create Credit Note Journal Voucher
            // Dr. Sales Revenue (Reduces Income)
            // Cr. Accounts Receivable (Customer)
            $jvLines = [
                [
                    'account_id' => $salesRevenueId,
                    'debit' => $netAmount,
                    'credit' => 0,
                    'narration' => "Credit Note for Sale Return #{$nextInvoice}",
                ],
                [
                    'account_id' => $arAccountId,
                    'debit' => 0,
                    'credit' => $netAmount,
                    'narration' => "Sale Return #{$nextInvoice} from Customer",
                ]
            ];

            $jvData = [
                'voucher_type' => VoucherMaster::TYPE_JOURNAL,
                'date' => $validated['return_date'],
                'status' => VoucherMaster::STATUS_POSTED,
                'party_type' => Customer::class,
                'party_id' => $customerId,
                'remarks' => "Credit Note for Sale Return #{$nextInvoice}" . ($sale ? " (Invoice #{$sale->invoice_no})" : ""),
            ];

            $voucherService->createVoucher($jvData, $jvLines, auth()->id());

            // 2. Handle Refund Option:
            if ($refundType === 'cash' || $refundType === 'bank') {
                $totalPaid = $netAmount;
                $balance = 0;

                // Determine Account
                $refundAccId = $validated['refund_account_id'] ?? null;
                if (!$refundAccId) {
                    if ($refundType === 'cash') {
                        $refundAccId = $balanceService->getCashAccountId();
                    } else {
                        $bankAcc = Account::where('title', 'like', '%Bank%')->orWhere('account_code', 'BANK')->first();
                        $refundAccId = $bankAcc ? $bankAcc->id : $balanceService->getCashAccountId();
                    }
                }

                // Payment Voucher (Money Out):
                // Dr. Accounts Receivable (Customer)
                // Cr. Cash/Bank Account
                $pvLines = [
                    [
                        'account_id' => $arAccountId,
                        'debit' => $netAmount,
                        'credit' => 0,
                        'narration' => "Refund to Customer for Return #{$nextInvoice}",
                    ],
                    [
                        'account_id' => $refundAccId,
                        'debit' => 0,
                        'credit' => $netAmount,
                        'narration' => "Refund paid via " . ucfirst($refundType) . " for Return #{$nextInvoice}",
                    ]
                ];

                $pvData = [
                    'voucher_type' => VoucherMaster::TYPE_PAYMENT,
                    'date' => $validated['return_date'],
                    'status' => VoucherMaster::STATUS_POSTED,
                    'party_type' => Customer::class,
                    'party_id' => $customerId,
                    'remarks' => ucfirst($refundType) . " Refund for Sale Return #{$nextInvoice}",
                ];

                $voucherService->createVoucher($pvData, $pvLines, auth()->id());

                // Customer Ledger audit trail: Credit Note and Refund entries
                if ($customer) {
                    $lastEntry = CustomerLedger::where('customer_id', $customerId)->lockForUpdate()->latest('id')->first();
                    $prevBal = $lastEntry ? (float)$lastEntry->closing_balance : (float)($customer->previous_balance ?? 0);

                    // Credit Note entry
                    CustomerLedger::create([
                        'customer_id' => $customerId,
                        'admin_or_user_id' => auth()->id() ?? 1,
                        'description' => "Credit Note for Return #{$nextInvoice}" . ($sale ? " (Inv #{$sale->invoice_no})" : ""),
                        'previous_balance' => $prevBal,
                        'closing_balance' => $prevBal - $netAmount,
                        'opening_balance' => 0,
                    ]);

                    // Refund entry
                    CustomerLedger::create([
                        'customer_id' => $customerId,
                        'admin_or_user_id' => auth()->id() ?? 1,
                        'description' => ucfirst($refundType) . " Refund for Return #{$nextInvoice}",
                        'previous_balance' => $prevBal - $netAmount,
                        'closing_balance' => $prevBal,
                        'opening_balance' => 0,
                    ]);
                }
            } else {
                // Customer Ledger Adjustment:
                // No immediate cash paid out; return amount credited against customer balance
                $totalPaid = 0;
                $balance = $netAmount;

                if ($customer) {
                    $lastEntry = CustomerLedger::where('customer_id', $customerId)->lockForUpdate()->latest('id')->first();
                    $prevBal = $lastEntry ? (float)$lastEntry->closing_balance : (float)($customer->previous_balance ?? 0);
                    $newBal = $prevBal - $netAmount;

                    CustomerLedger::create([
                        'customer_id' => $customerId,
                        'admin_or_user_id' => auth()->id() ?? 1,
                        'description' => "Credit Note for Sale Return #{$nextInvoice}" . ($sale ? " (Inv #{$sale->invoice_no})" : ""),
                        'previous_balance' => $prevBal,
                        'closing_balance' => $newBal,
                        'opening_balance' => 0,
                    ]);

                    $customer->previous_balance = $newBal;
                    $customer->save();
                }
            }

            // Update Return Record
            $return->update([
                'bill_amount' => $grossSubtotal,
                'item_discount' => $totalItemDiscount,
                'extra_discount' => $extraDiscount,
                'net_amount' => $netAmount,
                'paid' => $totalPaid,
                'balance' => $balance,
            ]);

            // Update Original Sale Status (only if 100% returned)
            if ($sale) {
                $totalSold = (float)$sale->items->sum('total_pieces');
                $totalReturned = (float)SaleReturnItem::join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
                    ->where('sale_returns.sale_id', $sale->id)
                    ->sum('sale_return_items.qty');

                if ($totalReturned >= $totalSold && $totalSold > 0) {
                    $sale->update(['sale_status' => 'returned']);
                }
            }

            DB::commit();

            if ($request->input('action') === 'save_and_print') {
                return redirect()->route('sale.return.print', $return->id);
            }

            return redirect()->route('sale.return.index')->with('success', "Sale Return #{$nextInvoice} processed successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error processing return: ' . $e->getMessage());
        }
    }

    /**
     * View a specific sale return details
     */
    public function viewReturn($id)
    {
        $return = SaleReturn::with(['customer', 'sale', 'warehouse', 'items.product'])->findOrFail($id);
        return view('admin_panel.sale.sale_return.show', compact('return'));
    }

    /**
     * Printable Return Voucher
     */
    public function printReturn($id)
    {
        $return = SaleReturn::with(['customer', 'sale', 'warehouse', 'items.product.brand'])->findOrFail($id);
        return view('admin_panel.sale.sale_return.print', compact('return'));
    }
}
