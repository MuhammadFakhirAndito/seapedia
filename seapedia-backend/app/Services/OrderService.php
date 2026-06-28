<?php

namespace App\Services;

use App\Models\Order;

/**
 * OrderService
 *
 * Helper kecil untuk transisi status order, supaya SETIAP perubahan
 * status (dari mana pun dipanggil: Seller process, Driver take/complete,
 * Admin overdue) selalu konsisten mencatat status_histories dengan
 * timestamp — sesuai requirement "Every order must store status history
 * with timestamps" yang berlaku di semua level.
 */
class OrderService
{
    public function transitionStatus(Order $order, string $newStatus, ?string $note = null): Order
    {
        $order->update(['status' => $newStatus]);

        $order->statusHistories()->create([
            'status' => $newStatus,
            'note' => $note,
        ]);

        return $order->fresh(['statusHistories']);
    }
}
