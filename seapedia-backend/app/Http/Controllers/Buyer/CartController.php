<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    /**
     * GET /api/buyer/cart-items
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->cartService->getCartSummary($request->user()));
    }

    /**
     * POST /api/buyer/cart-items
     *
     * Response 409 (bukan 422) kalau melanggar aturan single-store —
     * frontend membedakan ini secara khusus untuk menampilkan modal
     * "kosongkan keranjang dulu?" (lihat ProductDetail.vue).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $summary = $this->cartService->addItem(
            $request->user(),
            $request->product_id,
            $request->quantity
        );

        return response()->json([
            'message' => 'Produk berhasil ditambahkan ke keranjang.',
            ...$summary,
        ], 201);
    }

    /**
     * PUT /api/buyer/cart-items/{cartItem}
     */
    public function update(Request $request, int $cartItem): JsonResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $summary = $this->cartService->updateQuantity($request->user(), $cartItem, $request->quantity);

        return response()->json($summary);
    }

    /**
     * DELETE /api/buyer/cart-items/{cartItem}
     */
    public function destroy(Request $request, int $cartItem): JsonResponse
    {
        $summary = $this->cartService->removeItem($request->user(), $cartItem);

        return response()->json($summary);
    }

    /**
     * DELETE /api/buyer/cart-items
     * Mengosongkan seluruh cart (dipanggil dari modal "kosongkan keranjang").
     */
    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clearCart($request->user());

        return response()->json(['message' => 'Keranjang berhasil dikosongkan.']);
    }
}
