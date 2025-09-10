<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = ['name', 'city', 'address'];

    // A store can have many users
    public function users()
    {
        return $this->belongsToMany(User::class, 'store_user')
            ->withPivot('role')
            ->withTimestamps();
    }
    // 🔹 Relationship with products via pivot table
    public function products()
    {
        return $this->belongsToMany(Product::class, 'store_product')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    // Shortcut: get all managers
    public function managers()
    {
        return $this->users()->wherePivot('role', 'manager');
    }

    // Shortcut: get all staff
    public function staff()
    {
        return $this->users()->wherePivot('role', 'staff');
    }
}
