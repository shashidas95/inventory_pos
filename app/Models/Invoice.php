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
        'store_id',
        'order_id',
        'invoice_number',
        'invoice_date',
        'total_amount',
        'discount_amount',
        'subtotal_amount',
        'vat_percentage',
        'vat_amount',
        'final_total',
        'notes',
        'status'
    ];


    public function details()
    {
        return $this->hasMany(InvoiceDetail::class);
    }



    public static function createFromOrder(Order $order, $vat = 5, $discount = 0)
    {
        $totalAmount = $order->details->sum('total_amount');
        $subtotal = $totalAmount - $discount;
        $vatAmount = $subtotal * ($vat / 100);
        $finalTotal = $subtotal + $vatAmount;

        $invoice = self::create([
            'customer_id'     => $order->user_id,
            'user_id'         => $order->user_id,
            'store_id'        => $order->store_id,
            'order_id'        => $order->id,
            'invoice_number'  => 'INV-' . time(),
            'invoice_date'    => now(),
            'total_amount'    => $totalAmount,
            'discount_amount' => $discount,
            'subtotal_amount' => $subtotal,
            'vat_percentage'  => $vat,
            'vat_amount'      => $vatAmount,
            'final_total'     => $finalTotal,
            'status'          => 'Paid',
        ]);

        foreach ($order->details as $detail) {
            $invoice->details()->create([
                'product_id'       => $detail->product_id,
                'quantity'         => $detail->quantity,
                'unit_price'       => $detail->price,
                'total_amount'     => $detail->total_amount,
                'discount_amount'  => 0,
                'subtotal_amount'  => $detail->total_amount,
                'vat_percentage'   => $vat,
                'vat_amount'       => $detail->total_amount * ($vat / 100),
                'final_total'      => $detail->total_amount * (1 + $vat / 100),
            ]);
        }

        return $invoice;
    }



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
        return $this->hasMany(InvoiceDetail::class, 'invoice_id')->with('store');;
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
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
