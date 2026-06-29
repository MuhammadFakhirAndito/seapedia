<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promo;
use App\Models\Store;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Ringkasan seluruh marketplace dalam 1 endpoint.
     */
    public function summary()
    {
        $overdueOrders = $this->getOverdueOrders();

        return response()->json([
            'users'          => $this->userStats(),
            'stores'         => $this->storeStats(),
            'products'       => $this->productStats(),
            'orders'         => $this->orderStats(),
            'vouchers'       => $this->voucherStats(),
            'promos'         => $this->promoStats(),
            'delivery_jobs'  => $this->deliveryStats(),
            'overdue_orders' => [
                'count' => $overdueOrders->count(),
                'items' => $overdueOrders,
            ],
        ]);
    }

    /** Detail list users */
    public function users()
    {
        $users = User::with('roles')
            ->latest()
            ->paginate(20);

        return response()->json($users);
    }

    /** Detail list stores */
    public function stores()
    {
        $stores = Store::withCount('products')
            ->with('user:id,username,email')
            ->latest()
            ->paginate(20);

        return response()->json($stores);
    }

    /** Detail list products */
    public function products()
    {
        $products = Product::with('store:id,name')
            ->latest()
            ->paginate(20);

        return response()->json($products);
    }

    /** Detail list orders */
    public function orders()
    {
        $orders = Order::with([
                'buyer:id,username',
                'store:id,name',
                'items.product:id,name',
            ])
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    /** Detail list delivery jobs */
    public function deliveries()
    {
        $deliveries = Delivery::with([
                'order:id,order_number,status',
                'driver:id,username',
            ])
            ->latest()
            ->paginate(20);

        return response()->json($deliveries);
    }

    /** Detail list overdue orders */
    public function overdueOrders()
    {
        $orders = $this->getOverdueOrders();
        return response()->json([
            'count'  => $orders->count(),
            'orders' => $orders,
        ]);
    }

    // -------------------------------------------------------
    // Private helpers ini abis di benerin
    // -------------------------------------------------------

    private function userStats(): array
    {
    return [
        'total_users' => \App\Models\User::count(),
        'admins'      => \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->count(),
        'sellers'     => \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'seller');
        })->count(),
        'buyers'      => \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'buyer');
        })->count(),
        'drivers'     => \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'driver');
        })->count(),
    ];
    }

    private function storeStats(): array
    {
        return [
            'total' => Store::count(),
        ];
    }

    private function productStats(): array
    {
        return [
            'total'        => Product::count(),
            'out_of_stock' => Product::where('stock', 0)->count(),
        ];
    }

    private function orderStats(): array
    {
        return [
            'total'             => Order::count(),
            'sedang_dikemas'    => Order::where('status', 'sedang_dikemas')->count(),
            'menunggu_pengirim' => Order::where('status', 'menunggu_pengirim')->count(),
            'sedang_dikirim'    => Order::where('status', 'sedang_dikirim')->count(),
            'pesanan_selesai'   => Order::where('status', 'pesanan_selesai')->count(),
            'dikembalikan'      => Order::where('status', 'dikembalikan')->count(),
            'total_revenue'     => Order::where('status', 'pesanan_selesai')->sum('total_amount'),
        ];
    }

    private function voucherStats(): array
    {
        return [
            'total'   => Voucher::count(),
            'active'  => Voucher::where('expires_at', '>=', now())
                            ->whereColumn('usage_count', '<', 'usage_limit')
                            ->count(),
            'expired' => Voucher::where('expires_at', '<', now())->count(),
        ];
    }

    private function promoStats(): array
    {
        return [
            'total'   => Promo::count(),
            'active'  => Promo::where('expires_at', '>=', now())->count(),
            'expired' => Promo::where('expires_at', '<', now())->count(),
        ];
    }

    private function deliveryStats(): array
    {
        return [
            'total'     => Delivery::count(),
            'available' => Delivery::where('status', 'available')->count(),
            'taken'     => Delivery::where('status', 'taken')->count(),
            'completed' => Delivery::where('status', 'completed')->count(),
        ];
    }

    private function getOverdueOrders()
    {
        $now = now();

        // SLA per delivery method (dalam jam):
        // instant   = 1 hari  = 24 jam
        // next_day  = 2 hari  = 48 jam
        // regular   = 3 hari  = 72 jam
        return Order::with(['buyer:id,username', 'store:id,name', 'delivery'])
            ->whereIn('status', ['sedang_dikemas', 'menunggu_pengirim', 'sedang_dikirim'])
            ->where(function ($q) use ($now) {
                $q->where(function ($q2) use ($now) {
                    $q2->where('delivery_method', 'instant')
                       ->where('created_at', '<', $now->copy()->subHours(24));
                })->orWhere(function ($q2) use ($now) {
                    $q2->where('delivery_method', 'next_day')
                       ->where('created_at', '<', $now->copy()->subHours(48));
                })->orWhere(function ($q2) use ($now) {
                    $q2->where('delivery_method', 'regular')
                       ->where('created_at', '<', $now->copy()->subHours(72));
                });
            })
            ->get();
    }
}
