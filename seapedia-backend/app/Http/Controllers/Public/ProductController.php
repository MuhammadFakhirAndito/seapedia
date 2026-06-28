<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/public/products
     *
     * Endpoint katalog publik — bisa diakses guest tanpa login.
     * Hanya menampilkan produk yang is_active = true, dan menyertakan
     * info toko (sesuai requirement "Display store information in the
     * product listing").
     *
     * Mendukung query string ?q=... untuk pencarian sederhana (dipakai
     * oleh search bar di frontend).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with('store:id,name')
            ->where('is_active', true);

        if ($search = $request->query('q')) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $products = $query->latest()->paginate(20);

        return response()->json($products);
    }

    /**
     * GET /api/public/products/{product}
     */
    public function show(Product $product): JsonResponse
    {
        if (! $product->is_active) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
        }

        $product->load('store:id,name,description');

        return response()->json($product);
    }
}
