<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KosResource;
use App\Models\Kos;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $kos = $request->user()
            ->favorites()
            ->public()
            ->withAvg(['reviews as rating_avg' => fn ($q) => $q->where('is_hidden', false)], 'rating')
            ->withCount(['reviews as reviews_count' => fn ($q) => $q->where('is_hidden', false)])
            ->with('photos')
            ->latest()
            ->paginate(12);

        return KosResource::collection($kos);
    }

    public function store(Request $request, string $slug)
    {
        $kos = Kos::query()->public()->where('slug', $slug)->firstOrFail();

        $request->user()->favorites()->syncWithoutDetaching([$kos->id]);

        return response()->json(['message' => 'Kos disimpan ke favorit.', 'is_favorite' => true], 201);
    }

    public function destroy(Request $request, string $slug)
    {
        $kos = Kos::query()->public()->where('slug', $slug)->firstOrFail();

        $request->user()->favorites()->detach($kos->id);

        return response()->json(['message' => 'Kos dihapus dari favorit.', 'is_favorite' => false]);
    }
}
