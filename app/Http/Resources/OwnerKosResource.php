<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Kos */
class OwnerKosResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'district' => $this->district,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'whatsapp' => $this->whatsapp,
            'total_rooms' => $this->total_rooms,
            'available_rooms' => $this->available_rooms,
            'status' => $this->status,
            'wa_clicks' => $this->wa_clicks,
            'rating_avg' => $this->rating_avg !== null ? round((float) $this->rating_avg, 1) : null,
            'reviews_count' => $this->reviews_count,
            'rules' => $this->rules,
            'description' => $this->description,
            'visibility_label' => $this->statusLabel(),
        ];
    }
}
