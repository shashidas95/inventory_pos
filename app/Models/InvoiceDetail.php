<?php

namespace App\Models;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDetail extends Model
{
    protected $fillable = ['invoice_id', 'product_id', 'quantity', 'amount', 'total_amount'];


    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
