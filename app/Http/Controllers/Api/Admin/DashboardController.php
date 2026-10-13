<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\KosResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\UserResource;
use App\Models\Kos;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'summary' => [
                'pending_owners' => User::query()->where('role', User::ROLE_OWNER)->where('is_verified', false)->where('is_active', true)->count(),
                'pending_kos' => Kos::query()->where('status', Kos::STATUS_PENDING)->count(),
                'active_kos' => Kos::query()->public()->count(),
                'hidden_reviews' => Review::query()->where('is_hidden', true)->count(),
                'users' => User::query()->count(),
                'full_kos' => Kos::query()->public()->where('available_rooms', 0)->count(),
            ],
        ]);
    }
}
