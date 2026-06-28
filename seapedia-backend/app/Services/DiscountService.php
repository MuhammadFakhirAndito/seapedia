<?php

namespace App\Services;

use App\Models\Promo;
use App\Models\Voucher;
use Illuminate\Validation\ValidationException;

class DiscountService
{
    /**
     * @return array{
     *   discount_amount: float,
     *   voucher_id: ?int,
     *   promo_id: ?int,
     *   type: ?string,
     *   model: \App\Models\Voucher|\App\Models\Promo|null
     * }
     */
    public function apply(?string $discountCode, float $subtotal): array
    {
        // Kasus 1: Buyer secara eksplisit kirim kode voucher
        if ($discountCode) {
            $voucher = Voucher::where('code', $discountCode)->first();

            if (! $voucher) {
                throw ValidationException::withMessages([
                    'discount_code' => 'Kode voucher tidak ditemukan.',
                ]);
            }

            $this->assertVoucherValid($voucher);

            return [
                'discount_amount' => $this->calculate($voucher->discount_type, $voucher->discount_value, $voucher->max_discount, $subtotal),
                'voucher_id' => $voucher->id,
                'promo_id' => null,
                'type' => 'voucher',
                'model' => $voucher,
            ];
        }

        // Kasus 2: Tidak ada kode → cek Promo otomatis yang aktif
        $promo = Promo::where('is_active', true)
            ->where('expires_at', '>', now())
            ->orderByDesc('discount_value') // ambil yang paling menguntungkan kalau ada banyak
            ->first();

        if ($promo) {
            return [
                'discount_amount' => $this->calculate($promo->discount_type, $promo->discount_value, $promo->max_discount, $subtotal),
                'voucher_id' => null,
                'promo_id' => $promo->id,
                'type' => 'promo',
                'model' => $promo,
            ];
        }

        // Kasus 3: Tidak ada diskon sama sekali
        return [
            'discount_amount' => 0,
            'voucher_id' => null,
            'promo_id' => null,
            'type' => null,
            'model' => null,
        ];
    }

    private function assertVoucherValid(Voucher $voucher): void
    {
        if (! $voucher->is_active) {
            throw ValidationException::withMessages(['discount_code' => 'Voucher tidak aktif.']);
        }

        if ($voucher->expires_at->isPast()) {
            throw ValidationException::withMessages(['discount_code' => 'Voucher sudah expired.']);
        }

        if ($voucher->usage_count >= $voucher->usage_limit) {
            throw ValidationException::withMessages(['discount_code' => 'Kuota pemakaian voucher sudah habis.']);
        }
    }

    private function calculate(string $type, float $value, ?float $maxDiscount, float $subtotal): float
    {
        $amount = $type === 'percentage'
            ? $subtotal * ($value / 100)
            : $value;

        if ($maxDiscount !== null) {
            $amount = min($amount, $maxDiscount);
        }

        // Diskon tidak boleh melebihi subtotal
        return min($amount, $subtotal);
    }

    /**
     * Dipanggil setelah checkout SUKSES (dalam transaction yang sama)
     * untuk menambah usage_count voucher. Promo tidak perlu ini.
     */
    public function markUsed(array $discountResult): void
    {
        if ($discountResult['type'] === 'voucher' && $discountResult['model']) {
            $discountResult['model']->increment('usage_count');
        }
    }
}
