<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        // Start querying only products that are visible on the website
        $query = Product::with(['category_relation', 'webImages'])
            ->withSum('warehouseStocks as total_stock', 'total_pieces')
            ->where('is_web_visible', 1);

        // Auto-hide out of stock products if auto_hide_out_of_stock is enabled
        $query->where(function ($q) {
            $q->where('auto_hide_out_of_stock', 0)
              ->orWhereExists(function ($subQuery) {
                  $subQuery->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('warehouse_stocks')
                      ->whereColumn('warehouse_stocks.product_id', 'products.id')
                      ->groupBy('warehouse_stocks.product_id')
                      ->havingRaw('SUM(total_pieces) > 0');
              });
        });

        // Filter by category
        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        // Filter by promotional tag (e.g. Featured, Flash Sale)
        if ($request->has('promo_tag') && $request->promo_tag != '') {
            $query->where('promo_tag', $request->promo_tag);
        }
        
        // Homepage only filter
        if ($request->has('show_on_homepage') && $request->show_on_homepage == 1) {
            $query->where('show_on_homepage', 1);
        }

        // Search by keyword
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        // Fetch paginated results
        $products = $query->paginate(12);

        // Transform results slightly to ensure standard price if web_sale_price is missing
        $products->getCollection()->transform(function ($product) {
            $product->final_price = $product->web_sale_price ?: $product->sale_price_per_piece;
            $product->description = $product->meta_description ?: "Designed as part of our premium modern luxury apparel collection, this piece stands out with premium stitching and elegant cuts.";
            $product->total_stock = (int) ($product->total_stock ?? 0);
            return $product;
        });

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['category_relation', 'webImages'])
            ->withSum('warehouseStocks as total_stock', 'total_pieces')
            ->where('is_web_visible', 1)
            ->findOrFail($id);
        $product->final_price = $product->web_sale_price ?: $product->sale_price_per_piece;
        $product->description = $product->meta_description ?: "Designed as part of our premium modern luxury apparel collection, this piece stands out with premium stitching and elegant cuts.";
        $product->total_stock = (int) ($product->total_stock ?? 0);

        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }
}
