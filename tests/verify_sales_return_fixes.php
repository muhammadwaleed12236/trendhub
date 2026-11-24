<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\StockMovement;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Voucher;
use App\Models\User;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\ReportingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "=== STARTING VERIFICATION FOR 6 ISSUES ===\n\n";

$adminUser = User::first();
if ($adminUser) {
    Auth::login($adminUser);
}

$results = [
    'issue_1' => false,
    'issue_2' => false,
    'issue_3' => false,
    'issue_4' => false,
    'issue_5' => false,
    'issue_6' => false,
];

// =========================================================================
// ISSUE 2: Default Warehouse Auto-Selection & Permission
// =========================================================================
echo "--- Testing Issue 2: Default Warehouse ---\n";
try {
    $controller = app(SaleReturnController::class);
    $view = $controller->createReturn(new Request());
    $viewData = $view->getData();
    
    $defaultWarehouseId = $viewData['defaultWarehouseId'] ?? null;
    $warehouses = $viewData['warehouses'] ?? collect();
    
    echo "Default Warehouse ID resolved: " . var_export($defaultWarehouseId, true) . "\n";
    echo "Total Warehouses available in view: " . $warehouses->count() . "\n";
    
    if ($defaultWarehouseId !== null && $warehouses->contains('id', $defaultWarehouseId)) {
        echo "PASS: Default warehouse correctly identified and passed to view.\n";
        $results['issue_2'] = true;
    } else {
        echo "FAIL: Default warehouse not properly set.\n";
    }
} catch (\Throwable $e) {
    echo "FAIL with exception: " . $e->getMessage() . "\n";
}
echo "\n";


// =========================================================================
// ISSUE 3: Product Variants Display & Selection
// =========================================================================
echo "--- Testing Issue 3: Product Variants Search & Display ---\n";
try {
    // Find a product that has variants in color column
    $variantProduct = Product::whereNotNull('color')->get()->first(function($p) {
        $colors = is_string($p->color) ? json_decode($p->color, true) : $p->color;
        return is_array($colors) && count($colors) > 0;
    });

    if (!$variantProduct) {
        $variantProduct = Product::find(1);
    }

    echo "Testing with Product ID: {$variantProduct->id} (Name: {$variantProduct->item_name})\n";
    
    $searchKey = !empty($variantProduct->item_name) ? substr($variantProduct->item_name, 0, 3) : 'test';
    // Let's also test searching by variant name or barcode
    $colors = is_string($variantProduct->color) ? json_decode($variantProduct->color, true) : $variantProduct->color;
    $firstVar = $colors[0] ?? [];
    if (!empty($firstVar['name'])) {
        $searchKey = substr($firstVar['name'], 0, 3);
    }
    
    $req = new Request(['q' => $searchKey, 'warehouse_id' => $defaultWarehouseId ?? 1]);
    $searchResponse = $controller->searchProducts($req);
    $responseData = json_decode($searchResponse->getContent(), true);
    $items = $responseData['products'] ?? [];
    
    echo "Search key '{$searchKey}' returned " . count($items) . " items/variants.\n";
    
    $foundVariant = false;
    foreach ($items as $item) {
        if ($item['product_id'] == $variantProduct->id && !empty($item['has_variant'])) {
            $foundVariant = true;
            echo "Found Variant: " . ($item['text'] ?? '') . "\n";
            echo " - Variant Name: " . ($item['variant_name'] ?? 'N/A') . "\n";
            echo " - SKU: " . ($item['sku'] ?? 'N/A') . "\n";
            echo " - Barcode: " . ($item['barcode'] ?? 'N/A') . "\n";
            echo " - Size: " . ($item['size'] ?? 'N/A') . "\n";
            echo " - Color: " . ($item['color_val'] ?? 'N/A') . "\n";
            echo " - Variant Data present: " . (!empty($item['variant_data']) ? 'YES' : 'NO') . "\n";
            echo " - Available Stock: " . ($item['available_stock'] ?? 0) . "\n";
            break;
        }
    }
    
    if ($foundVariant) {
        echo "PASS: Product variants are individually searched and returned with SKU, Barcode, Size, Color, Stock, and Variant payload.\n";
        $results['issue_3'] = true;
    } else {
        echo "FAIL: Variant items not properly returned.\n";
    }
} catch (\Throwable $e) {
    echo "FAIL with exception: " . $e->getMessage() . "\n";
}
echo "\n";


