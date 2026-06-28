<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * GET /api/admin/vouchers
     */
    public function index(): JsonResponse
    {
        return response()->json(Voucher::latest()->get());
    }

    /**
     * GET /api/admin/vouchers/{voucher}
     */
    public function show(Voucher $voucher): JsonResponse
    {
        return response()->json($voucher);
    }

    /**
     * POST /api/admin/vouchers
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:vouchers,code'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['required', 'integer', 'min:1'],
            'expires_at' => ['required', 'date', 'after:now'],
        ]);

        $voucher = Voucher::create($validated);

        return response()->json([
            'message' => 'Voucher berhasil dibuat.',
            'voucher' => $voucher,
        ], 201);
    }
}
