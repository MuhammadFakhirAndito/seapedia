<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->addresses()->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateAddress($request);
        $user = $request->user();

        // Kalau ini alamat pertama, otomatis jadi default. Kalau user
        // secara eksplisit minta jadi default, alamat lain di-unset dulu.
        $isFirstAddress = $user->addresses()->count() === 0;
        $makeDefault = $isFirstAddress || ($validated['is_default'] ?? false);

        if ($makeDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            ...$validated,
            'is_default' => $makeDefault,
        ]);

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan.',
            'address' => $address,
        ], 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwnership($request, $address);
        $validated = $this->validateAddress($request);

        if ($validated['is_default'] ?? false) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'message' => 'Alamat berhasil diperbarui.',
            'address' => $address,
        ]);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwnership($request, $address);
        $address->delete();

        return response()->json(['message' => 'Alamat berhasil dihapus.']);
    }

    /**
     * Cek manual ownership (tanpa Policy terpisah, karena cuma dipakai
     * di controller ini). Kalau bukan alamat milik user yang login,
     * lempar 403 — mencegah Buyer A menghapus/edit alamat Buyer B hanya
     * dengan menebak ID.
     */
    private function authorizeOwnership(Request $request, Address $address): void
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke alamat ini.');
        }
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['sometimes', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'full_address' => ['required', 'string', 'max:1000'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
    }
}
