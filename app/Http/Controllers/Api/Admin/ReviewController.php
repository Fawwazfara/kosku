<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::query()
            ->with(['user:id,name,email', 'kos:id,name,slug'])
            ->when($request->query('filter') === 'hidden', fn ($q) => $q->where('is_hidden', true))
            ->when($request->query('filter') === 'visible', fn ($q) => $q->where('is_hidden', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return ReviewResource::collection($reviews);
    }

    public function hide(Request $request, Review $review): JsonResponse
    {
        $review->update(['is_hidden' => true]);

        return response()->json(['message' => 'Ulasan disembunyikan dari publik.']);
    }

    public function unhide(Request $request, Review $review): JsonResponse
    {
        $review->update(['is_hidden' => false]);

        return response()->json(['message' => 'Ulasan ditampilkan kembali.']);
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        $review->delete();

        return response()->json(['message' => 'Ulasan dihapus permanen.']);
    }
}
