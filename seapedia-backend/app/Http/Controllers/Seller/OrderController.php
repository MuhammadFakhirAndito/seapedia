<?php

namespace App\Http\Controllers\Seller;

use App\Models\Delivery;
use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    /**
     * GET /api/seller/orders
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

    /*
     * POST /api/seller/orders/{order}/process
     */
    public function process(Request $request, int $order): JsonResponse
    {
        $store = $request->user()->store;
        $orderModel = $store?->orders()->find($order);

        if (! $orderModel) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        if ($orderModel->status !== 'sedang_dikemas') {
            throw ValidationException::withMessages([
                'status' => "Order tidak bisa diproses karena status saat ini adalah '{$orderModel->status}'.",
            ]);
        }

        $updated = $this->orderService->transitionStatus(
            $orderModel,
            'menunggu_pengirim',
            'Order diproses oleh Seller, siap diambil Driver.'
        );

        if (! $updated->delivery) {
            Delivery::create([
                'order_id'       => $updated->id,
                'status'         => 'available',
                'earning_amount' => round($updated->delivery_fee * 0.8, 2),
            ]);
        }

        return response()->json([
            'message' => 'Order berhasil diproses dan siap untuk pengiriman.',
            'order'   => $updated->load('delivery'),
        ]);
    }
}