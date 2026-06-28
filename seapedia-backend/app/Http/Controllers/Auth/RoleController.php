<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    /**
     * POST /api/select-role
     *
     * Mengubah active_role user yang sedang login. Hanya bisa pilih role
     * yang memang dia miliki (dicek dari relasi roles(), bukan asal terima
     * input apa saja) — supaya orang tidak bisa curang set active_role
     * jadi 'seller' padahal tidak pernah register sebagai seller.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'role' => ['required', 'string', 'in:buyer,seller,driver'],
        ]);

        $user = $request->user();
        $ownsRole = $user->roles()->where('name', $request->role)->exists();

        if (! $ownsRole) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak memiliki role tersebut.',
            ]);
        }

        $user->update(['active_role' => $request->role]);

        return response()->json([
            'message' => 'Role aktif berhasil diubah.',
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * GET /api/me
     *
     * Mengembalikan profil user yang sedang login + daftar role yang
     * dimiliki + active_role saat ini. Dipanggil frontend setiap kali
     * butuh tahu status auth terkini (misal setelah refresh halaman).
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json($this->formatUser($request->user()));
    }

    private function formatUser($user): array
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
