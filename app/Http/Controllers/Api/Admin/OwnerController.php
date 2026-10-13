<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->where('role', User::ROLE_OWNER)
            ->when($request->query('status') === 'pending', fn ($q) => $q->where('is_verified', false))
            ->when($request->query('status') === 'verified', fn ($q) => $q->where('is_verified', true))
            ->with(['kos' => fn ($q) => $q->select('id', 'owner_id', 'name', 'status', 'type')])
            ->withCount(['kos', 'kos as active_kos_count' => fn ($q) => $q->where('status', 'active')])
            ->latest()
            ->get();

        return response()->json([
            'owners' => $users->map(fn (User $u) => [
                ...(new UserResource($u))->toArray($request),
                'kos_count' => $u->kos_count,
                'active_kos_count' => $u->active_kos_count,
                'kos' => $u->kos->map(fn ($k) => [
                    'id' => $k->id,
                    'name' => $k->name,
                    'type' => $k->type,
                    'status' => $k->status,
                ]),
            ]),
        ]);
    }

    public function verify(Request $request, User $user): JsonResponse
    {
        if (! $user->isOwner()) {
            return response()->json(['message' => 'User ini bukan pemilik kos.'], 422);
        }

        $user->update([
            'is_verified' => true,
            'is_active' => true,
        ]);

        $active = $user->kos()->where('status', '!=', 'active')->where('status', '!=', 'rejected')->get();
        foreach ($active as $kos) {
            $kos->update(['status' => 'active']);
        }

        return response()->json([
            'message' => "Pemilik {$user->name} terverifikasi.",
            'user' => new UserResource($user),
        ]);
    }

    public function reject(Request $request, User $user): JsonResponse
    {
        if (! $user->isOwner()) {
            return response()->json(['message' => 'User ini bukan pemilik kos.'], 422);
        }

        $user->update([
            'is_verified' => false,
            'is_active' => false,
        ]);

        $user->kos()->where('status', 'pending')->update(['status' => 'rejected']);

        return response()->json(['message' => "Pemilik {$user->name} ditolak dan dinonaktifkan."]);
    }
}
