<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * GET /api/seller/orders
     *
     * Daftar order yang masuk ke toko milik Seller yang sedang login.
     * Level 3 baru sebatas LIHAT — aksi "process order" (pindah status
     * ke Menunggu Pengirim) baru masuk di Level 4.
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            return response()->json(['message' => 'Anda belum membuat toko.'], 404);
        }

        $orders = $store->orders()
            ->with('buyer:id,username', 'items')
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    /**
     * GET /api/seller/orders/{order}
     */
    public function show(Request $request, int $order): JsonResponse
    {
        $store = $request->user()->store;

        $order = $store?->orders()
            ->with('buyer:id,username', 'items', 'statusHistories', 'address')
            ->find($order);

        if (! $order) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        return response()->json($order);
    }
}
