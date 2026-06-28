<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * GET /api/buyer/wallet
     * Menampilkan saldo + 20 transaksi terakhir.
     */
    public function show(Request $request): JsonResponse
    {
        $wallet = $request->user()->wallet;

        return response()->json([
            'balance' => $wallet->balance,
            'transactions' => $wallet->transactions()->latest()->limit(20)->get(),
        ]);
    }

    /**
     * POST /api/buyer/wallet/topup
     *
     * Dummy top-up — tidak terhubung ke payment gateway sungguhan
     * (sesuai requirement dokumen: "top-up flow may be a dummy simulation").
     * Yang penting balance dan history-nya benar-benar tersimpan dan
     * konsisten, karena ini akan dipakai nyata oleh checkout nanti.
     */
    public function topup(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:10000', 'max:10000000'],
        ]);

        $wallet = $request->user()->wallet;
        $amount = $request->amount;

        $wallet->increment('balance', $amount);
        $transaction = $wallet->transactions()->create([
            'type' => 'topup',
            'amount' => $amount,
            'description' => 'Top-up dummy oleh buyer',
        ]);

        return response()->json([
            'message' => 'Top-up berhasil.',
            'balance' => $wallet->balance,
            'transaction' => $transaction,
        ], 201);
    }
}
