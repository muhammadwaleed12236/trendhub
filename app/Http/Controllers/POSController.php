<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountHead;
use App\Models\Customer;
use App\Models\Product;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class POSController extends Controller
{
    public function index()
    {
        // 1. Fetch all active products
        $products = Product::where('is_active', true)->with('warehouseStocks')->get();

        $posProducts = [];
        foreach ($products as $p) {
            $ppb = $p->pieces_per_box > 0 ? $p->pieces_per_box : 1;

            $variants = [];
            if ($p->color) {
                try {
                    $parsed = is_string($p->color) ? json_decode($p->color, true) : $p->color;
                    if (is_array($parsed) && count($parsed) > 0 && isset($parsed[0]['name'])) {
                        $variants = $parsed;
                    }
                } catch (\Exception $e) {}
            }

            if (count($variants) > 0) {
                // Fetch all sales, returns, purchases, and purchase returns for this product to distribute
                $salesList = DB::table('sale_items')
                    ->where('product_id', $p->id)
                    ->select('total_pieces', 'color')
                    ->get();

                $returnsList = DB::table('sale_return_items as sri')
                    ->join('sale_returns as sr', 'sr.id', '=', 'sri.sale_return_id')
                    ->leftJoin('sale_items as si', function($join) {
                        $join->on('si.sale_id', '=', 'sr.sale_id')
                             ->on('si.product_id', '=', 'sri.product_id');
                    })
                    ->where('sri.product_id', $p->id)
                    ->select('sri.qty', 'si.color')
                    ->get();

                $purchasesList = DB::table('purchase_items as pi')
                    ->join('purchases as pur', 'pur.id', '=', 'pi.purchase_id')
                    ->where('pi.product_id', $p->id)
                    ->where('pur.status_purchase', 'approved')
                    ->select('pi.qty as total_pieces', 'pi.color')
                    ->get();

                $purchaseReturnsList = DB::table('purchase_return_items as pri')
                    ->where('pri.product_id', $p->id)
                    ->select('pri.qty', 'pri.color')
                    ->get();

                $variantItems = [];
                $totalStockPieces = 0;

                foreach ($variants as $v) {
                    $size = (isset($v['size']) && $v['size'] !== '-') ? " {$v['size']}" : '';
                    $color = (isset($v['color']) && $v['color'] !== '-') ? " ({$v['color']})" : '';
                    $vName = ($v['name'] ?? $p->item_name) . $size . $color;
                    
                    $initial = (float) ($v['stock'] ?? 0);

                    // Calculate Purchased variant qty
                    $purchased = 0;
                    foreach ($purchasesList as $pItem) {
                        if ($this->matchSaleItemToVariant($pItem, $v)) {
                            $purchased += (float) $pItem->total_pieces;
                        }
                    }

                    // Calculate Purchase Returned variant qty
                    $pReturned = 0;
                    foreach ($purchaseReturnsList as $prItem) {
                        if ($this->matchSaleItemToVariant($prItem, $v)) {
                            $pReturned += (float) $prItem->qty;
                        }
                    }
                    
                    // Calculate Sold variant qty
                    $sold = 0;
                    foreach ($salesList as $sItem) {
                        if ($this->matchSaleItemToVariant($sItem, $v)) {
                            $sold += (float) $sItem->total_pieces;
                        }
                    }

                    // Calculate Returned variant qty
                    $returnedQty = 0;
                    foreach ($returnsList as $rItem) {
                        if ($this->matchSaleItemToVariant($rItem, $v)) {
                            $returnedQty += (float) $rItem->qty;
                        }
                    }

                    $vBalance = max(0, $initial + $purchased - $sold + $returnedQty - $pReturned);
                    $totalStockPieces += $vBalance;

                    $vStockDisplay = $vBalance;
                    if (($p->size_mode === 'by_cartons' || $p->size_mode === 'by_size') && $ppb > 1) {
                        $vBoxes = floor($vBalance / $ppb);
                        $vLoose = $vBalance % $ppb;
                        $vStockDisplay = $vLoose > 0 ? "$vBoxes.$vLoose" : $vBoxes;
                    }

                    $v['current_stock'] = $vStockDisplay;
                    $variantJson = json_encode($v);

                    $variantItems[] = [
                        'id' => $p->id . '|variant|' . base64_encode($variantJson),
                        'name' => $vName,
                        'size_val' => $v['size'] ?? '-',
                        'color_val' => $v['color'] ?? '-',
                        'price' => $v['sale_price'] ?? $p->sale_price_per_piece ?? 0,
                        'wholesale_price' => $v['wholesale_price'] ?? $p->wholesale_price ?? 0,
                        'weight_per_piece' => $v['weight_per_piece'] ?? $p->weight_per_piece ?? 0,
                        'stock_pieces' => $vBalance,
                        'stock' => $vStockDisplay,
                        'variant_data' => base64_encode($variantJson)
                    ];
                }

                $totalStockDisplay = $totalStockPieces;
                if (($p->size_mode === 'by_cartons' || $p->size_mode === 'by_size') && $ppb > 1) {
                    $boxes = floor($totalStockPieces / $ppb);
                    $loose = $totalStockPieces % $ppb;
                    $totalStockDisplay = $loose > 0 ? "$boxes.$loose" : $boxes;
                }

                $posProducts[] = [
                    'id' => $p->id,
                    'name' => $p->item_name,
                    'sku' => $p->item_code ?? '',
                    'stock' => $totalStockDisplay,
                    'stock_pieces' => $totalStockPieces,
                    'size_mode' => $p->size_mode,
                    'pieces_per_box' => $ppb,
                    'price' => $p->sale_price_per_piece ?? 0,
                    'wholesale_price' => $p->wholesale_price ?? 0,
                    'weight_per_piece' => $p->weight_per_piece ?? 0,
                    'image' => $p->image ? asset('uploads/products/'.$p->image) : null,
                    'variants' => $variantItems,
                ];

            } else {
                $stockPieces = (float) ($p->warehouseStocks->sum('total_pieces') ?? 0);
                $stockDisplay = $stockPieces;
                if (($p->size_mode === 'by_cartons' || $p->size_mode === 'by_size') && $ppb > 1) {
                    $boxes = floor($stockPieces / $ppb);
                    $loose = $stockPieces % $ppb;
                    $stockDisplay = $loose > 0 ? "$boxes.$loose" : $boxes;
                }

                $posProducts[] = [
                    'id' => $p->id,
                    'name' => $p->item_name,
                    'sku' => $p->item_code ?? '',
                    'stock' => $stockDisplay,
                    'stock_pieces' => $stockPieces,
                    'size_mode' => $p->size_mode,
                    'pieces_per_box' => $ppb,
                    'price' => $p->sale_price_per_piece ?? 0,
                    'wholesale_price' => $p->wholesale_price ?? 0,
                    'weight_per_piece' => $p->weight_per_piece ?? 0,
                    'image' => $p->image ? asset('uploads/products/'.$p->image) : null,
                    'variants' => [],
                ];
            }
        }

        // 2. Fetch all customers
        $customers = Customer::orderBy('customer_name')->get();

        // 3. Fetch all Cash/Bank Accounts
        $cashAndBankHeads = AccountHead::whereIn('name', ['Cash', 'Bank'])->pluck('id');
        $accounts = Account::whereIn('head_id', $cashAndBankHeads)->orderBy('title')->get();

        return view('admin_panel.pos.index', compact('posProducts', 'customers', 'accounts'));
    }

    private function matchSaleItemToVariant($saleItem, $variant)
    {
        $itemColor = $saleItem->color;
        if (empty($itemColor)) {
            return false;
        }

        $itemVariant = [];
        $b64Decoded = base64_decode($itemColor, true);
        if ($b64Decoded !== false) {
            $json = json_decode($b64Decoded, true);
            if (is_array($json)) {
                $itemVariant = $json;
            }
        }
        if (empty($itemVariant)) {
            $json = json_decode($itemColor, true);
            if (is_array($json)) {
                $itemVariant = $json;
            }
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
}
