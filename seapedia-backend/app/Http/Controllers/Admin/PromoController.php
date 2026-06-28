<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    /**
     * GET /api/admin/promos
     */
    public function index(): JsonResponse
    {
        return response()->json(Promo::latest()->get());
    }

    /**
     * GET /api/admin/promos/{promo}
     */
    public function show(Promo $promo): JsonResponse
    {
        return response()->json($promo);
    }

    /**
     * POST /api/admin/promos
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'expires_at' => ['required', 'date', 'after:now'],
        ]);

        $promo = Promo::create($validated);

        return response()->json([
            'message' => 'Promo berhasil dibuat.',
            'promo' => $promo,
        ], 201);
    }
}
