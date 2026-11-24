<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\WarehouseStock;
use App\Models\StockMovement;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\User;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\ReportingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "========================================================================\n";
echo "      COMPREHENSIVE STOCK CONSISTENCY & AUDIT VERIFICATION TEST        \n";
echo "========================================================================\n\n";

$adminUser = User::first();
if ($adminUser) {
    Auth::login($adminUser);
}

$saleReturnController = app(SaleReturnController::class);
$reportingController = app(ReportingController::class);

$allPassed = true;

// =========================================================================
// TEST 1: VARIANT PRODUCT AUDIT (Product 1)
// =========================================================================
echo ">>> TEST 1: VARIANT PRODUCT STOCK CONSISTENCY AUDIT (Product 1)\n";

DB::beginTransaction();

try {
    $productId = 1;
    $warehouseId = 1;
    $product = Product::find($productId);
    
    $colors = is_string($product->color) ? json_decode($product->color, true) : $product->color;
    $targetVariantIndex = 0;
    $targetVariant = $colors[$targetVariantIndex];
    $targetVariantName = $targetVariant['name'] ?? 'test';
    $targetVariantSize = $targetVariant['size'] ?? 'bnb';
    $targetVariantColor = $targetVariant['color'] ?? 'bnm';
    $targetVariant['orig_initial_stock'] = $targetVariant['stock'] ?? 0;
    $colors[$targetVariantIndex] = $targetVariant;
    $product->color = $colors;
    $product->save();
    
    $variantDataB64 = base64_encode(json_encode($targetVariant));
    
    // 1. Initial State
    $initWhStock = (float)(WarehouseStock::where('warehouse_id', $warehouseId)->where('product_id', $productId)->value('quantity') ?? 0);
    $initOnHand = (float)(DB::table('v_stock_onhand')->where('product_id', $productId)->value('onhand_qty') ?? 0);
    
    $repInit = json_decode($reportingController->fetchItemStock(new Request(['product_id' => $productId, 'warehouse_id' => $warehouseId]))->getContent(), true);
    $initItemReportTotal = (float)($repInit['total_current_stock'] ?? 0);
    $initVariantRow = collect($repInit['data'] ?? [])->first(function($v) use ($targetVariantSize, $targetVariantColor) {
        return strpos($v['item_name'], $targetVariantSize) !== false || strpos($v['item_name'], $targetVariantColor) !== false;
    });
    $initItemReportVariant = (float)($initVariantRow['balance'] ?? 0);
    $initVariantModelStock = (float)($targetVariant['stock'] ?? 0);
    
    echo "  [Product 1 BEFORE Return]\n";
    echo "  - WarehouseStock (WH #{$warehouseId}): {$initWhStock}\n";
    echo "  - View v_stock_onhand: {$initOnHand}\n";
    echo "  - Item Stock Report Total: {$initItemReportTotal}\n";
    echo "  - Item Stock Report Variant ({$targetVariantSize}-{$targetVariantColor}): {$initItemReportVariant}\n";
    echo "  - Variant Model Stock (products.color): {$initVariantModelStock}\n\n";

    // 2. Perform Return: 5 Good Stock, 3 Damaged Stock
    $goodQty = 5;
    $damagedQty = 3;
    $unitPrice = 100;
    
    echo "  Submitting Direct Return: {$goodQty} Good Stock + {$damagedQty} Damaged Stock...\n";
    
    $reqData = [
        'return_type' => 'direct',
        'customer_id' => 'walkin',
        'warehouse_id' => $warehouseId,
        'return_date' => date('Y-m-d'),
        'action' => 'save',
        'refund_type' => 'cash',
        'product_id' => [$productId, $productId],
        'color' => [$variantDataB64, $variantDataB64],
        'qty' => [$goodQty, $damagedQty],
        'price' => [$unitPrice, $unitPrice],
        'stock_condition' => ['good', 'damaged'],
        'item_reason' => ['Customer Return (Good)', 'Damaged In Transit (Damaged)']
    ];
    
    $response = $saleReturnController->processSaleReturn(new Request($reqData));
    
    // 3. Post-Return State
    $postWhStock = (float)(WarehouseStock::where('warehouse_id', $warehouseId)->where('product_id', $productId)->value('quantity') ?? 0);
    $postOnHand = (float)(DB::table('v_stock_onhand')->where('product_id', $productId)->value('onhand_qty') ?? 0);
    
    $repPost = json_decode($reportingController->fetchItemStock(new Request(['product_id' => $productId, 'warehouse_id' => $warehouseId]))->getContent(), true);
    $postItemReportTotal = (float)($repPost['total_current_stock'] ?? 0);
    $postVariantRow = collect($repPost['data'] ?? [])->first(function($v) use ($targetVariantSize, $targetVariantColor) {
        return strpos($v['item_name'], $targetVariantSize) !== false || strpos($v['item_name'], $targetVariantColor) !== false;
    });
    $postItemReportVariant = (float)($postVariantRow['balance'] ?? 0);
    
    $product->refresh();
    $updatedColors = is_string($product->color) ? json_decode($product->color, true) : $product->color;
    $postVariantModelStock = (float)($updatedColors[$targetVariantIndex]['stock'] ?? 0);
    
    echo "  [Product 1 AFTER Return]\n";
    echo "  - WarehouseStock: {$postWhStock} (Diff: +" . ($postWhStock - $initWhStock) . " | Expected: +{$goodQty})\n";
    echo "  - View v_stock_onhand: {$postOnHand} (Diff: +" . ($postOnHand - $initOnHand) . " | Expected: +{$goodQty})\n";
    echo "  - Item Stock Report Total: {$postItemReportTotal} (Diff: +" . ($postItemReportTotal - $initItemReportTotal) . " | Expected: +{$goodQty})\n";
    echo "  - Item Stock Report Variant: {$postItemReportVariant} (Diff: +" . ($postItemReportVariant - $initItemReportVariant) . " | Expected: +{$goodQty})\n";
    echo "  - Variant Model Stock: {$postVariantModelStock} (Diff: +" . ($postVariantModelStock - $initVariantModelStock) . " | Expected: +{$goodQty})\n\n";

    // 4. Verify Movement History
    $lastReturn = SaleReturn::orderBy('id', 'desc')->first();
    $movements = DB::table('stock_movements')->where('ref_id', $lastReturn->id)->get();
    $goodMovement = $movements->where('type', 'in')->where('ref_type', 'SALE_RETURN')->first();
    $damagedMovement = $movements->where('type', 'adjustment')->where('ref_type', 'SALE_RETURN_DAMAGED')->first();
    
    echo "  Stock Movements Verification:\n";
    echo "  - Good Movement: " . ($goodMovement ? "Found (qty={$goodMovement->qty}, type={$goodMovement->type})" : "MISSING") . "\n";
    echo "  - Damaged Movement: " . ($damagedMovement ? "Found (qty={$damagedMovement->qty}, type={$damagedMovement->type})" : "MISSING") . "\n";

    $t1Passed = (
        ($postWhStock - $initWhStock) == $goodQty &&
        ($postOnHand - $initOnHand) == $goodQty &&
        ($postItemReportTotal - $initItemReportTotal) == $goodQty &&
        ($postItemReportVariant - $initItemReportVariant) == $goodQty &&
        ($postVariantModelStock - $initVariantModelStock) == $goodQty &&
        $goodMovement && $damagedMovement
    );

    if ($t1Passed) {
        echo ">>> RESULT TEST 1: PASS - All 5 stock sources increased by EXACTLY +{$goodQty} (Good Stock). Damaged Stock (+{$damagedQty}) was excluded from salable inventory across all sources.\n\n";
    } else {
        echo ">>> RESULT TEST 1: FAIL\n\n";
        $allPassed = false;
    }

} catch (\Throwable $e) {
    echo ">>> TEST 1 EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    $allPassed = false;
} finally {
    DB::rollBack();
    echo "  (Test 1 database transaction rolled back cleanly)\n\n";
}


