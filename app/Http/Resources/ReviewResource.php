<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Review */
class ReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->whenLoaded('user');
        $viewer = $request->user();
        $kos = $this->whenLoaded('kos');

        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_hidden' => $this->is_hidden,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $user ? new UserResource($this->user) : null,
            'can' => [
                'update' => $viewer?->can('update', $this->resource) ?? false,
                'delete' => $viewer?->can('delete', $this->resource) ?? false,
            ],
        ];
    }
}
