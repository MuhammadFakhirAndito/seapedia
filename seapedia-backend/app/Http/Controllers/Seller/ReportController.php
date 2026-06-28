<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * GET /api/seller/reports/income
     *
     * Ringkasan income Seller. Pada Level 4 ini, "income" dihitung dari
     * subtotal (BUKAN total_amount) order yang sudah diproses Seller —
     * karena delivery_fee adalah milik Driver dan PPN adalah pajak yang
     * disetor, bukan pendapatan Seller. Order dengan status
     * 'dikembalikan' dikecualikan (akan ditangani reversal-nya di Level 6).
     *
     * Order yang masih 'sedang_dikemas' (belum diproses) TETAP dihitung
     * di sini sebagai "pending income" terpisah dari yang sudah diproses,
     * supaya Seller bisa melihat potensi pendapatan yang akan masuk.
     */
    public function income(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            return response()->json(['message' => 'Anda belum membuat toko.'], 404);
        }

        $confirmedQuery = $store->orders()->whereIn('status', [
            'menunggu_pengirim', 'sedang_dikirim', 'pesanan_selesai',
        ]);
        $pendingQuery = $store->orders()->where('status', 'sedang_dikemas');

        $totalIncome = $confirmedQuery->sum('subtotal');
        $totalOrders = $confirmedQuery->count();
        $pendingIncome = $pendingQuery->sum('subtotal');
        $pendingOrders = $pendingQuery->count();

        $recentOrders = $store->orders()
            ->with('buyer:id,username')
            ->latest()
            ->limit(5)
            ->get(['id', 'order_number', 'buyer_id', 'subtotal', 'status', 'created_at']);

        return response()->json([
            'total_income' => (float) $totalIncome,
            'total_orders' => $totalOrders,
            'pending_income' => (float) $pendingIncome,
            'pending_orders' => $pendingOrders,
            'recent_orders' => $recentOrders,
        ]);
    }
}
