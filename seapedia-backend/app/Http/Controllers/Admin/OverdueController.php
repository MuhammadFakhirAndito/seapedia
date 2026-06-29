<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class OverdueController extends Controller
{
    /**
     * Trigger overdue handling via API.
     * Admin bisa kirim simulate_days untuk demo.
     *
     * POST /api/admin/overdue/handle
     * Body: { "simulate_days": 3 }
     */
    public function handle(Request $request)
    {
        $request->validate([
            'simulate_days' => 'nullable|integer|min:0|max:30',
        ]);

        $simulateDays = $request->input('simulate_days', 0);

        // Jalankan artisan command secara programatik
        Artisan::call('seapedia:handle-overdue', [
            '--simulate-days' => $simulateDays,
        ]);

        $output = Artisan::output();

        return response()->json([
            'message'       => 'Overdue handling selesai dijalankan.',
            'simulate_days' => $simulateDays,
            'output'        => $output,
        ]);
    }

    /**
     * Simulasi maju N hari — shortcut untuk demo.
     *
     * POST /api/admin/simulate-next-day
     * Body: { "days": 1 }
     */
    public function simulateNextDay(Request $request)
    {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:30',
        ]);

        $days = $request->input('days', 1);

        Artisan::call('seapedia:handle-overdue', [
            '--simulate-days' => $days,
        ]);

        $output = Artisan::output();

        return response()->json([
            'message' => "Simulasi maju {$days} hari selesai. Overdue handling sudah dijalankan.",
            'days'    => $days,
            'output'  => $output,
        ]);
    }
}
