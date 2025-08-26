<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->profile->phone,
            'address' => $this->profile->address,
            'role' => ucfirst($this->role),
            'image' => $this->profile->avatar_url,
            'status' => $this->profile->email ? 'Active' : 'Inactive',
        ];
    }
}
