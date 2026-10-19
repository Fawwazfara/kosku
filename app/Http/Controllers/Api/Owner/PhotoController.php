<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\PhotoUpdateRequest;
use App\Http\Requests\PhotoUploadRequest;
use App\Http\Resources\PhotoResource;
use App\Models\Kos;
use App\Models\KosPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function store(PhotoUploadRequest $request, Kos $kos): JsonResponse
    {
        $this->authorize('managePhotos', $kos);

        $order = (int) KosPhoto::query()->where('kos_id', $kos->id)->max('sort_order');
        $count = $kos->photos()->count();
        $sisa = max(0, Kos::MAX_PHOTOS - $count);
        $files = array_slice($request->file('photos'), 0, $sisa);

        if (empty($files)) {
            return response()->json([
                'message' => 'Batas maksimal '.Kos::MAX_PHOTOS.' foto sudah tercapai.',
            ], 422);
        }

        $created = [];

        foreach ($files as $file) {
            $path = $file->store("kos/{$kos->id}", 'public');

            $created[] = $kos->photos()->create([
                'path' => $path,
                'is_primary' => false,
                'sort_order' => $order += 1,
            ]);
        }

        if ($count === 0 && filled($created)) {
            $created[0]->update(['is_primary' => true]);
        }

        return response()->json([
            'message' => count($created).' foto diunggah.',
            'photos' => PhotoResource::collection($kos->photos()->get()),
        ], 201);
    }

    public function update(PhotoUpdateRequest $request, KosPhoto $photo): JsonResponse
    {
        $this->authorize('managePhotos', $photo->kos);

        $photo->update([
            'is_primary' => $request->boolean('is_primary'),
            ...$request->has('sort_order') ? ['sort_order' => $request->integer('sort_order')] : [],
        ]);

        if ($photo->is_primary) {
            KosPhoto::query()->where('kos_id', $photo->kos_id)
                ->whereKeyNot($photo->id)
                ->update(['is_primary' => false]);
        }

        return response()->json([
            'message' => 'Foto diperbarui.',
            'photos' => PhotoResource::collection($photo->kos->photos()->get()),
        ]);
    }

    public function destroy(Request $request, KosPhoto $photo): JsonResponse
    {
        $this->authorize('managePhotos', $photo->kos);

        $kos = $photo->kos;
        $wasPrimary = $photo->is_primary;

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        if ($wasPrimary && $kos->photos()->exists()) {
            $kos->photos()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        return response()->json([
            'message' => 'Foto dihapus.',
            'photos' => PhotoResource::collection($kos->photos()->get()),
        ]);
    }

    public function reorder(Request $request, Kos $kos): JsonResponse
    {
        $this->authorize('managePhotos', $kos);

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:kos_photos,id'],
        ]);

        foreach ($validated['ids'] as $index => $id) {
            KosPhoto::query()->where('kos_id', $kos->id)->where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'message' => 'Urutan foto disimpan.',
            'photos' => PhotoResource::collection($kos->photos()->get()),
        ]);
    }
}
