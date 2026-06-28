<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\StoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * GET /api/seller/store
     * Mengambil profil toko milik Seller yang sedang login (kalau sudah ada).
     */
    public function show(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            return response()->json(['message' => 'Anda belum membuat toko.'], 404);
        }

        return response()->json($store);
    }

    /**
     * POST /api/seller/store
     *
     * Membuat ATAU memperbarui toko milik Seller. Sengaja digabung jadi
     * satu endpoint (upsert) karena satu Seller hanya boleh punya satu
     * toko — tidak ada skenario "buat toko kedua".
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $store = $user->store()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return response()->json([
            'message' => $store->wasRecentlyCreated
                ? 'Toko berhasil dibuat.'
                : 'Toko berhasil disimpan.',
            'store' => $store,
        ], $store->wasRecentlyCreated ? 201 : 200);
    }
}
