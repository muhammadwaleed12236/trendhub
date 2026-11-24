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
            } else {
                $return->original_net_amount = 0;
                $return->total_returned = (float)$return->net_amount;
                $return->new_net_amount = 0;
                $return->new_due_amount = 0;
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

        // Warehouses: Respect user/branch permissions and determine configured default warehouse
        $user = auth()->user();
        $canViewAllWarehouses = $user && (
            $user->email === 'admin@admin.com' ||
            $user->hasRole('Super Admin') ||
            $user->hasRole('Admin') ||
            $user->can('warehouse.view') ||
            $user->can('warehouse.stock.view')
        );

        $warehousesQuery = Warehouse::query();
        if ($user && !empty($user->branch_id)) {
            $warehousesQuery->where('branch_id', $user->branch_id);
        }
        if (!$canViewAllWarehouses && $user && !empty($user->warehouse_id)) {
            $warehousesQuery->where('id', $user->warehouse_id);
        }
        $warehouses = $warehousesQuery->orderBy('id')->get();

        // Determine configured default warehouse
        $defaultWarehouseId = null;
        if ($user && !empty($user->warehouse_id) && $warehouses->contains('id', $user->warehouse_id)) {
            $defaultWarehouseId = (int)$user->warehouse_id;
        }
        if (!$defaultWarehouseId) {
            $cfgWh = \App\Models\Setting::where('key', 'default_warehouse_id')->value('value')
                ?? \App\Models\SystemSetting::where('key', 'default_warehouse_id')->value('value');
            if ($cfgWh && $warehouses->contains('id', (int)$cfgWh)) {
                $defaultWarehouseId = (int)$cfgWh;
            }
        }
        if (!$defaultWarehouseId && $warehouses->isNotEmpty()) {
            $defaultWarehouseId = $warehouses->first()->id;
        }
        if (!$defaultWarehouseId) {
            $defaultWarehouseId = 1;
        }

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
            'defaultWarehouseId',
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
     * AJAX endpoint: Search products for Direct Sales Return
     */
    public function searchProducts(Request $request)
    {
        $term = $request->get('q') ?? $request->get('term') ?? '';

        $query = Product::query();

        if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'is_active')) {
            $query->where(function ($q) {
                $q->where('is_active', 1)->orWhereNull('is_active');
            });
        }

        if (!empty($term)) {
            $query->where(function ($q) use ($term) {
                $q->where('item_name', 'like', "%{$term}%")
                    ->orWhere('item_code', 'like', "%{$term}%")
                    ->orWhere('barcode_path', 'like', "%{$term}%")
                    ->orWhere('color', 'like', "%{$term}%");
            });
        }

        $warehouseId = $request->get('warehouse_id');

        $products = $query->with(['brand', 'unit', 'warehouseStocks'])
            ->limit(40)
            ->get();

        $results = [];

        foreach ($products as $p) {
            $ppb = (int)($p->pieces_per_box > 0 ? $p->pieces_per_box : 1);
            $basePrice = (float)($p->sale_price_per_piece > 0 
                ? $p->sale_price_per_piece 
                : ($p->purchase_price_per_piece > 0 ? $p->purchase_price_per_piece : 0));
            $brand = $p->brand->name ?? $p->brand_name ?? '';
            $unit = $p->unit->name ?? 'pc';
            $pCode = $p->item_code ?? $p->product_code ?? '';

            // Check if product has variants
            $variants = [];
            if (!empty($p->color)) {
                try {
                    $parsed = is_string($p->color) ? json_decode($p->color, true) : $p->color;
                    if (is_string($parsed)) {
                        $parsed = json_decode($parsed, true);
                    }
                    if (is_array($parsed) && count($parsed) > 0 && (isset($parsed[0]['name']) || isset($parsed[0]['color']))) {
                        $variants = $parsed;
                    }
                } catch (\Exception $e) {}
            }

            if (count($variants) > 0) {
                // Fetch variant stock calculations using ERP transaction calculation logic
                $variantStocks = $this->calculateProductVariantStocks($p, $variants, $warehouseId);

                foreach ($variants as $vIdx => $v) {
                    $vName = $v['name'] ?? $p->item_name;
                    $vSize = (isset($v['size']) && $v['size'] !== '-') ? $v['size'] : '-';
                    $vColor = (isset($v['color']) && $v['color'] !== '-') ? $v['color'] : '-';
                    $vBarcode = $v['barcode'] ?? $p->barcode_path ?? '';
                    $vSku = $v['sku'] ?? $pCode;
                    $vPrice = (float)($v['sale_price'] ?? ($v['purch_price'] ?? $basePrice));
                    $vUnit = $v['unit'] ?? $unit;
                    $vStock = (float)($variantStocks[$vIdx] ?? ($v['stock'] ?? 0));

                    // Filter if term is present
                    if (!empty($term)) {
                        $matchesTerm = (
                            stripos($p->item_name, $term) !== false ||
                            stripos($pCode, $term) !== false ||
                            stripos($vName, $term) !== false ||
                            stripos($vSize, $term) !== false ||
                            stripos($vColor, $term) !== false ||
                            stripos($vSku, $term) !== false ||
                            stripos($vBarcode, $term) !== false
                        );
                        if (!$matchesTerm) {
                            continue;
                        }
                    }

                    $variantJson = json_encode($v);
                    $encodedVariant = base64_encode($variantJson);

                    $displayText = ($vSku ? "[{$vSku}] " : '') . "{$p->item_name} - {$vName} (Size: {$vSize}, Color: {$vColor})" 
                        . ($vBarcode ? " [Barcode: {$vBarcode}]" : '') 
                        . " | Stock: {$vStock} {$vUnit} | Rs. " . number_format($vPrice, 2);

                    $results[] = [
                        'id' => $p->id . '|var|' . $encodedVariant,
                        'product_id' => $p->id,
                        'item_name' => $p->item_name,
                        'variant_name' => $vName,
                        'size' => $vSize,
                        'color_val' => $vColor,
                        'sku' => $vSku,
                        'barcode' => $vBarcode,
                        'brand' => $brand,
                        'price' => $vPrice,
                        'pieces_per_box' => $ppb,
                        'unit' => $vUnit,
                        'available_stock' => $vStock,
                        'variant_data' => $encodedVariant,
                        'has_variant' => true,
                        'text' => $displayText,
                    ];
                }
            } else {
                // Product has no variants: display base product
                $whStock = $p->warehouseStocks;
                if ($warehouseId) {
                    $whStock = $whStock->where('warehouse_id', $warehouseId);
                }
                $availableStock = (float)$whStock->sum('total_pieces');

                $results[] = [
                    'id' => (string)$p->id,
                    'product_id' => $p->id,
                    'item_name' => $p->item_name,
                    'variant_name' => '',
                    'size' => '-',
                    'color_val' => '-',
                    'sku' => $pCode,
                    'barcode' => $p->barcode_path ?? '',
                    'brand' => $brand,
                    'price' => $basePrice,
                    'pieces_per_box' => $ppb,
                    'unit' => $unit,
                    'available_stock' => $availableStock,
                    'variant_data' => '',
                    'has_variant' => false,
                    'text' => ($pCode ? "[{$pCode}] " : '') . $p->item_name . ($brand ? " ({$brand})" : '') . " | Stock: {$availableStock} {$unit} | Rs. " . number_format($basePrice, 2),
                ];
            }
        }

        return response()->json([
            'status' => true,
            'products' => $results
        ]);
    }

    /**
     * AJAX endpoint: Get sales invoices by Customer ID
     */
    public function getInvoicesByCustomer($customerId, Request $request)
    {
        $query = Sale::query()->where('sale_status', '!=', 'cancelled');

        if ($customerId && $customerId !== 'all') {
            if ($customerId === 'walkin' || $customerId === 'walk-in' || $customerId === 'walking') {
                $query->where(function ($q) {
                    $q->whereNull('customer_id')
                      ->orWhereNotNull('walkin_name')
                      ->orWhereHas('customer_relation', function ($cq) {
                          $cq->where('customer_type', 'Walking Customer')
                             ->orWhere('customer_id', 'CUST-WALK');
                      });
                });
            } else {
                $cust = Customer::find($customerId);
                if ($cust && ($cust->customer_type === 'Walking Customer' || $cust->customer_id === 'CUST-WALK')) {
                    $query->where(function ($q) use ($customerId) {
                        $q->where('customer_id', $customerId)
                          ->orWhereNull('customer_id')
                          ->orWhereNotNull('walkin_name');
                    });
                } else {
                    $query->where('customer_id', $customerId);
                }
            }
        }

        if ($request->has('q') && !empty($request->q)) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('invoice_no', 'like', "%{$searchTerm}%")
                  ->orWhere('total_net', 'like', "%{$searchTerm}%");
            });
        }

        // Return all previous sales invoices of the customer
        $sales = $query->latest('id')
            ->take(500)
            ->get(['id', 'invoice_no', 'customer_id', 'walkin_name', 'created_at', 'total_net', 'sale_status']);

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

        $returnType = $request->input('return_type', 'invoice');
        $isDirect = ($returnType === 'direct');

        $validated = $request->validate([
            'return_type' => 'required|in:invoice,direct',
            'sale_id' => $isDirect ? 'nullable' : 'required|exists:sales,id',
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

        // Validate that return quantity does NOT exceed remaining returnable quantity for this invoice (Only for invoice-based returns)
        $sale = (!$isDirect && !empty($validated['sale_id'])) ? Sale::where('id', $validated['sale_id'])->lockForUpdate()->first() : null;
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

            // Resolve Customer ID (fallback to sale's customer or Walk-in Customer)
            $rawCustomerId = $validated['customer_id'] ?? null;
            $isWalkin = false;
            if ($rawCustomerId === 'walkin' || $rawCustomerId === 'walk-in' || $rawCustomerId === 'walking' || empty($rawCustomerId)) {
                if (!$sale || empty($sale->customer_id)) {
                    $isWalkin = true;
                    $customerId = null;
                } else {
                    $customerId = $sale->customer_id;
                }
            } else {
                $customerId = $rawCustomerId;
            }

            $customer = null;
            if (!$isWalkin && $customerId && $customerId !== 'all') {
                $customer = Customer::find($customerId);
                if ($customer && ($customer->customer_type === 'Walking Customer' || $customer->customer_id === 'CUST-WALK')) {
                    $isWalkin = true;
                    $customerId = null;
                    $customer = null;
                }
            }

            // Create Sale Return Header
            $return = SaleReturn::create([
                'sale_id' => $isDirect ? null : ($validated['sale_id'] ?? null),
                'return_type' => $returnType,
                'return_invoice' => $nextInvoice,
                'customer_id' => $customerId,
                'warehouse_id' => $validated['warehouse_id'],
                'return_date' => $validated['return_date'],
                'refund_type' => $isWalkin ? ($validated['refund_type'] === 'bank' ? 'bank' : 'cash') : $validated['refund_type'],
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
                $color = $request->color[$idx] ?? null;

                $product = Product::find($productId);
                $ppb = (int)($product ? ($product->pieces_per_box > 0 ? $product->pieces_per_box : 1) : 1);

                $itemGross = round($qty * $price, 2);
                $lineTotal = max(0, round($itemGross - $itemDisc, 2));

                $boxes = floor($qty / $ppb);
                $loosePieces = $qty % $ppb;

                // Save Return Item with variant color
                SaleReturnItem::create([
                    'sale_return_id' => $return->id,
                    'product_id' => $productId,
                    'is_manual' => $isDirect ? 1 : 0,
                    'product_name' => $product ? ($product->item_name ?? $product->product_name) : null,
                    'color' => $color,
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

                // Extract variant info for note and exact variant stock update
                $variantInfo = [];
                $variantDesc = '';
                if (!empty($color)) {
                    $b64Decoded = base64_decode($color, true);
                    if ($b64Decoded !== false) {
                        $variantInfo = json_decode($b64Decoded, true) ?: [];
                    }
                    if (empty($variantInfo)) {
                        $variantInfo = json_decode($color, true) ?: [];
                    }
                    if (!empty($variantInfo)) {
                        $vN = $variantInfo['name'] ?? '';
                        $vS = $variantInfo['size'] ?? ($variantInfo['size_val'] ?? '');
                        $vC = $variantInfo['color'] ?? ($variantInfo['color_val'] ?? '');
                        $variantDesc = trim(($vN ? "$vN " : '') . ($vS || $vC ? "($vS | $vC)" : ''));
                    }
                }

                $whNote = "Warehouse #{$validated['warehouse_id']}";
                $varNote = $variantDesc ? " | Variant: {$variantDesc}" : "";
                $notePrefix = $isDirect ? "Direct Return #{$nextInvoice}" : "Return #{$nextInvoice}";

                // Inventory Update Logic:
                // Good Stock: Added back to salable inventory and warehouse stock
                // Damaged Stock: Do not add to salable stock; track separately
                if ($condition === 'good') {
                    // 1. Warehouse Stock
                    $stock = WarehouseStock::where('warehouse_id', $validated['warehouse_id'])
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();

                    if ($stock) {
                        $currentTotalPieces = (float)$stock->total_pieces;
                        if ($currentTotalPieces == 0 && $stock->quantity > 0) {
                            $currentTotalPieces = (float)$stock->quantity * $ppb;
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

                    // 2. Exact Variant Stock Update (Issue 3 & 4)
                    if ($product) {
                        if (!empty($product->color)) {
                            $parsedVariants = is_string($product->color) ? json_decode($product->color, true) : $product->color;
                            if (is_array($parsedVariants) && count($parsedVariants) > 0) {
                                $variantUpdated = false;
                                foreach ($parsedVariants as &$pv) {
                                    if ($this->matchVariantToItemColor($pv, $color, $variantInfo)) {
                                        if (!isset($pv['orig_initial_stock'])) {
                                            $pv['orig_initial_stock'] = $pv['stock'] ?? 0;
                                        }
                                        $oldVarStock = (float)($pv['stock'] ?? 0);
                                        $pv['stock'] = $oldVarStock + $qty;
                                        $pv['available_stock'] = $pv['stock'];
                                        $variantUpdated = true;
                                        break; // Update exact variant only!
                                    }
                                }
                                unset($pv);
                                if ($variantUpdated) {
                                    $product->color = json_encode($parsedVariants);
                                }
                            }
                        }

                        // 3. Update Product Model Stock Totals
                        $currentWhPieces = (float)WarehouseStock::where('product_id', $productId)->sum('total_pieces');
                        $product->total_stock_qty = $currentWhPieces;
                        if ($product->size_mode === 'by_pieces') {
                            $product->piece_quantity = (int)$currentWhPieces;
                        } elseif ($product->size_mode === 'by_cartons' || $product->size_mode === 'by_size') {
                            $product->boxes_quantity = (int)floor($currentWhPieces / $ppb);
                            $product->loose_pieces = (int)($currentWhPieces % $ppb);
                        }
                        $product->save();
                    }

                    // Inward stock movement for good stock
                    $movements[] = [
                        'product_id' => $productId,
                        'type' => 'in',
                        'qty' => $qty,
                        'ref_type' => 'SALE_RETURN',
                        'ref_id' => $return->id,
                        'note' => "{$notePrefix} (Good Stock) | {$whNote}{$varNote}",
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                } else {
                    // Damaged Stock: type is 'adjustment' with qty = 0 so it does NOT increase salable stock in v_stock_onhand or warehouse inventory!
                    $movements[] = [
                        'product_id' => $productId,
                        'type' => 'adjustment',
                        'qty' => 0,
                        'ref_type' => 'SALE_RETURN_DAMAGED',
                        'ref_id' => $return->id,
                        'note' => "{$notePrefix} (Damaged Stock: {$qty} pcs - Not Restocked) | {$whNote}{$varNote}",
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

            $refundType = $isWalkin ? ($validated['refund_type'] === 'bank' ? 'bank' : 'cash') : $validated['refund_type'];
            $totalPaid = 0;
            $balance = 0;
            $voucherService = app(VoucherService::class);
            $balanceService = app(BalanceService::class);
            $arAccountId = $balanceService->getAccountsReceivableId();
            $salesRevenueId = $balanceService->getSalesRevenueId();

            if ($isWalkin) {
                // WALK-IN CUSTOMER ACCOUNTING (Matches ERP configuration in TransactionService):
                // Walk-in customers have no Accounts Receivable or Customer Ledger debt.
                // Refund is paid out via Cash or Bank Account directly against Sales Revenue.
                $refundAccId = $validated['refund_account_id'] ?? null;
                if (!$refundAccId) {
                    if ($refundType === 'bank') {
                        $bankAcc = Account::where('title', 'like', '%Bank%')->orWhere('account_code', 'BANK')->first();
                        $refundAccId = $bankAcc ? $bankAcc->id : $balanceService->getCashAccountId();
                    } else {
                        $refundAccId = $balanceService->getCashAccountId();
                    }
                }

                // Payment Voucher (Money Out):
                // Dr. Sales Revenue (Reduces Income)
                // Cr. Cash / Bank Account
                $walkinLines = [
                    [
                        'account_id' => $salesRevenueId,
                        'debit' => $netAmount,
                        'credit' => 0,
                        'narration' => "Sales Return Refund for Walk-in Return #{$nextInvoice}",
                    ],
                    [
                        'account_id' => $refundAccId,
                        'debit' => 0,
                        'credit' => $netAmount,
                        'narration' => "Refund paid via " . ucfirst($refundType) . " for Walk-in Return #{$nextInvoice}",
                    ]
                ];

                $walkinVoucherData = [
                    'voucher_type' => VoucherMaster::TYPE_PAYMENT,
                    'date' => $validated['return_date'],
                    'status' => VoucherMaster::STATUS_POSTED,
                    'party_type' => null,
                    'party_id' => null,
                    'payment_from' => 'Walk-in',
                    'remarks' => "Refund for Walk-in " . ($isDirect ? "Direct " : "") . "Sale Return #{$nextInvoice}",
                ];

                $voucherService->createVoucher($walkinVoucherData, $walkinLines, auth()->id());

                $totalPaid = $netAmount;
                $balance = 0;
            } else {
                // REGISTERED CUSTOMER ACCOUNTING:
                // 1. Double-Entry Accounting: Create Credit Note Journal Voucher
                // Dr. Sales Revenue (Reduces Income)
                // Cr. Accounts Receivable (Customer)
                $jvNarration = $isDirect ? "Credit Note for Direct Sale Return #{$nextInvoice}" : "Credit Note for Sale Return #{$nextInvoice}";
                $jvLines = [
                    [
                        'account_id' => $salesRevenueId,
                        'debit' => $netAmount,
                        'credit' => 0,
                        'narration' => $jvNarration,
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
                    'remarks' => $isDirect 
                        ? "Credit Note for Direct Sale Return #{$nextInvoice}" 
                        : "Credit Note for Sale Return #{$nextInvoice}" . ($sale ? " (Invoice #{$sale->invoice_no})" : ""),
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
                        'remarks' => ucfirst($refundType) . " Refund for " . ($isDirect ? "Direct " : "") . "Sale Return #{$nextInvoice}",
                    ];

                    $voucherService->createVoucher($pvData, $pvLines, auth()->id());

                    // Customer Ledger audit trail: Credit Note and Refund entries
                    if ($customer) {
                        $lastEntry = CustomerLedger::where('customer_id', $customerId)->lockForUpdate()->latest('id')->first();
                        $prevBal = $lastEntry ? (float)$lastEntry->closing_balance : (float)($customer->previous_balance ?? 0);

                        $creditDesc = $isDirect
                            ? "Credit Note for Direct Return #{$nextInvoice}"
                            : "Credit Note for Return #{$nextInvoice}" . ($sale ? " (Inv #{$sale->invoice_no})" : "");

                        // Credit Note entry
                        CustomerLedger::create([
                            'customer_id' => $customerId,
                            'admin_or_user_id' => auth()->id() ?? 1,
                            'description' => $creditDesc,
                            'previous_balance' => $prevBal,
                            'closing_balance' => $prevBal - $netAmount,
                            'opening_balance' => 0,
                        ]);

                        // Refund entry
                        CustomerLedger::create([
                            'customer_id' => $customerId,
                            'admin_or_user_id' => auth()->id() ?? 1,
                            'description' => ucfirst($refundType) . " Refund for " . ($isDirect ? "Direct " : "") . "Return #{$nextInvoice}",
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

                        $creditDesc = $isDirect
                            ? "Credit Note for Direct Sale Return #{$nextInvoice}"
                            : "Credit Note for Sale Return #{$nextInvoice}" . ($sale ? " (Inv #{$sale->invoice_no})" : "");

                        CustomerLedger::create([
                            'customer_id' => $customerId,
                            'admin_or_user_id' => auth()->id() ?? 1,
                            'description' => $creditDesc,
                            'previous_balance' => $prevBal,
                            'closing_balance' => $newBal,
                            'opening_balance' => 0,
                        ]);

                        $customer->previous_balance = $newBal;
                        $customer->save();
                    }
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

            // Update Original Sale Status (only for invoice-based returns and if 100% returned)
            if (!$isDirect && $sale) {
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

            $successMsg = $isDirect 
                ? "Direct Sale Return #{$nextInvoice} processed successfully."
                : "Sale Return #{$nextInvoice} processed successfully.";

            return redirect()->route('sale.return.index')->with('success', $successMsg);

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

    /**
     * Helper to calculate current available stock for each variant of a product
     */
    private function calculateProductVariantStocks(Product $product, array $variants, $warehouseId = null): array
    {
        $salesQuery = DB::table('sale_items')->where('product_id', $product->id);
        if ($warehouseId) {
            $salesQuery->where('warehouse_id', $warehouseId);
        }
        $salesList = $salesQuery->select('total_pieces', 'color')->get();

        $returnsQuery = DB::table('sale_return_items as sri')
            ->join('sale_returns as sr', 'sr.id', '=', 'sri.sale_return_id')
            ->where('sri.product_id', $product->id)
            ->where(function ($q) {
                $q->where('sri.stock_condition', 'good')->orWhereNull('sri.stock_condition');
            });
        if ($warehouseId) {
            $returnsQuery->where('sri.warehouse_id', $warehouseId);
        }
        $returnsList = $returnsQuery->select('sri.qty', 'sri.color', 'sr.sale_id')->get();

        $saleIds = $returnsList->pluck('sale_id')->filter()->unique()->toArray();
        $saleItemsMap = [];
        if (!empty($saleIds)) {
            $siList = DB::table('sale_items')
                ->whereIn('sale_id', $saleIds)
                ->where('product_id', $product->id)
                ->select('sale_id', 'color')
                ->get();
            foreach ($siList as $si) {
                $saleItemsMap[$si->sale_id][] = $si->color;
            }
        }

        $purchasesQuery = DB::table('purchase_items as pi')
            ->join('purchases as pur', 'pur.id', '=', 'pi.purchase_id')
            ->where('pi.product_id', $product->id)
            ->whereIn('pur.status_purchase', ['approved', 'Returned', 'Partial']);
        if ($warehouseId) {
            $purchasesQuery->where('pur.warehouse_id', $warehouseId);
        }
        $purchasesList = $purchasesQuery->select('pi.qty as total_pieces', 'pi.color')->get();

        $purchaseReturnsQuery = DB::table('purchase_return_items as pri')
            ->where('pri.product_id', $product->id);
        $purchaseReturnsList = $purchaseReturnsQuery->select('pri.qty', 'pri.color')->get();

        $adjQuery = DB::table('stock_movements')
            ->where('product_id', $product->id)
            ->where('type', 'adjustment')
            ->whereNotIn('ref_type', ['INIT']);
        if ($warehouseId) {
            $adjQuery->where('note', 'like', "%Warehouse #{$warehouseId}%");
        }
        $adjList = $adjQuery->select('qty', 'note')->get();

        $stocks = [];
        foreach ($variants as $idx => $v) {
            $initial = (float)($v['orig_initial_stock'] ?? $v['initial_stock'] ?? $v['stock'] ?? 0);

            $purchased = 0;
            foreach ($purchasesList as $pItem) {
                if ($this->matchItemToVariant($pItem, $v)) {
                    $purchased += (float)$pItem->total_pieces;
                }
            }

            $pReturned = 0;
            foreach ($purchaseReturnsList as $prItem) {
                if ($this->matchItemToVariant($prItem, $v)) {
                    $pReturned += (float)$prItem->qty;
                }
            }

            $sold = 0;
            foreach ($salesList as $sItem) {
                if ($this->matchItemToVariant($sItem, $v)) {
                    $sold += (float)$sItem->total_pieces;
                }
            }

            $returnedQty = 0;
            foreach ($returnsList as $rItem) {
                $rColor = $rItem->color;
                if (empty($rColor) && !empty($rItem->sale_id)) {
                    $saleColors = $saleItemsMap[$rItem->sale_id] ?? [];
                    $rColor = !empty($saleColors) ? $saleColors[0] : '';
                }
                $rItemCopy = (object)['qty' => $rItem->qty, 'color' => $rColor];
                if ($this->matchItemToVariant($rItemCopy, $v)) {
                    $returnedQty += (float)$rItem->qty;
                }
            }

            $adjustments = 0;
            foreach ($adjList as $adjItem) {
                if ($this->matchAdjustmentToVariant($adjItem, $v)) {
                    $adjustments += (float)$adjItem->qty;
                }
            }

            $stocks[$idx] = max(0, $initial + $purchased - $sold + $returnedQty - $pReturned + $adjustments);
        }

        return $stocks;
    }

    private function matchItemToVariant($item, $variant): bool
    {
        $itemColor = $item->color ?? '';
        if (empty($itemColor)) return false;

        $itemVariant = [];
        $b64Decoded = base64_decode($itemColor, true);
        if ($b64Decoded !== false) {
            $json = json_decode($b64Decoded, true);
            if (is_array($json)) $itemVariant = $json;
        }
        if (empty($itemVariant)) {
            $json = json_decode($itemColor, true);
            if (is_array($json)) $itemVariant = $json;
        }

        if (empty($itemVariant)) {
            return strtolower(trim($itemColor)) === strtolower(trim($variant['color'] ?? ''));
        }

        $vBarcode = strtolower(trim($variant['barcode'] ?? ''));
        $itemVBarcode = strtolower(trim($itemVariant['barcode'] ?? ''));
        if (!empty($vBarcode) && !empty($itemVBarcode) && $vBarcode === $itemVBarcode) {
            return true;
        }

        $vColor = strtolower(trim($variant['color'] ?? '-'));
        $vSize = strtolower(trim($variant['size'] ?? '-'));
        $itemVColor = strtolower(trim($itemVariant['color'] ?? ($itemVariant['color_val'] ?? '-')));
        $itemVSize = strtolower(trim($itemVariant['size'] ?? ($itemVariant['size_val'] ?? '-')));

        if ($vColor === '') $vColor = '-';
        if ($vSize === '') $vSize = '-';
        if ($itemVColor === '') $itemVColor = '-';
        if ($itemVSize === '') $itemVSize = '-';

        $matchColorSize = ($vColor === $itemVColor && $vSize === $itemVSize);
        if ($matchColorSize) {
            $vName = strtolower(trim($variant['name'] ?? ''));
            $itemVName = strtolower(trim($itemVariant['name'] ?? ($itemVariant['variant_name'] ?? '')));
            if (!empty($vName) && !empty($itemVName) && $vName !== '-' && $itemVName !== '-') {
                return $vName === $itemVName;
            }
            return true;
        }

        return false;
    }

    private function matchAdjustmentToVariant($adjItem, $variant): bool
    {
        $note = strtolower($adjItem->note ?? '');
        if (empty($note)) return false;

        $vName  = strtolower(trim($variant['name'] ?? ''));
        $vSize  = strtolower(trim($variant['size'] ?? '-'));
        $vColor = strtolower(trim($variant['color'] ?? '-'));

        if (preg_match('/variant:\s*([^|\(]+)/i', $note, $matches)) {
            $noteVariantName = strtolower(trim($matches[1]));
            if (!empty($vName) && $noteVariantName !== $vName) return false;
            if (!empty($vName) && $noteVariantName === $vName) {
                return true;
            }
        }

        $hasSpecificAttributes = ($vSize !== '-' && !empty($vSize)) || ($vColor !== '-' && !empty($vColor));
        if ($hasSpecificAttributes) {
            $sizeMatch = true;
            if ($vSize !== '-' && !empty($vSize)) {
                $sizeMatch = preg_match('/\b' . preg_quote($vSize, '/') . '\b/i', $note) === 1;
            }
            $colorMatch = true;
            if ($vColor !== '-' && !empty($vColor)) {
                $colorMatch = preg_match('/\b' . preg_quote($vColor, '/') . '\b/i', $note) === 1;
            }
            return $sizeMatch && $colorMatch;
        }

        if (!empty($vName)) {
            return preg_match('/\b' . preg_quote($vName, '/') . '\b/i', $note) === 1;
        }

        return false;
    }

    private function matchVariantToItemColor(array $variant, $itemColor, array $variantInfo): bool
    {
        if (empty($itemColor) && empty($variantInfo)) {
            return false;
        }

        $vBarcode = strtolower(trim($variant['barcode'] ?? ''));
        $infoBarcode = strtolower(trim($variantInfo['barcode'] ?? ''));
        if (!empty($vBarcode) && !empty($infoBarcode) && $vBarcode === $infoBarcode) {
            return true;
        }

        $vColor = strtolower(trim($variant['color'] ?? '-'));
        $vSize = strtolower(trim($variant['size'] ?? '-'));
        $infoColor = strtolower(trim($variantInfo['color'] ?? ($variantInfo['color_val'] ?? '-')));
        $infoSize = strtolower(trim($variantInfo['size'] ?? ($variantInfo['size_val'] ?? '-')));

        if ($vColor === '') $vColor = '-';
        if ($vSize === '') $vSize = '-';
        if ($infoColor === '') $infoColor = '-';
        if ($infoSize === '') $infoSize = '-';

        $colorSizeMatch = ($vColor === $infoColor && $vSize === $infoSize);
        if ($colorSizeMatch) {
            $vName = strtolower(trim($variant['name'] ?? ''));
            $infoName = strtolower(trim($variantInfo['name'] ?? ($variantInfo['variant_name'] ?? '')));
            if (!empty($vName) && !empty($infoName) && $vName !== '-' && $infoName !== '-') {
                return $vName === $infoName;
            }
            return true;
        }

        return false;
    }
}
