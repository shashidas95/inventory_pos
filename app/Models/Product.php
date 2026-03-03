<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    // public function stores()
    // {
    //     return $this->belongsToMany(Store::class, 'store_product')
    //         ->withPivot('quantity')
    //         ->withTimestamps();
    // }
    // Define the many-to-many relationship with Store
    public function stores(): BelongsToMany
    {
        // Assumes the pivot table is named 'product_store'
        return $this->belongsToMany(Store::class)->withTimestamps();
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
