<?php

namespace App\Http\Resources;

use App\Models\Kos;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Kos */
class KosResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $photos = $this->relationLoaded('photos') ? $this->photos : $this->photos()->get();
        $primary = $photos->firstWhere('is_primary', true) ?? $photos->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'address' => $this->address,
            'district' => $this->district,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'total_rooms' => $this->total_rooms,
            'available_rooms' => $this->available_rooms,
            'status' => $this->status,
            'is_full' => $this->is_full,
            'rating_avg' => $this->rating_avg !== null ? round((float) $this->rating_avg, 1) : null,
            'reviews_count' => $this->reviews_count,
            'primary_photo' => $primary ? [
                'id' => $primary->id,
                'url' => $primary->url,
            ] : null,
        ];
    }
}
