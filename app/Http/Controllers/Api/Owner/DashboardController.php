<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $kosIds = $user->kos()->pluck('id');

        return response()->json([
            'summary' => [
                'total_kos' => $kosIds->count(),
                'active_kos' => $user->kos()->where('status', Kos::STATUS_ACTIVE)->count(),
                'pending_kos' => $user->kos()->where('status', Kos::STATUS_PENDING)->count(),
                'total_rooms' => (int) $user->kos()->sum('total_rooms'),
                'available_rooms' => (int) $user->kos()->sum('available_rooms'),
                'wa_clicks' => (int) $user->kos()->sum('wa_clicks'),
                'rating_avg' => Review::query()
                    ->whereIn('kos_id', $kosIds)
                    ->where('is_hidden', false)
                    ->avg('rating'),
                'reviews_count' => Review::query()
                    ->whereIn('kos_id', $kosIds)
                    ->where('is_hidden', false)
                    ->count(),
            ],
            'latest_reviews' => Review::query()
                ->whereIn('kos_id', $kosIds)
                ->where('is_hidden', false)
                ->with(['user:id,name', 'kos:id,name,slug'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Review $r) => [
                    'id' => $r->id,
                    'rating' => $r->rating,
                    'comment' => $r->comment,
                    'created_at' => $r->created_at,
                    'user_name' => $r->user?->name,
                    'kos_name' => $r->kos?->name,
                    'kos_slug' => $r->kos?->slug,
                ]),
        ]);
    }
}
