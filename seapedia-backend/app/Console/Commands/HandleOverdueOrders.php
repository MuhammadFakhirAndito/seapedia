<?php

namespace App\Console\Commands;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\WalletTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HandleOverdueOrders extends Command
{
    /**
     * php artisan seapedia:handle-overdue
     * php artisan seapedia:handle-overdue --simulate-days=2
     *
     * --simulate-days=N : anggap waktu maju N hari ke depan (untuk demo)
     */
    protected $signature = 'seapedia:handle-overdue
                            {--simulate-days=0 : Simulasi maju N hari (0 = waktu sekarang)}';

    protected $description = 'Auto refund / auto return untuk order yang melewati SLA pengiriman';

    // SLA dalam jam per delivery method
    private const SLA_HOURS = [
        'instant'  => 24,
        'next_day' => 48,
        'regular'  => 72,
    ];

    public function handle(): int
    {
        $simulateDays = (int) $this->option('simulate-days');
        $now = now()->addDays($simulateDays);

        if ($simulateDays > 0) {
            $this->info("⏩ Mode simulasi: waktu dianggap maju {$simulateDays} hari ({$now->toDateTimeString()})");
        }

        $this->info('Mencari order yang melewati SLA...');

        $overdueOrders = Order::with(['items.product', 'buyer.wallet', 'delivery'])
            ->whereIn('status', ['sedang_dikemas', 'menunggu_pengirim', 'sedang_dikirim'])
            ->whereNull('refunded_at') // cegah double refund
            ->where(function ($q) use ($now) {
                foreach (self::SLA_HOURS as $method => $hours) {
                    $q->orWhere(function ($q2) use ($method, $hours, $now) {
                        $q2->where('delivery_method', $method)
                           ->where('created_at', '<', $now->copy()->subHours($hours));
                    });
                }
            })
            ->get();

        if ($overdueOrders->isEmpty()) {
            $this->info('Tidak ada order overdue saat ini.');
            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$overdueOrders->count()} order overdue. Memproses...");
        $processed = 0;

        foreach ($overdueOrders as $order) {
            try {
                DB::transaction(function () use ($order, $now) {
                    // Tandai sudah diproses supaya tidak double
                    $order->update(['refunded_at' => $now]);

                    // Kembalikan stok produk
                    foreach ($order->items as $item) {
                        $item->product->increment('stock', $item->quantity);
                    }

                    // Refund ke wallet Buyer
                    $wallet = $order->buyer->wallet;
                    if ($wallet) {
                        $wallet->increment('balance', $order->total_amount);

                        WalletTransaction::create([
                            'wallet_id'   => $wallet->id,
                            'type'        => 'refund',
                            'amount'      => $order->total_amount,
                            'description' => 'Auto-refund order ' . $order->order_number . ' (overdue SLA)',
                        ]);
                    }

                    // Batalkan delivery job kalau ada
                    if ($order->delivery && $order->delivery->status !== 'completed') {
                        $order->delivery->update(['status' => 'completed']); // tutup job
                    }

                    // Update status order ke dikembalikan
                    $order->update(['status' => 'dikembalikan']);

                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status'   => 'dikembalikan',
                        'note'     => 'Order dikembalikan otomatis karena melewati SLA '
                                    . strtoupper($order->delivery_method)
                                    . '. Refund Rp' . number_format($order->total_amount, 0, ',', '.')
                                    . ' dikembalikan ke wallet.',
                    ]);
                });

                $this->line("  ✓ Order #{$order->order_number} → dikembalikan, refund Rp" . number_format($order->total_amount, 0, ',', '.'));
                $processed++;

            } catch (\Exception $e) {
                $this->error("  ✗ Order #{$order->order_number} gagal: " . $e->getMessage());
            }
        }

        $this->info("Selesai. {$processed} order berhasil diproses.");
        return Command::SUCCESS;
    }
}
