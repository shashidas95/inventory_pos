<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = ['email', 'otp','status'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
