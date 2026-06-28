<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkoutService) {}

    /**
     * POST /api/buyer/checkout
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'delivery_method' => ['required', 'string', 'in:instant,next_day,regular'],
            'discount_code' => ['nullable', 'string'],
        ]);

        // Pastikan address_id memang milik buyer yang sedang login,
        // bukan alamat milik buyer lain yang ID-nya ditebak.
        $address = $request->user()->addresses()->find($validated['address_id']);
        if (! $address) {
            throw ValidationException::withMessages([
                'address_id' => 'Alamat tidak ditemukan atau bukan milik Anda.',
            ]);
        }

        $order = $this->checkoutService->checkout($request->user(), $validated);

        return response()->json([
            'message' => 'Checkout berhasil.',
            'order' => $order,
        ], 201);
    }
}
