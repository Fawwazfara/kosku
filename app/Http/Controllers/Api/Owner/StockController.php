<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStockRequest;
use App\Http\Resources\KosResource;
use App\Models\Kos;
use Illuminate\Http\JsonResponse;

class StockController extends Controller
{
    public function update(UpdateStockRequest $request, Kos $kos): JsonResponse
    {
        $this->authorize('updateStock', $kos);

        $kos->update(['available_rooms' => $request->integer('available_rooms')]);

        return response()->json([
            'message' => $kos->available_rooms > 0
                ? "Sisa kamar diperbarui: {$kos->available_rooms} kamar."
                : 'Kos ditandai PENUH.',
            'kos' => new KosResource($kos),
        ]);
    }
}
