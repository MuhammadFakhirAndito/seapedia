<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * ProductPolicy
 *
 * Memastikan Seller hanya bisa update/delete produk yang benar-benar
 * milik toko dia sendiri. Ini lapisan pertahanan TERPISAH dari middleware
 * CheckActiveRole — middleware cuma cek "apakah dia Seller", Policy ini
 * cek "apakah produk INI miliknya".
 *
 * Daftarkan di app/Providers/AppServiceProvider.php (Laravel 11+, karena
 * tidak ada lagi AuthServiceProvider bawaan):
 *
 *   use App\Models\Product;
 *   use App\Policies\ProductPolicy;
 *   use Illuminate\Support\Facades\Gate;
 *
 *   public function boot(): void
 *   {
 *       Gate::policy(Product::class, ProductPolicy::class);
 *   }
 */
class ProductPolicy
{
    public function update(User $user, Product $product): bool
    {
        return $product->store->user_id === $user->id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $product->store->user_id === $user->id;
    }
}
