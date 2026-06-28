<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * GET /api/buyer/reports/spending
     */
    public function spending(Request $request): JsonResponse
    {
        $buyer = $request->user();

        $baseQuery = $buyer->ordersAsBuyer()->where('status', '!=', 'dikembalikan');

        $totalSpent = $baseQuery->sum('total_amount');
        $totalOrders = $baseQuery->count();
        $totalDiscountUsed = $baseQuery->sum('discount_amount');

        $recentOrders = $buyer->ordersAsBuyer()
            ->with('store:id,name')
            ->latest()
            ->limit(5)
            ->get(['id', 'order_number', 'store_id', 'total_amount', 'status', 'created_at']);

        return response()->json([
            'total_spent' => (float) $totalSpent,
            'total_orders' => $totalOrders,
            'total_discount_used' => (float) $totalDiscountUsed,
            'recent_orders' => $recentOrders,
        ]);
    }
}
