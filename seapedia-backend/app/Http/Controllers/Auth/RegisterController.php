<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            // Kalau cuma 1 role, langsung set sebagai active_role supaya
            // user tidak perlu lewat halaman pilih-role kalau memang cuma
            // punya satu pilihan. Kalau lebih dari 1, biarkan null —
            // frontend akan redirect ke /select-role.
            'active_role' => count($validated['roles']) === 1 ? $validated['roles'][0] : null,
        ]);

        $roleIds = Role::whereIn('name', $validated['roles'])->pluck('id');
        $user->roles()->attach($roleIds);

        // Setiap role butuh resource pendukungnya masing-masing.
        // Buat secara otomatis di sini supaya user langsung siap pakai,
        // tanpa perlu langkah setup tambahan.
        $user->wallet()->create(['balance' => 0]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 201);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'active_role' => $user->active_role,
            'roles' => $user->roles()->get(['roles.id', 'roles.name']),
        ];
    }
}