// =========================================================================
// TEST 2: NON-VARIANT PRODUCT AUDIT (Product 3)
// =========================================================================
echo ">>> TEST 2: NON-VARIANT PRODUCT STOCK CONSISTENCY AUDIT (Product 3)\n";

DB::beginTransaction();

try {
    $productId = 3;
    $warehouseId = 1;
    $product = Product::find($productId);
    
    // 1. Initial State
    $initWhStock = (float)(WarehouseStock::where('warehouse_id', $warehouseId)->where('product_id', $productId)->value('total_pieces') ?? 0);
    $initOnHand = (float)(DB::table('v_stock_onhand')->where('product_id', $productId)->value('onhand_qty') ?? 0);
    
    $repInit = json_decode($reportingController->fetchItemStock(new Request(['product_id' => $productId, 'warehouse_id' => $warehouseId]))->getContent(), true);
    $initItemReportTotal = (float)($repInit['total_current_stock'] ?? 0);
    
    echo "  [Product 3 BEFORE Return]\n";
    echo "  - WarehouseStock (WH #{$warehouseId}): {$initWhStock}\n";
    echo "  - View v_stock_onhand: {$initOnHand}\n";
    echo "  - Item Stock Report Total: {$initItemReportTotal}\n\n";

    // 2. Perform Return: 4 Good Stock, 2 Damaged Stock
    $goodQty = 4;
    $damagedQty = 2;
    $unitPrice = 50;
    
    echo "  Submitting Direct Return: {$goodQty} Good Stock + {$damagedQty} Damaged Stock...\n";
    
    $reqData = [
        'return_type' => 'direct',
        'customer_id' => 'walkin',
        'warehouse_id' => $warehouseId,
        'return_date' => date('Y-m-d'),
        'action' => 'save',
        'refund_type' => 'cash',
        'product_id' => [$productId, $productId],
        'color' => ['', ''],
        'qty' => [$goodQty, $damagedQty],
        'price' => [$unitPrice, $unitPrice],
        'stock_condition' => ['good', 'damaged'],
        'item_reason' => ['Overstocked Return (Good)', 'Damaged Packaging (Damaged)']
    ];
    
    $response = $saleReturnController->processSaleReturn(new Request($reqData));
    
    // 3. Post-Return State
    $postWhStock = (float)(WarehouseStock::where('warehouse_id', $warehouseId)->where('product_id', $productId)->value('total_pieces') ?? 0);
    $postOnHand = (float)(DB::table('v_stock_onhand')->where('product_id', $productId)->value('onhand_qty') ?? 0);
    
    $repPost = json_decode($reportingController->fetchItemStock(new Request(['product_id' => $productId, 'warehouse_id' => $warehouseId]))->getContent(), true);
    $postItemReportTotal = (float)($repPost['total_current_stock'] ?? 0);
    
    echo "  [Product 3 AFTER Return]\n";
    echo "  - WarehouseStock: {$postWhStock} (Diff: +" . ($postWhStock - $initWhStock) . " | Expected: +{$goodQty})\n";
    echo "  - View v_stock_onhand: {$postOnHand} (Diff: +" . ($postOnHand - $initOnHand) . " | Expected: +{$goodQty})\n";
    echo "  - Item Stock Report Total: {$postItemReportTotal} (Diff: +" . ($postItemReportTotal - $initItemReportTotal) . " | Expected: +{$goodQty})\n\n";

    $t2Passed = (
        ($postWhStock - $initWhStock) == $goodQty &&
        ($postOnHand - $initOnHand) == $goodQty &&
        ($postItemReportTotal - $initItemReportTotal) == $goodQty
    );

    if ($t2Passed) {
        echo ">>> RESULT TEST 2: PASS - Non-variant product stock sources all increased by EXACTLY +{$goodQty} (Good Stock). Damaged Stock (+{$damagedQty}) was excluded.\n\n";
    } else {
        echo ">>> RESULT TEST 2: FAIL\n\n";
        $allPassed = false;
    }

} catch (\Throwable $e) {
    echo ">>> TEST 2 EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    $allPassed = false;
} finally {
    DB::rollBack();
    echo "  (Test 2 database transaction rolled back cleanly)\n\n";
}

// =========================================================================
// TEST 3: RE-CHECK OF ALL 6 FUNCTIONAL ISSUES
// =========================================================================
echo ">>> TEST 3: RE-VERIFYING ALL 6 ORIGINAL FUNCTIONAL ISSUES\n";
$output = shell_exec('php tests/verify_sales_return_fixes.php');
echo $output;

if (strpos($output, 'ISSUE_1: PASS') !== false &&
    strpos($output, 'ISSUE_2: PASS') !== false &&
    strpos($output, 'ISSUE_3: PASS') !== false &&
    strpos($output, 'ISSUE_4: PASS') !== false &&
    strpos($output, 'ISSUE_5: PASS') !== false &&
    strpos($output, 'ISSUE_6: PASS') !== false) {
    echo "\n>>> RE-VERIFICATION RESULT: ALL 6 ISSUES REMAIN FULLY PASSED.\n";
} else {
    echo "\n>>> RE-VERIFICATION RESULT: REGRESSION DETECTED.\n";
    $allPassed = false;
}

echo "\n========================================================================\n";
echo "OVERALL AUDIT STATUS: " . ($allPassed ? "ALL AUDITS PASSED - PRODUCTION READY" : "AUDIT FAILED") . "\n";
echo "========================================================================\n";
