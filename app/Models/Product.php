<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'description', 'price', 'quantity', 'image'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    /**
     * Many-to-Many relationship with stores
     * Allows tracking per-store stock
     */
    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_product')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Order Details this product is part of
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * Invoice Details this product is part of
     */
    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class);
    }
    public function getTotalQuantityAttribute()
    {
        // return $this->stores()->sum('quantity');
        return $this->stores->sum('pivot.quantity');
    }
}