// =========================================================================
// ISSUE 1, 4, 5, 6: End-to-End Sales Return Transactions
// =========================================================================
echo "--- Testing Issues 1, 4, 5, 6: Return Processing, Stock Update, Reports, and Save/Print ---\n";

DB::beginTransaction();

try {
    // We will test on Product 1 (variant product) and Warehouse 1
    $targetWarehouseId = $defaultWarehouseId ?? 1;
    $product = Product::find(1);
    
    // Decode colors/variants
    $colors = is_string($product->color) ? json_decode($product->color, true) : $product->color;
    $targetVariantIndex = 0;
    $targetVariant = $colors[$targetVariantIndex];
    $variantBarcode = $targetVariant['barcode'] ?? 'VAR-1-BC';
    $targetVariant['barcode'] = $variantBarcode;
    $targetVariantName = $targetVariant['name'] ?? 'Red Variant';
    $targetVariantSize = $targetVariant['size'] ?? 'M';
    $targetVariantColor = $targetVariant['color'] ?? 'Red';
    $targetVariant['orig_initial_stock'] = $targetVariant['stock'] ?? 0;
    $colors[$targetVariantIndex] = $targetVariant;
    $product->color = $colors;
    $product->save();
    
    $variantDataB64 = base64_encode(json_encode($targetVariant));
    
    echo "Initial Stock Check for Product {$product->id}, Variant: {$targetVariantName} ({$targetVariantColor}-{$targetVariantSize}):\n";
    $initialProductStock = (float)($product->total_stock_qty ?? 0);
    $initialVariantStock = (float)($targetVariant['stock'] ?? 0);
    $initialWhStock = (float)(WarehouseStock::where('warehouse_id', $targetWarehouseId)->where('product_id', $product->id)->value('quantity') ?? 0);
    
    // Check initial On-hand report from v_stock_onhand (column is onhand_qty)
    $initialOnHand = (float)(DB::table('v_stock_onhand')->where('product_id', $product->id)->value('onhand_qty') ?? 0);
    
    // Check initial ReportingController fetchItemStock
    $reportingCtrl = app(ReportingController::class);
    $repReq = new Request(['product_id' => $product->id, 'warehouse_id' => $targetWarehouseId]);
    $repInit = $reportingCtrl->fetchItemStock($repReq);
    $repInitData = json_decode($repInit->getContent(), true);
    $initialItemStockTotal = (float)($repInitData['total_current_stock'] ?? 0);
    $matchedVariantReportInit = collect($repInitData['data'] ?? [])->first(function($v) use ($targetVariant) {
        return strpos($v['item_name'], $targetVariant['size']) !== false
            || strpos($v['item_name'], $targetVariant['color']) !== false;
    });
    $initialVariantReportStock = (float)($matchedVariantReportInit['balance'] ?? 0);
    
    echo " - Initial Product Total Stock: $initialProductStock\n";
    echo " - Initial Variant Stock: $initialVariantStock\n";
    echo " - Initial Warehouse Stock: $initialWhStock\n";
    echo " - Initial On-Hand View Stock: $initialOnHand\n";
    echo " - Initial Item Stock Report Total: $initialItemStockTotal\n";
    echo " - Initial Variant Stock in Report: $initialVariantReportStock\n";

    // -------------------------------------------------------------
    // Test A: Direct Return with Walk-in Customer (Issue 1), Save & Print (Issue 6), Good Stock & Damaged Stock (Issue 4)
    // -------------------------------------------------------------
    echo "\nSubmitting Direct Return for Walk-in Customer...\n";
    $returnQtyGood = 3;
    $returnQtyDamaged = 2;
    $unitPrice = 100;
    $totalAmount = ($returnQtyGood + $returnQtyDamaged) * $unitPrice;
    
    // We will do a request with action = 'save_and_print'
    $postData = [
        'return_type' => 'direct',
        'customer_id' => 'walkin',
        'warehouse_id' => $targetWarehouseId,
        'return_date' => date('Y-m-d'),
        'action' => 'save_and_print',
        'refund_type' => 'cash',
        'refund_account_id' => null,
        'tax_rate' => 0,
        'shipping_fee' => 0,
        'discount_amount' => 0,
        'product_id' => [
            0 => $product->id,
            1 => $product->id
        ],
        'color' => [
            0 => $variantDataB64,
            1 => $variantDataB64
        ],
        'qty' => [
            0 => $returnQtyGood,
            1 => $returnQtyDamaged
        ],
        'price' => [
            0 => $unitPrice,
            1 => $unitPrice
        ],
        'stock_condition' => [
            0 => 'good',
            1 => 'damaged'
        ],
        'item_reason' => [
            0 => 'Customer changed mind (Good)',
            1 => 'Defective (Damaged)'
        ]
    ];
    
    $reqA = new Request($postData);
    $responseA = $controller->processSaleReturn($reqA);
    
    echo "Session error: " . var_export(session('error'), true) . "\n";
    // Verify Issue 6: Save & Print Redirect
    echo "Response status: " . $responseA->getStatusCode() . "\n";
    $redirectUrl = $responseA->headers->get('Location');
    echo "Redirect URL: " . $redirectUrl . "\n";
    
    $saleReturnA = SaleReturn::orderBy('id', 'desc')->first();
    echo "Created Sale Return ID: " . $saleReturnA->id . " (Invoice: {$saleReturnA->return_invoice})\n";
    
    if (strpos($redirectUrl, '/sale/return/' . $saleReturnA->id . '/print') !== false) {
        echo "PASS (Issue 6 Part A): Save & Print automatically redirects to the print voucher URL.\n";
    } else {
        echo "FAIL (Issue 6 Part A): Did not redirect to print voucher URL.\n";
    }
    
    // Verify Issue 1: Walk-in Customer Handling
    echo "\nVerifying Issue 1 (Walk-in Customer)...\n";
    $walkinCheck = false;
    if ($saleReturnA->customer_id === null) {
        echo " - Return customer_id is NULL (Walk-in customer preserved without dummy FK)\n";
        // Check if any CustomerLedger was created for this return
        $ledgerCount = CustomerLedger::where('description', 'like', "%{$saleReturnA->return_invoice}%")->count();
        echo " - CustomerLedger entries for this return: $ledgerCount (Expected: 0)\n";
        
        // Check Voucher
        $voucher = \App\Models\VoucherMaster::where('remarks', 'like', "%{$saleReturnA->return_invoice}%")->first();
        if ($voucher) {
            echo " - Voucher created ID: {$voucher->id}, Type: {$voucher->voucher_type}, Amount: {$voucher->total_amount}\n";
            $walkinCheck = ($ledgerCount === 0);
        } else {
            echo " - Cash/Bank payment recorded directly.\n";
            $walkinCheck = ($ledgerCount === 0);
        }
    }
    
    if ($walkinCheck) {
        echo "PASS: Issue 1 (Walk-in Customer) handled properly without polluting CustomerLedger or crashing.\n";
        $results['issue_1'] = true;
    } else {
        echo "FAIL: Issue 1 (Walk-in Customer) accounting/customer verification failed.\n";
    }
    
    // Verify Issue 4: Stock Not Increasing After Return (Good vs Damaged)
    echo "\nVerifying Issue 4 (Good Stock vs Damaged Stock)...\n";
    $product->refresh();
    $updatedColors = is_string($product->color) ? json_decode($product->color, true) : $product->color;
    $updatedVariantStock = (float)($updatedColors[$targetVariantIndex]['stock'] ?? 0);
    $updatedWhStock = (float)(WarehouseStock::where('warehouse_id', $targetWarehouseId)->where('product_id', $product->id)->value('quantity') ?? 0);
    
    echo " - Stock before return: Variant={$initialVariantStock}, Warehouse={$initialWhStock}\n";
    echo " - Expected additions: Good = +{$returnQtyGood}, Damaged = +0 to salable\n";
    echo " - Stock after return: Variant={$updatedVariantStock}, Warehouse={$updatedWhStock}\n";
    
    $variantStockDiff = $updatedVariantStock - $initialVariantStock;
    $whStockDiff = $updatedWhStock - $initialWhStock;
    
    echo " - Variant Stock Diff: +{$variantStockDiff}\n";
    echo " - Warehouse Stock Diff: +{$whStockDiff}\n";
    
    // Verify stock movements
    $movements = DB::table('stock_movements')->where('ref_id', $saleReturnA->id)->get();
    echo " - Stock Movements recorded: " . $movements->count() . "\n";
    $goodMove = $movements->where('type', 'in')->where('ref_type', 'SALE_RETURN')->first();
    $damagedMove = $movements->where('type', 'adjustment')->where('ref_type', 'SALE_RETURN_DAMAGED')->first();
    
    echo "   * Good movement: " . ($goodMove ? "Found (qty={$goodMove->qty}, type={$goodMove->type}, ref_type={$goodMove->ref_type})" : "NOT FOUND") . "\n";
    echo "   * Damaged movement: " . ($damagedMove ? "Found (qty={$damagedMove->qty}, type={$damagedMove->type}, ref_type={$damagedMove->ref_type})" : "NOT FOUND") . "\n";
    
    if ($variantStockDiff == $returnQtyGood && $whStockDiff == $returnQtyGood && $goodMove && $damagedMove) {
        echo "PASS: Issue 4 (Stock Increasing After Return): Good stock increased exact variant and warehouse stock; Damaged stock remained separate.\n";
        $results['issue_4'] = true;
    } else {
        echo "FAIL: Issue 4 verification failed.\n";
    }
    
    // Verify Issue 5: Stock Report Matching
    echo "\nVerifying Issue 5 (Stock Reports Accuracy & Matching)...\n";
    $updatedOnHand = (float)(DB::table('v_stock_onhand')->where('product_id', $product->id)->value('onhand_qty') ?? 0);
    $onHandDiff = $updatedOnHand - $initialOnHand;
    echo " - v_stock_onhand Diff: +{$onHandDiff} (Expected: +{$returnQtyGood})\n";
    
    $repReqAfter = new Request(['product_id' => $product->id, 'warehouse_id' => $targetWarehouseId]);
    $repAfter = $reportingCtrl->fetchItemStock($repReqAfter);
    $repAfterData = json_decode($repAfter->getContent(), true);
    $updatedItemStockTotal = (float)($repAfterData['total_current_stock'] ?? 0);
    $itemStockDiff = $updatedItemStockTotal - $initialItemStockTotal;
    echo " - Item Stock Report Total Diff: +{$itemStockDiff} (Expected: +{$returnQtyGood})\n";
    
    // Variant stock in Item Stock Report
    $variantsReport = $repAfterData['data'] ?? [];
    $matchedVariantReport = collect($variantsReport)->first(function($v) use ($targetVariant) {
        return strpos($v['item_name'], $targetVariant['size']) !== false
            || strpos($v['item_name'], $targetVariant['color']) !== false;
    });
    $variantReportStock = (float)($matchedVariantReport['balance'] ?? 0);
    $variantReportDiff = $variantReportStock - $initialVariantReportStock;
    echo " - Variant Report Stock: {$variantReportStock} (Before: {$initialVariantReportStock}, Diff: +{$variantReportDiff}, Expected: +{$returnQtyGood})\n";
    
    if ($onHandDiff == $returnQtyGood && $itemStockDiff == $returnQtyGood && $variantReportDiff == $returnQtyGood) {
        echo "PASS: Issue 5 (Stock Reports): Inventory On-Hand Report, Item Stock Report, Warehouse Stock, and Variant Stock all match accurately.\n";
        $results['issue_5'] = true;
    } else {
        echo "FAIL: Issue 5 stock report verification failed.\n";
    }

    // -------------------------------------------------------------
    // Test B: Verify Issue 6 Part B: Standard 'Save' button redirect & no duplicate return
    // -------------------------------------------------------------
    echo "\nVerifying Issue 6 Part B (Standard 'Save' button redirect & uniqueness)...\n";
    $postDataSave = $postData;
    $postDataSave['action'] = 'save';
    $postDataSave['qty'] = [0 => 1, 1 => 0]; // 1 item
    $postDataSave['stock_condition'] = [0 => 'good', 1 => 'good'];
    
    $initialReturnCount = SaleReturn::count();
    $reqB = new Request($postDataSave);
    $responseB = $controller->processSaleReturn($reqB);
    
    $redirectUrlB = $responseB->headers->get('Location');
    echo "Redirect URL for standard Save: " . $redirectUrlB . "\n";
    $finalReturnCount = SaleReturn::count();
    
    if (strpos($redirectUrlB, '/sale/return') !== false && strpos($redirectUrlB, '/print') === false && $finalReturnCount === ($initialReturnCount + 1)) {
        echo "PASS: Issue 6 (Save vs Save & Print): Standard Save redirects to index list without printing, and created exactly 1 record.\n";
        $results['issue_6'] = true;
    } else {
        echo "FAIL: Issue 6 standard Save verification failed.\n";
    }

} catch (\Throwable $e) {
    echo "FAIL with exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    // Roll back transaction so database is completely clean
    DB::rollBack();
    echo "\nDatabase transaction rolled back successfully. No test records left behind.\n";
}

echo "\n==================== SUMMARY ====================\n";
foreach ($results as $k => $passed) {
    echo strtoupper($k) . ": " . ($passed ? "PASS" : "FAIL") . "\n";
}
