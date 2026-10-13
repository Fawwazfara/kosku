<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Kos */
class KosDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $request->user();

        return [
            ...(new KosResource($this->resource))->toArray($request),
            'description' => $this->description,
            'rules' => $this->rules,
            'wa_clicks' => $this->wa_clicks,
            'whatsapp_link' => whatsapp_link($this->whatsapp, $this->name, $this->price_min),
            'maps_link' => maps_link($this->latitude, $this->longitude),
            'is_favorite' => (bool) $viewer && $this->favoritedBy->contains($viewer->id),
            'review' => $viewer ? optional($this->reviews->firstWhere('user_id', $viewer->id))->only(['id', 'rating', 'comment']) : null,
            'owner' => [
                'name' => $this->owner?->name,
                'phone_display' => $this->owner?->phone,
            ],
            'photos' => PhotoResource::collection($this->photos),
            'facilities' => FacilityResource::collection($this->facilities),
            'reviews' => ReviewResource::collection($this->reviews->where('is_hidden', false)),
            'can' => [
                'update' => $viewer?->can('update', $this->resource) ?? false,
                'review' => $viewer?->can('create', Review::class) ?? false,
            ],
        ];
    }
}
