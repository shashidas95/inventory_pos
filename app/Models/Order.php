<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = ['user_id', 'status', 'total'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }
    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id');
    }

    public function subtotal()
    {
        return $this->details->sum(fn($item) => $item->total);
    }
}
