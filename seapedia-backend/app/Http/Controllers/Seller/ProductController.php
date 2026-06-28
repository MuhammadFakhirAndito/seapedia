<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\ProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * GET /api/seller/products
     * Daftar produk milik toko Seller yang sedang login saja.
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            return response()->json([
                'message' => 'Anda belum membuat toko. Buat toko terlebih dahulu sebelum menambah produk.',
            ], 422);
        }

        $products = $store->products()->latest()->get();

        return response()->json($products);
    }

    /**
     * POST /api/seller/products
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            throw ValidationException::withMessages([
                'store' => 'Anda harus membuat toko terlebih dahulu sebelum menambah produk.',
            ]);
        }

        $product = $store->products()->create($request->validated());

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'product' => $product,
        ], 201);
    }

    /**
     * PUT /api/seller/products/{product}
     *
     * Otorisasi ownership dicek lewat Gate::authorize, yang akan melempar
     * 403 otomatis kalau ProductPolicy::update() mengembalikan false.
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $product->update($request->validated());

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'product' => $product,
        ]);
    }

    /**
     * DELETE /api/seller/products/{product}
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }
}
