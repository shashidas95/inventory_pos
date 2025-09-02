<?php

namespace App\Models;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDetail extends Model
{
    protected $fillable = ['invoice_id', 'product_id', 'quantity', 'amount', 'total_amount'];


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
}
