<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Purchase extends Model
{
    use HasFactory;
    use SoftDeletes;
    // app/Models/Purchase.php
    protected $table = 'purchases'; // if it's not default


//     DB_DATABASE=binsult1_sweetsultan_db
// DB_USERNAME=binsult1_sweetsultan_db
// DB_PASSWORD=fywW8BpmDgGBF5uKXAvy

// main wala


// DB_DATABASE=binsult1_biz
// DB_USERNAME=binsult1_biz
// DB_PASSWORD=waleed@123
// local wala

//    CHECK KA CONCEPT SET KARNA HAI PAYMENT VOUCHER MAI AGAR MAINY CHEQUE DIYA 4 TAREKH KA TOU OS DATE KO MINUS HO

    protected $fillable = [
        'invoice_no',
        'supplier',
        'purchase_date',
        'warehouse_id',
        'item_category',
        'item_name',
        'quantity',
        'price',
        'total',
        'note',
        'unit',
        'total_price',
        'discount',
        'Payable_amount',
        'paid_amount',
        'due_amount',
        'status',
        'is_return'
    ];
}
