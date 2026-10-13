<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KosDetailResource;
use App\Http\Resources\KosResource;
use App\Http\Resources\PhotoResource;
use App\Models\Kos;
use Illuminate\Http\Request;

class KosController extends Controller
{
    public function index(Request $request)
    {
        $kos = Kos::query()
            ->public()
            ->search($request->query('q'))
            ->type($request->query('type'))
            ->priceBetween($request->query('price_min'), $request->query('price_max'))
            ->withFacilities($request->query('facilities'))
            ->when($request->boolean('available'), fn ($q) => $q->available())
            ->withAvg(['reviews as rating_avg' => fn ($q) => $q->where('is_hidden', false)], 'rating')
            ->withCount(['reviews as reviews_count' => fn ($q) => $q->where('is_hidden', false)])
            ->with('photos')
            ->orderByDesc('updated_at')
            ->when($request->query('sort') === 'cheapest', fn ($q) => $q->reorder()->orderBy('price_min'))
            ->when($request->query('sort') === 'rating', fn ($q) => $q->reorder()->orderByDesc('rating_avg'))
            ->when($request->query('sort') === 'newest', fn ($q) => $q->reorder()->orderByDesc('created_at'))
            ->paginate(min((int) $request->query('per_page', 12), 24))
            ->withQueryString();

        return KosResource::collection($kos);
    }

    public function show(Request $request, string $slug)
    {
        /** @var \App\Models\Kos|null $kos */
        $kos = Kos::query()
            ->public()
            ->where('slug', $slug)
            ->withAvg(['reviews as rating_avg' => fn ($q) => $q->where('is_hidden', false)], 'rating')
            ->withCount(['reviews as reviews_count' => fn ($q) => $q->where('is_hidden', false)])
            ->with(['owner:id,name,phone', 'photos', 'facilities', 'reviews.user', 'favoritedBy'])
            ->firstOrFail();

        return new KosDetailResource($kos);
    }

    public function trackWaClick(Request $request, string $slug)
    {
        $kos = Kos::query()->public()->where('slug', $slug)->firstOrFail();

        $kos->increment('wa_clicks');

        return response()->json(['whatsapp_link' => whatsapp_link($kos->whatsapp, $kos->name, $kos->price_min)]);
    }
}
