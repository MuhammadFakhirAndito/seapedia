<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    private const PPN_RATE = 0.12;

    private const DELIVERY_FEES = [
        'instant' => 25000,
        'next_day' => 15000,
        'regular' => 9000,
    ];

    // SLA dalam jam, dipakai OverdueService nanti di Level 6
    private const SLA_HOURS = [
        'instant' => 2,
        'next_day' => 24,
        'regular' => 72,
    ];

    public function __construct(
        private DiscountService $discountService
    ) {}

    /**
     * @param  \App\Models\User  $buyer
     * @param  array{address_id:int, delivery_method:string, discount_code:?string}  $payload
     * @return Order
     *
     * @throws ValidationException
     */
    public function checkout($buyer, array $payload): Order
    {
        return DB::transaction(function () use ($buyer, $payload) {
            $cart = Cart::where('user_id', $buyer->id)
                ->with('items.product')
                ->lockForUpdate() // cegah race condition kalau ada concurrent checkout
                ->first();

            if (! $cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Cart kosong, tidak bisa checkout.',
                ]);
            }

            // 1. Subtotal + validasi stock SEBELUM membuat apapun
            $subtotal = 0;
            foreach ($cart->items as $item) {
                $product = $item->product;

                // Lock baris produk juga, supaya stock tidak berubah di tengah transaksi
                $lockedProduct = $product->lockForUpdate()->find($product->id);

                if ($lockedProduct->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Stock produk '{$lockedProduct->name}' tidak cukup.",
                    ]);
                }

                $subtotal += $lockedProduct->price * $item->quantity;
            }

            // 2. Diskon (Voucher/Promo) — delegasikan ke DiscountService
            $discountResult = $this->discountService->apply(
                $payload['discount_code'] ?? null,
                $subtotal
            );
            $discountAmount = $discountResult['discount_amount'];

            // 3. Delivery fee
            $deliveryMethod = $payload['delivery_method'];
            $deliveryFee = self::DELIVERY_FEES[$deliveryMethod];

            // 4 & 5. PPN dihitung dari (subtotal - discount + delivery_fee)
            $dasarKenaPpn = ($subtotal - $discountAmount) + $deliveryFee;
            $ppnAmount = round($dasarKenaPpn * self::PPN_RATE, 2);

            // 6. Total akhir
            $totalAmount = $dasarKenaPpn + $ppnAmount;

            // Validasi wallet SEBELUM membuat order
            $wallet = $buyer->wallet;
            if ($wallet->balance < $totalAmount) {
                throw ValidationException::withMessages([
                    'wallet' => 'Saldo wallet tidak cukup untuk menyelesaikan checkout.',
                ]);
            }

            // Buat order
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'buyer_id' => $buyer->id,
                'store_id' => $cart->store_id,
                'address_id' => $payload['address_id'],
                'delivery_method' => $deliveryMethod,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'voucher_id' => $discountResult['voucher_id'] ?? null,
                'promo_id' => $discountResult['promo_id'] ?? null,
                'delivery_fee' => $deliveryFee,
                'ppn_amount' => $ppnAmount,
                'total_amount' => $totalAmount,
                'status' => 'sedang_dikemas',
                'sla_due_at' => now()->addHours(self::SLA_HOURS[$deliveryMethod]),
            ]);

            // Snapshot setiap item + kurangi stock
            foreach ($cart->items as $item) {
                $product = $item->product;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'price_snapshot' => $product->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $product->price * $item->quantity,
                ]);

                // Kurangi stock, jangan sampai negatif (sudah divalidasi di atas,
                // tapi decrement tetap dijaga di level query)
                $product->decrement('stock', $item->quantity);
                $product->increment('sold_count', $item->quantity);
            }

            // Catat status history awal
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'sedang_dikemas',
                'note' => 'Order dibuat dari checkout.',
            ]);

            // Potong wallet buyer
            $wallet->decrement('balance', $totalAmount);
            $wallet->transactions()->create([
                'type' => 'checkout_charge',
                'amount' => $totalAmount,
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'description' => "Checkout order {$order->order_number}",
            ]);

            // Tandai usage voucher (kalau dipakai)
            $this->discountService->markUsed($discountResult);

            // Kosongkan cart setelah checkout sukses
            $cart->items()->delete();
            $cart->update(['store_id' => null]);

            return $order->load('items', 'statusHistories');
        });
    }

    private function generateOrderNumber(): string
    {
        return 'SPD-'.now()->format('Ymd').'-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
