<?php

namespace App\Models;

use App\Models\InvoiceDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = ['customer_id', 'user_id', 'invoice_number', 'invoice_date', 'total_amount', 'notes', 'status', 'order_id'];

    public function invoiceDetails(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class);
    }
}
