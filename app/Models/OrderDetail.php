<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = ['order_id', 'product_id', 'store_id', 'quantity', 'price', 'total_amount'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalAttribute()
    {
        return $this->quantity * $this->price;
    }

}
