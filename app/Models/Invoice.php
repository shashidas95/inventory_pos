<?php

namespace App\Models;

use App\Models\User;
use App\Models\InvoiceDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'invoice_number',
        'invoice_date',
        'subtotal_amount',
        'vat_percentage',
        'vat_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'status',
        'order_id'
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
    // Relation to the User who created this invoice
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation to invoice details

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_id');
    }
    public function getVatAmountAttribute()
    {
        return $this->subtotal_amount * ($this->vat_percentage / 100);
    }

    public function getFinalTotalAttribute()
    {
        return $this->subtotal_amount + $this->vat_amount - $this->discount_amount;
    }
}
