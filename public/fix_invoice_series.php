<?php
/**
 * Emergency Fix: invoice_series next_number reset
 * Run this ONCE on live server: https://trendhubstore.pk/fix_invoice_series.php?token=trendhub_sync_default_token_2026
 * DELETE this file after running!
 */

if (($_GET['token'] ?? '') !== 'trendhub_sync_default_token_2026') {
    die('Unauthorized');
}

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Find max invoice number currently in sales table
$lastSale = DB::table('sales')
    ->where('invoice_no', 'LIKE', 'INV-%')
    ->orderBy('id', 'desc')
    ->first();

$maxNum = 0;
if ($lastSale && $lastSale->invoice_no) {
    if (preg_match('/INV-(\d+)/i', $lastSale->invoice_no, $m)) {
        $maxNum = (int)$m[1];
    }
}

$nextNum = $maxNum + 1;

// 2. Update invoice_series
$updated = DB::table('invoice_series')
    ->where('is_default', 1)
    ->update(['next_number' => $nextNum]);

if (!$updated) {
    // Try any INV prefix series
    $updated = DB::table('invoice_series')
        ->where('prefix', 'INV')
        ->update(['next_number' => $nextNum]);
}

// 3. Report
$series = DB::table('invoice_series')->get();

echo "<h2>✅ Invoice Series Fixed!</h2>";
echo "<p><b>Last Sale Invoice:</b> " . ($lastSale->invoice_no ?? 'None') . "</p>";
echo "<p><b>Max Number Found:</b> {$maxNum}</p>";
echo "<p><b>next_number set to:</b> {$nextNum}</p>";
echo "<hr><h3>All invoice_series rows:</h3><pre>";
print_r($series->toArray());
echo "</pre>";
echo "<p style='color:red'><b>⚠️ DELETE this file from server now! (public/fix_invoice_series.php)</b></p>";
