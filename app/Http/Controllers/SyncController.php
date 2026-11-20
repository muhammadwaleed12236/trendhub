<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    /**
     * Get the configured cloud sync URL with fallback.
     */
    private function getCloudSyncUrl(): string
    {
        return rtrim(env('CLOUD_SYNC_URL', 'https://trendhubstore.pk'), '/');
    }

    /**
     * Get the configured sync token with fallback.
     */
    private function getSyncToken(): string
    {
        return env('SYNC_TOKEN', 'trendhub_sync_default_token_2026');
    }

    /**
     * Returns current sync & network status for the frontend POS and dashboard.
     */
    public function syncStatus()
    {
        $pendingSales = Sale::where('is_synced', 0)->count();
        $pendingCustomers = Customer::where('is_synced', 0)->count();
        $cloudUrl = $this->getCloudSyncUrl();

        $isOnline = false;
        try {
            $resp = Http::timeout(3)
                ->withoutVerifying()
                ->get($cloudUrl . '/api/settings');
            $isOnline = $resp->successful();
        } catch (\Throwable $t) {
            $isOnline = false;
        }

        return response()->json([
            'status' => 'success',
            'is_online' => $isOnline,
            'pending_sales' => $pendingSales,
            'pending_customers' => $pendingCustomers,
            'total_pending' => $pendingSales + $pendingCustomers,
            'cloud_url' => $cloudUrl
        ]);
    }

    /**
     * Runs on the local server instance to push data to the cloud.
     */
    public function syncToCloud(Request $request = null)
    {
        $cloudSyncUrl = $this->getCloudSyncUrl();
        $syncToken = $this->getSyncToken();

        // 1. Fetch unsynced customers (up to 50)
        $customerModels = Customer::where('is_synced', 0)->limit(50)->get();
        $customers = $customerModels->map(function ($c) {
            if (empty($c->uuid)) {
                $c->uuid = (string) \Illuminate\Support\Str::uuid();
                $c->saveQuietly();
            }
            return $c->toArray();
        })->toArray();

        // 2. Fetch unsynced sales with items (up to 50 per batch)
        $saleModels = Sale::where('is_synced', 0)
            ->with('customer_relation')
            ->orderBy('id', 'asc')
            ->limit(50)
            ->get();

        $sales = $saleModels->map(function ($s) {
            if (empty($s->uuid)) {
                $s->uuid = (string) \Illuminate\Support\Str::uuid();
                $s->saveQuietly();
            }

            $sData = $s->toArray();

            // Map customer local ID to UUID
            $cust = $s->customer_relation ?? Customer::find($s->customer_id);
            $sData['customer_uuid'] = $cust ? $cust->uuid : null;

            // Load items and map product local ID to SKU
            $sData['items'] = SaleItem::where('sale_id', $s->id)->get()->map(function ($item) {
                $iData = $item->toArray();
                $prod = Product::find($item->product_id);
                $iData['product_code'] = $prod ? $prod->item_code : null;
                return $iData;
            })->toArray();

            return $sData;
        })->toArray();

        if (empty($customers) && empty($sales)) {
            return response()->json([
                'status' => 'success',
                'message' => 'All local invoices and data are fully synchronized with the cloud!',
                'pending_sales' => 0,
                'pending_customers' => 0
            ]);
        }

        try {
            // Send payload to cloud via HTTP Client (withoutVerifying to bypass local SSL errors)
            $response = Http::timeout(45)
                ->withoutVerifying()
                ->withHeaders([
                    'X-Sync-Token' => $syncToken,
                    'Accept' => 'application/json'
                ])
                ->post($cloudSyncUrl . '/api/receive-sync', [
                    'customers' => $customers,
                    'sales' => $sales
                ]);

            if ($response->successful()) {
                $resData = $response->json();
                if (isset($resData['status']) && $resData['status'] === 'success') {
                    // Update local records to synced
                    if (!empty($customers)) {
                        $custUuids = collect($customers)->pluck('uuid')->filter();
                        Customer::whereIn('uuid', $custUuids)->update(['is_synced' => 1]);
                    }
                    if (!empty($sales)) {
                        $saleUuids = collect($sales)->pluck('uuid')->filter();
                        Sale::whereIn('uuid', $saleUuids)->update(['is_synced' => 1]);
                    }

                    $remainingSales = Sale::where('is_synced', 0)->count();

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Cloud synchronization completed successfully! ' . count($sales) . ' sales synced.',
                        'synced_sales' => count($sales),
                        'synced_customers' => count($customers),
                        'remaining_sales' => $remainingSales
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => $resData['message'] ?? 'Sync failed on cloud server.'
                    ], 500);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cloud server responded with status: ' . $response->status()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Connection to cloud failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pulls latest products, prices and stock from cloud to keep local POS updated.
     */
    public function pullProductsFromCloud(Request $request = null)
    {
        $cloudSyncUrl = $this->getCloudSyncUrl();

        try {
            $resp = Http::timeout(45)
                ->withoutVerifying()
                ->get($cloudSyncUrl . '/api/products', ['per_page' => 1000]);

            if (!$resp->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to reach cloud products API. Status: ' . $resp->status()
                ], 500);
            }

            $body = $resp->json();
            $productsData = $body['data']['data'] ?? $body['data'] ?? [];

            if (empty($productsData) || !is_array($productsData)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No products returned from cloud server.',
                    'updated_count' => 0
                ]);
            }

            $updated = 0;
            $created = 0;

            DB::beginTransaction();
            foreach ($productsData as $pItem) {
                if (empty($pItem['item_code']) && empty($pItem['item_name'])) {
                    continue;
                }

                $localProduct = null;
                if (!empty($pItem['item_code'])) {
                    $localProduct = Product::where('item_code', $pItem['item_code'])->first();
                }

                if (!$localProduct && !empty($pItem['item_name'])) {
                    $localProduct = Product::where('item_name', $pItem['item_name'])->first();
                }

                $fields = [
                    'item_name' => $pItem['item_name'] ?? ($localProduct ? $localProduct->item_name : 'Unknown Product'),
                    'sale_price_per_piece' => $pItem['sale_price_per_piece'] ?? ($pItem['price'] ?? 0),
                    'sale_price_per_box' => $pItem['sale_price_per_box'] ?? 0,
                    'wholesale_price' => $pItem['wholesale_price'] ?? 0,
                    'color' => is_array($pItem['color'] ?? null) ? json_encode($pItem['color']) : ($pItem['color'] ?? null),
                    'is_active' => $pItem['is_active'] ?? 1,
                    'is_web_visible' => $pItem['is_web_visible'] ?? 0,
                    'size_mode' => $pItem['size_mode'] ?? 'by_size',
                    'total_stock_qty' => $pItem['total_stock_qty'] ?? ($pItem['total_stock'] ?? 0),
                ];

                if ($localProduct) {
                    $localProduct->update($fields);
                    $updated++;
                } else {
                    $fields['item_code'] = $pItem['item_code'] ?? ('ITEM-' . str_pad(rand(100, 9999), 4, '0', STR_PAD_LEFT));
                    Product::create($fields);
                    $created++;
                }
            }
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Products synchronized! Updated: {$updated}, Created: {$created}.",
                'updated_count' => $updated,
                'created_count' => $created
            ]);
        } catch (\Throwable $t) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to pull products: ' . $t->getMessage()
            ], 500);
        }
    }

    /**
     * Runs on the cloud server instance to receive pushed data.
     */
    public function receiveSync(Request $request)
    {
        $token = $request->header('X-Sync-Token') ?? $request->input('sync_token');
        if ($token !== $this->getSyncToken()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized sync request.'], 401);
        }

        $payload = $request->all();
        $customers = $payload['customers'] ?? [];
        $sales = $payload['sales'] ?? [];

        DB::beginTransaction();
        try {
            // Process customers
            foreach ($customers as $cData) {
                $uuid = $cData['uuid'] ?? null;
                if (!$uuid) {
                    continue;
                }
                unset($cData['id']);

                $customer = Customer::where('uuid', $uuid)->first();
                if (!$customer) {
                    $customer = new Customer($cData);
                    $customer->uuid = $uuid;
                    $customer->is_synced = 1;
                    $customer->save();
                } else {
                    $customer->update($cData);
                }
            }

            // Process sales
            $saleController = app(\App\Http\Controllers\SaleController::class);
            $balanceService = app(\App\Services\BalanceService::class);
            $transactionService = app(\App\Services\TransactionService::class);

            foreach ($sales as $sData) {
                $uuid = $sData['uuid'] ?? null;
                if (!$uuid) {
                    continue;
                }

                $items = $sData['items'] ?? [];

                unset($sData['id']);
                unset($sData['items']);

                $customerUuid = $sData['customer_uuid'] ?? null;
                if ($customerUuid) {
                    $cloudCustomer = Customer::where('uuid', $customerUuid)->first();
                    if ($cloudCustomer) {
                        $sData['customer_id'] = $cloudCustomer->id;
                    }
                }
                unset($sData['customer_uuid']);

                $sale = Sale::where('uuid', $uuid)->first();
                if (!$sale) {
                    // Check if invoice_no already exists with different uuid to prevent unique constraint crash
                    if (!empty($sData['invoice_no'])) {
                        $existingInv = Sale::where('invoice_no', $sData['invoice_no'])->first();
                        if ($existingInv) {
                            $sData['invoice_no'] = $sData['invoice_no'] . '-LOC';
                        }
                    }

                    $sale = new Sale($sData);
                    $sale->uuid = $uuid;
                    $sale->is_synced = 1;
                    $sale->save();

                    // Create sale items
                    foreach ($items as $iData) {
                        unset($iData['id']);
                        unset($iData['sale_id']);

                        $productCode = $iData['product_code'] ?? null;
                        if ($productCode) {
                            $cloudProduct = Product::where('item_code', $productCode)->first();
                            if ($cloudProduct) {
                                $iData['product_id'] = $cloudProduct->id;
                            }
                        }
                        unset($iData['product_code']);

                        $saleItem = new SaleItem($iData);
                        $saleItem->sale_id = $sale->id;
                        $saleItem->save();
                    }

                    // Process postings on the cloud
                    if ($sale->sale_status === 'posted') {
                        // 1. Deduct stock from warehouse
                        try {
                            $saleController->handleStockImpact($sale, 'out');
                        } catch (\Throwable $eStock) {
                            Log::warning('Stock impact sync warning: ' . $eStock->getMessage());
                        }

                        // 2. Legacy Ledger posting
                        try {
                            $saleController->updateLedger($sale);
                        } catch (\Throwable $eLedger) {
                            Log::warning('Ledger sync warning: ' . $eLedger->getMessage());
                        }

                        // 3. Professional Ledger posting (Debit AR, Credit Sales)
                        try {
                            $custForVoucher = $sale->customer_relation ?? Customer::find($sale->customer_id);
                            if ($custForVoucher) {
                                $date = $sale->created_at ? $sale->created_at->format('Y-m-d') : date('Y-m-d');
                                $balanceService->createSaleVoucher(
                                    $custForVoucher,
                                    $sale->total_net,
                                    $sale->invoice_no,
                                    $date
                                );
                            }
                        } catch (\Throwable $eVoucher) {
                            Log::warning('Voucher sync warning: ' . $eVoucher->getMessage());
                        }

                        // 4. Auto Receipt (cash payment posting)
                        try {
                            $transactionService->createReceiptFromSale($sale);
                        } catch (\Throwable $eReceipt) {
                            Log::warning('Receipt sync warning: ' . $eReceipt->getMessage());
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Sync payload processed successfully.']);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('Sync error on cloud: ' . $ex->getMessage());
            return response()->json(['status' => 'error', 'message' => $ex->getMessage()], 500);
        }
    }
}
