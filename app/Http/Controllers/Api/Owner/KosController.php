<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKosRequest;
use App\Http\Requests\UpdateKosRequest;
use App\Http\Resources\KosDetailResource;
use App\Http\Resources\PhotoResource;
use App\Models\Kos;
use App\Models\KosPhoto;
use App\Support\PhotoManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KosController extends Controller
{
    public function index(Request $request)
    {
        $kos = $request->user()
            ->kos()
            ->withAvg(['reviews as rating_avg' => fn ($q) => $q->where('is_hidden', false)], 'rating')
            ->withCount(['reviews as reviews_count' => fn ($q) => $q->where('is_hidden', false)])
            ->with('photos')
            ->latest()
            ->get();

        return \App\Http\Resources\OwnerKosResource::collection($kos);
    }

    public function store(StoreKosRequest $request)
    {
        $data = $request->validated();
        $data['owner_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['name']);

        $kos = Kos::create($data);

        $kos->facilities()->sync($request->input('facility_ids', []));

        return response()->json([
            'message' => 'Kos dibuat. Menunggu verifikasi admin agar tampil di pencarian.',
            'kos' => new KosDetailResource($kos->fresh()),
        ], 201);
    }

    public function show(Request $request, Kos $kos)
    {
        $this->authorize('update', $kos);

        return new KosDetailResource($kos->load(['photos', 'facilities']));
    }

    public function update(UpdateKosRequest $request, Kos $kos)
    {
        $kos->update($request->validated());

        $kos->facilities()->sync($request->input('facility_ids', []));

        return response()->json([
            'message' => 'Kos diperbarui.',
            'kos' => new KosDetailResource($kos->fresh()),
        ]);
    }

    public function destroy(Request $request, Kos $kos)
    {
        $this->authorize('delete', $kos);

        $kos->delete();

        return response()->json(['message' => 'Kos dihapus.']);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = str($name)->slug()->limit(200, '')->value() ?: 'kos';

        $slug = $base;
        $i = 1;

        while (Kos::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.++$i;
        }

        return $slug;
    }
}
