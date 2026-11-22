<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceSeries extends Model
{
    use HasFactory;

    protected $table = 'invoice_series';

    protected $guarded = [];

    /**
     * Generate the next formatted invoice number for a given prefix.
     */
    public static function generateNextNo($prefix = null)
    {
        // Wrap in transaction with DB lock to prevent race condition / duplicate invoice_no
        return \DB::transaction(function () use ($prefix) {

            $series = null;

            if ($prefix) {
                $series = self::where('prefix', strtoupper(trim($prefix)))->lockForUpdate()->first();
            }

            if (!$series) {
                $series = self::where('is_default', 1)->lockForUpdate()->first()
                    ?: self::lockForUpdate()->first();
            }

            if (!$series) {
                return 'INV-0001';
            }

            $pref = strtoupper($series->prefix);
            $padding = $series->padding ?: 4;

            // Find highest existing invoice number in sales table for this prefix
            $lastSale = Sale::where('invoice_no', 'LIKE', $pref . '-%')
                ->orderBy('id', 'desc')
                ->first();

            $numFromSale = 0;
            if ($lastSale && $lastSale->invoice_no) {
                if (preg_match('/' . preg_quote($pref, '/') . '-(\d+)/i', $lastSale->invoice_no, $matches)) {
                    $numFromSale = (int) $matches[1];
                }
            }

            $nextNum = max((int) $series->next_number, $numFromSale + 1);

            // FOOLPROOF DUPLICATE PREVENTION:
            // Ensure the candidate invoice number does NOT already exist in sales table.
            // If it exists (e.g. from prior syncs or manual entries), loop forward to the next available number.
            do {
                $candidate = $pref . '-' . str_pad($nextNum, $padding, '0', STR_PAD_LEFT);
                $exists = Sale::where('invoice_no', $candidate)
                    ->orWhere('invoice_no', $candidate . '-LOC')
                    ->exists();
                if ($exists) {
                    $nextNum++;
                }
            } while ($exists);

            // Immediately increment & save to block next concurrent request
            $series->next_number = $nextNum + 1;
            $series->save();

            return $candidate;
        });
    }

    /**
     * Increment series counter after sale creation if applicable
     */
    public static function incrementCounterForInvoice($invoiceNo)
    {
        if (empty($invoiceNo)) return;

        // Support standard 'INV-2838' and synced suffix 'INV-2838-LOC'
        if (preg_match('/^([A-Z0-9]+)-(\d+)(?:-[A-Za-z0-9]+)?$/i', trim($invoiceNo), $matches)) {
            $prefix = strtoupper($matches[1]);
            $number = (int) $matches[2];

            $series = self::where('prefix', $prefix)->lockForUpdate()->first();
            if ($series) {
                if ($number >= $series->next_number) {
                    $series->next_number = $number + 1;
                    $series->save();
                }
            } else {
                self::create([
                    'prefix' => $prefix,
                    'next_number' => $number + 1,
                    'padding' => strlen($matches[2]),
                    'is_default' => 0
                ]);
            }
        }
    }
}
