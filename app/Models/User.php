<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Otp;
use App\Models\Store;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'store_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    /**
     * Get the identifier that will be stored in the JWT token.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey;
    }
    /**
     * Return an array with custom claims to be added to the JWT token.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function opt()
    {
        return $this->hasOne(Otp::class);
    }
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }
    // A user can belong to many stores
    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    // Check if user is global admin
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Store-specific role check
    public function storeRole($storeId): ?string
    {
        $store = $this->stores()->where('store_id', $storeId)->first();
        return $store ? $store->pivot->role : null;
    }
}
