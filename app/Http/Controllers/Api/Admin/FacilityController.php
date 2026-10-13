<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FacilityRequest;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        return FacilityResource::collection(
            Facility::query()
                ->withCount('kos')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(FacilityRequest $request): JsonResponse
    {
        $facility = Facility::create($request->validated());

        return response()->json([
            'message' => 'Fasilitas ditambahkan.',
            'facility' => new FacilityResource($facility),
        ], 201);
    }

    public function update(FacilityRequest $request, Facility $facility): JsonResponse
    {
        $facility->update($request->validated());

        return response()->json([
            'message' => 'Fasilitas diperbarui.',
            'facility' => new FacilityResource($facility),
        ]);
    }

    public function destroy(Request $request, Facility $facility): JsonResponse
    {
        $facility->delete();

        return response()->json(['message' => 'Fasilitas dihapus.']);
    }
}
