<?php

namespace App\Models;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDetail extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'store_id',
        'quantity',
        'unit_price',
        'total_amount',
        'discount_amount',
        'subtotal_amount',
        'vat_percentage',
        'vat_amount',
        'final_total',
    ];


    // Define relationship to Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    // Optional: relationship to Invoice
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
