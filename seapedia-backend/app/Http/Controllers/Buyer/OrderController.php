<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * GET /api/buyer/orders
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->ordersAsBuyer()
            ->with('store:id,name', 'items')
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    /**
     * GET /api/buyer/orders/{order}
     *
     * Memuat status history lengkap untuk ditampilkan sebagai timeline.
     */
    public function show(Request $request, int $order): JsonResponse
    {
        $order = $request->user()
            ->ordersAsBuyer()
            ->with('store:id,name', 'items', 'statusHistories', 'address', 'delivery.driver:id,username')
            ->find($order);

        if (! $order) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        return response()->json($order);
    }
}
