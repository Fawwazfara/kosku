<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->query('role')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = '%'.strtolower((string) $request->query('q')).'%';
                $q->whereRaw('lower(name) like ? or lower(email) like ?', [$like, $like]);
            })
            ->withCount('kos')
            ->latest()
            ->paginate(20);

        return response()->json($users);
    }

    public function toggleActive(Request $request, User $user): JsonResponse
    {
        if ($user->isAdmin() && $user->id !== $request->user()->id) {
            return response()->json(['message' => 'Admin lain tidak dapat dinonaktifkan.'], 422);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return response()->json([
            'message' => $user->is_active ? "User {$user->name} diaktifkan." : "User {$user->name} dinonaktifkan.",
            'is_active' => $user->is_active,
        ]);
    }
}
