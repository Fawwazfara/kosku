<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChooseRoleRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $isOwner = $data['role'] === User::ROLE_OWNER;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'role' => $data['role'],
            'is_verified' => ! $isOwner,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => (new UserResource($user))->toArray($request),
            'message' => $isOwner
                ? 'Pendaftaran berhasil. Akun pemilik menunggu verifikasi admin.'
                : 'Pendaftaran berhasil.',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->json([
            'user' => (new UserResource(Auth::user()))->toArray($request),
            'message' => 'Berhasil masuk.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Berhasil keluar.']);
    }

    public function user(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function chooseRole(ChooseRoleRequest $request): JsonResponse
    {
        $user = $request->user();
        $isOwner = $request->validated('role') === User::ROLE_OWNER;

        $user->forceFill([
            'role' => $request->validated('role'),
            'is_verified' => ! $isOwner,
        ])->save();

        return response()->json([
            'user' => (new UserResource($user->fresh()))->toArray($request),
            'message' => $isOwner
                ? 'Peran disimpan. Akun pemilik menunggu verifikasi admin.'
                : 'Peran disimpan.',
        ]);
    }
}
