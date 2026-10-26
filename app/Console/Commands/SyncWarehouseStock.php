<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncWarehouseStock extends Command
{
    protected $signature = 'stock:sync-warehouse-stock {--dry-run : Only check and report discrepancies without updating}';

    protected $description = 'Synchronize and repair warehouse stocks and total_pieces for all products based on live transactions and variants';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $this->info($isDryRun ? '--- DRY RUN: Checking Stock Discrepancies ---' : '--- Synchronizing Warehouse Stocks ---');

        $defaultWarehouse = Warehouse::first();
        $defaultWhId = $defaultWarehouse ? $defaultWarehouse->id : 1;

        $products = Product::with('warehouseStocks')->get();
        $fixedCount = 0;
        $missingStockCount = 0;

        foreach ($products as $product) {
            $ppb = ($product->pieces_per_box > 0) ? (float)$product->pieces_per_box : 1.0;
            $variants = [];
            if ($product->color) {
                try {
                    $parsed = is_string($product->color) ? json_decode($product->color, true) : $product->color;
                    if (is_array($parsed) && count($parsed) > 0 && (isset($parsed[0]['name']) || isset($parsed[0]['color']))) {
                        $variants = $parsed;
                    }
                } catch (\Exception $e) {}
            }

            $hasVariants = count($variants) > 0;

            if ($hasVariants) {
                $liveVariantStockSum = $this->calculateLiveVariantStockSum($product, $variants);

                // 1. If product has no warehouse_stocks record
                if ($product->warehouseStocks->isEmpty()) {
                    $missingStockCount++;
                    $this->warn("Product #{$product->id} ({$product->item_name}): Missing warehouse_stocks record. Setting to {$liveVariantStockSum}");
                    if (!$isDryRun) {
                        WarehouseStock::create([
                            'warehouse_id' => $defaultWhId,
                            'product_id'   => $product->id,
                            'total_pieces' => $liveVariantStockSum,
                            'quantity'     => $ppb > 0 ? round($liveVariantStockSum / $ppb, 2) : $liveVariantStockSum,
                            'price'        => 0,
                            'remarks'      => 'Auto-created during Stock Sync',
                        ]);
                        $fixedCount++;
                    }
                    continue;
                }

                // 2. Ensure existing default warehouse stock matches live variant sum
                $ws = $product->warehouseStocks->where('warehouse_id', $defaultWhId)->first() ?: $product->warehouseStocks->first();
                if ($ws) {
                    $oldPieces = (float)$ws->total_pieces;
                    if (abs($oldPieces - $liveVariantStockSum) >= 0.01) {
                        $this->line("Product #{$product->id} ({$product->item_name}) in WH #{$ws->warehouse_id}: Syncing total_pieces: {$oldPieces} -> {$liveVariantStockSum}");
                        if (!$isDryRun) {
                            $ws->total_pieces = $liveVariantStockSum;
                            $ws->quantity = $ppb > 0 ? round($liveVariantStockSum / $ppb, 2) : $liveVariantStockSum;
                            $ws->save();
                        }
                        $fixedCount++;
                    }
                }
            } else {
                // Product has no variants
                if ($product->warehouseStocks->isEmpty()) {
                    $missingStockCount++;
                    $this->warn("Product #{$product->id} ({$product->item_name}): Missing warehouse_stocks record.");
                    if (!$isDryRun) {
                        WarehouseStock::create([
                            'warehouse_id' => $defaultWhId,
                            'product_id'   => $product->id,
                            'total_pieces' => 0,
                            'quantity'     => 0,
                            'price'        => 0,
                            'remarks'      => 'Auto-created during Stock Sync',
                        ]);
                        $fixedCount++;
                    }
                    continue;
                }

                foreach ($product->warehouseStocks as $ws) {
                    $oldPieces = (float)$ws->total_pieces;
                    $oldQty = (float)$ws->quantity;
                    if ($oldPieces <= 0 && $oldQty > 0) {
                        $newPieces = round($oldQty * $ppb, 2);
                        $this->line("Product #{$product->id} ({$product->item_name}) in WH #{$ws->warehouse_id}: Recovered total_pieces from boxes: {$oldPieces} -> {$newPieces}");
                        if (!$isDryRun) {
                            $ws->total_pieces = $newPieces;
                            $ws->save();
                        }
                        $fixedCount++;
                    }
                }
            }
        }

        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Total Products Scanned: {$products->count()}");
        $this->info("Products with Missing Stock Records: {$missingStockCount}");

        if ($isDryRun) {
            $this->warn("Dry run complete. No records were modified. Run without --dry-run to apply fixes.");
        } else {
            $this->info("Successfully synchronized/fixed {$fixedCount} stock record(s).");
        }

        return 0;
    }

    private function calculateLiveVariantStockSum($product, $variants)
    {
        $salesList = DB::table('sale_items')
            ->where('product_id', $product->id)
            ->select('total_pieces', 'color')
            ->get();

        $webSalesList = DB::table('ecommerce_order_items as eoi')
            ->join('ecommerce_orders as eo', 'eo.id', '=', 'eoi.ecommerce_order_id')
            ->where('eoi.product_id', $product->id)
            ->where('eo.is_stock_deducted', 1)
            ->select('eoi.quantity as total_pieces', 'eoi.color', 'eoi.size')
            ->get();

        $salesListArray = $salesList->toArray();
        foreach ($webSalesList as $wItem) {
            $salesListArray[] = (object) [
                'total_pieces' => $wItem->total_pieces,
                'color' => json_encode([
                    'color' => $wItem->color ?: '-',
                    'size' => $wItem->size ?: '-'
                ])
            ];
        }
        $salesList = collect($salesListArray);

        $returnsList = DB::table('sale_return_items as sri')
            ->join('sale_returns as sr', 'sr.id', '=', 'sri.sale_return_id')
            ->where('sri.product_id', $product->id)
            ->select('sri.qty', 'sri.color', 'sr.sale_id')
            ->get();

        $saleIds = $returnsList->pluck('sale_id')->unique()->toArray();
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

        $purchasesList = DB::table('purchase_items as pi')
            ->join('purchases as pur', 'pur.id', '=', 'pi.purchase_id')
            ->where('pi.product_id', $product->id)
            ->whereIn('pur.status_purchase', ['approved', 'Returned', 'Partial'])
            ->select('pi.qty as total_pieces', 'pi.color')
            ->get();

        $purchaseReturnsList = DB::table('purchase_return_items as pri')
            ->where('pri.product_id', $product->id)
            ->select('pri.qty', 'pri.color')
            ->get();

        $adjList = DB::table('stock_movements')
            ->where('product_id', $product->id)
            ->where('type', 'adjustment')
            ->whereNotIn('ref_type', ['INIT'])
            ->select('qty', 'note')
            ->get();

        $totalVariantStock = 0;

        foreach ($variants as $v) {
            $vInitial = (float)($v['stock'] ?? 0);

            $purchased = 0;
            foreach ($purchasesList as $pItem) {
                if ($this->matchSaleItemToVariant($pItem, $v)) {
                    $purchased += (float) $pItem->total_pieces;
                }
            }

            $pReturned = 0;
            foreach ($purchaseReturnsList as $prItem) {
                if ($this->matchSaleItemToVariant($prItem, $v)) {
                    $pReturned += (float) $prItem->qty;
                }
            }

            $sold = 0;
            foreach ($salesList as $sItem) {
                if ($this->matchSaleItemToVariant($sItem, $v)) {
                    $sold += (float) $sItem->total_pieces;
                }
            }

            $returnedQty = 0;
            foreach ($returnsList as $rItem) {
                $rColor = $rItem->color;
                if (empty($rColor)) {
                    $saleColors = $saleItemsMap[$rItem->sale_id] ?? [];
                    $rColor = !empty($saleColors) ? $saleColors[0] : '';
                }
                $rItemCopy = (object)[
                    'qty' => $rItem->qty,
                    'color' => $rColor
                ];
                if ($this->matchSaleItemToVariant($rItemCopy, $v)) {
                    $returnedQty += (float) $rItem->qty;
                }
            }

            $adjustments = 0;
            foreach ($adjList as $adjItem) {
                if ($this->matchAdjustmentToVariant($adjItem, $v)) {
                    $adjustments += (float) $adjItem->qty;
                }
            }

            $vCurrentStock = max(0, $vInitial + $purchased - $sold + $returnedQty - $pReturned + $adjustments);
            $totalVariantStock += $vCurrentStock;
        }

        return $totalVariantStock;
    }

    private function matchSaleItemToVariant($saleItem, $variant)
    {
        $itemColor = $saleItem->color;
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

        $vColor = strtolower(trim($variant['color'] ?? '-'));
        $vSize = strtolower(trim($variant['size'] ?? '-'));
        $itemVColor = strtolower(trim($itemVariant['color'] ?? ($itemVariant['color_val'] ?? '-')));
        $itemVSize = strtolower(trim($itemVariant['size'] ?? ($itemVariant['size_val'] ?? '-')));

        if ($vColor === '') $vColor = '-';
        if ($vSize === '') $vSize = '-';
        if ($itemVColor === '') $itemVColor = '-';
        if ($itemVSize === '') $itemVSize = '-';

        return $vColor === $itemVColor && $vSize === $itemVSize;
    }

    private function matchAdjustmentToVariant($adjItem, $variant)
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
}
