<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KosController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', Kos::STATUS_PENDING);

        $kos = Kos::query()
            ->when(in_array($status, Kos::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->with(['owner:id,name,email,phone,is_verified', 'photos'])
            ->withAvg(['reviews as rating_avg' => fn ($q) => $q->where('is_hidden', false)], 'rating')
            ->withCount(['reviews as reviews_count' => fn ($q) => $q->where('is_hidden', false)])
            ->latest()
            ->paginate(min((int) $request->query('per_page', 12), 24))
            ->withQueryString();

        return \App\Http\Resources\KosResource::collection($kos);
    }

    public function approve(Request $request, Kos $kos): JsonResponse
    {
        $kos->update(['status' => Kos::STATUS_ACTIVE]);

        return response()->json(['message' => "Kos \"{$kos->name}\" disetujui dan tampil ke publik."]);
    }

    public function reject(Request $request, Kos $kos): JsonResponse
    {
        $kos->update(['status' => Kos::STATUS_REJECTED]);

        return response()->json(['message' => "Kos \"{$kos->name}\" ditolak."]);
    }

    public function hide(Request $request, Kos $kos): JsonResponse
    {
        $kos->update(['status' => Kos::STATUS_HIDDEN]);

        return response()->json(['message' => "Kos \"{$kos->name}\" disembunyikan dari publik."]);
    }
}
