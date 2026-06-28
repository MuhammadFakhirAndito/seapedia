<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    /**
     * Lihat semua job yang tersedia (status: available).
     * Hanya muncul setelah Seller proses order (menunggu_pengirim).
     */
    public function index()
    {
        $jobs = Delivery::with([
                'order.store',
                'order.items.product',
                'order.buyer:id,username',
            ])
            ->where('status', 'available')
            ->latest()
            ->get();

        return response()->json($jobs);
    }

    /**
     * Lihat detail 1 job spesifik.
     */
    public function show(Delivery $delivery)
    {
        $delivery->load([
            'order.store',
            'order.items.product',
            'order.buyer:id,username',
            'order.statusHistories',
        ]);

        return response()->json($delivery);
    }

    /**
     * Driver mengambil job.
     * Pakai pessimistic lock supaya 2 driver tidak bisa ambil job yang sama.
     */
    public function take(Request $request, Delivery $delivery)
    {
        $driver = $request->user();

        try {
            DB::transaction(function () use ($delivery, $driver) {
                $locked = Delivery::lockForUpdate()->findOrFail($delivery->id);

                if ($locked->status !== 'available') {
                    throw new \Exception('Job ini sudah diambil oleh driver lain.');
                }

                $locked->update([
                    'driver_id' => $driver->id,
                    'status'    => 'taken',
                    'taken_at'  => now(),
                ]);

                $order = $locked->order;
                $order->update(['status' => 'sedang_dikirim']);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status'   => 'sedang_dikirim',
                    'note'     => 'Order diambil oleh Driver: ' . $driver->username,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'message'  => 'Job berhasil diambil. Segera antar pesanan!',
            'delivery' => $delivery->fresh(['order.store', 'order.items']),
        ]);
    }

    /**
     * Driver konfirmasi pesanan selesai diantarkan.
     */
    public function complete(Request $request, Delivery $delivery)
    {
        $driver = $request->user();

        if ($delivery->driver_id !== $driver->id) {
            return response()->json([
                'message' => 'Anda bukan driver untuk job ini.',
            ], 403);
        }

        if ($delivery->status !== 'taken') {
            return response()->json([
                'message' => 'Job ini tidak dalam status sedang dikirim.',
            ], 409);
        }

        DB::transaction(function () use ($delivery, $driver) {
            $delivery->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

            $order = $delivery->order;
            $order->update(['status' => 'pesanan_selesai']);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status'   => 'pesanan_selesai',
                'note'     => 'Pesanan dikonfirmasi selesai oleh Driver: ' . $driver->username,
            ]);
        });

        return response()->json([
            'message'        => 'Pesanan berhasil diantarkan! Earning kamu sudah tercatat.',
            'delivery'       => $delivery->fresh(['order']),
            'earning_amount' => $delivery->earning_amount,
        ]);
    }

    /**
     * Dashboard driver: job aktif, history, total earning.
     */
    public function history(Request $request)
    {
        $driver = $request->user();

        $allJobs = Delivery::with(['order.store', 'order.items.product'])
            ->where('driver_id', $driver->id)
            ->latest()
            ->get();

        $activeJob     = $allJobs->firstWhere('status', 'taken');
        $completedJobs = $allJobs->where('status', 'completed')->values();
        $totalEarning  = $completedJobs->sum('earning_amount');

        return response()->json([
            'driver'         => $driver->only('id', 'username'),
            'total_earning'  => (float) $totalEarning,
            'active_job'     => $activeJob,
            'completed_jobs' => $completedJobs,
            'total_jobs'     => $completedJobs->count(),
        ]);
    }
}
