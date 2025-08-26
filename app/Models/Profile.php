<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = ['user_id', 'avatar', 'phone', 'address'];
    protected $appends = ['avatar_url'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Accessor for full avatar URL.
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            // If avatar is stored in storage/app/public
            return asset('storage/' . $this->avatar);
        }
        // Default avatar
        return asset('images/default-avatar.png');
    }
}
