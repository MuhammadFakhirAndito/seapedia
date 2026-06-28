<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getOrCreateCart(User $user)
    {
        return $user->cart()->firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * @throws ValidationException jika produk dari toko berbeda dari isi cart saat ini
     */
    public function addItem(User $user, int $productId, int $quantity): array
    {
        $product = Product::with('store')->findOrFail($productId);
        $cart = $this->getOrCreateCart($user)->load('items.product');

        // Cart sudah punya isi dari toko lain -> tolak
        if ($cart->store_id !== null && $cart->store_id !== $product->store_id) {
            throw ValidationException::withMessages([
                'cart' => "Keranjang Anda berisi produk dari toko lain ({$this->getCurrentStoreName($cart)}). ".
                          'Kosongkan keranjang terlebih dahulu untuk menambah produk dari toko ini.',
            ])->status(409);
        }

        if ($product->stock < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Stock produk tidak cukup.',
            ]);
        }

        // Set store lock kalau ini item pertama di cart
        if ($cart->store_id === null) {
            $cart->update(['store_id' => $product->store_id]);
        }

        $existingItem = $cart->items->firstWhere('product_id', $productId);

        if ($existingItem) {
            $existingItem->update(['quantity' => $existingItem->quantity + $quantity]);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return $this->getCartSummary($user);
    }

    public function updateQuantity(User $user, int $cartItemId, int $quantity): array
    {
        $cart = $this->getOrCreateCart($user);
        $item = $cart->items()->findOrFail($cartItemId);

        if ($quantity < 1) {
            $item->delete();
        } else {
            if ($item->product->stock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock produk tidak cukup.',
                ]);
            }
            $item->update(['quantity' => $quantity]);
        }

        $this->resetStoreLockIfEmpty($cart);

        return $this->getCartSummary($user);
    }

    public function removeItem(User $user, int $cartItemId): array
    {
        $cart = $this->getOrCreateCart($user);
        $cart->items()->where('id', $cartItemId)->delete();

        $this->resetStoreLockIfEmpty($cart);

        return $this->getCartSummary($user);
    }

    public function clearCart(User $user): void
    {
        $cart = $this->getOrCreateCart($user);
        $cart->items()->delete();
        $cart->update(['store_id' => null]);
    }

    public function getCartSummary(User $user): array
    {
        $cart = $this->getOrCreateCart($user)->load('items.product', 'store:id,name');

        $items = $cart->items->map(fn ($item) => [
            'id' => $item->id,
            'product' => $item->product,
            'quantity' => $item->quantity,
            'subtotal' => $item->product->price * $item->quantity,
        ]);

        return [
            'cart_id' => $cart->id,
            'store_id' => $cart->store_id,
            'store_name' => $cart->store?->name,
            'items' => $items,
            'subtotal' => $items->sum('subtotal'),
        ];
    }

    /**
     * Kalau setelah update/remove cart jadi kosong, lepas store lock
     * supaya buyer bebas pilih toko lain lagi untuk belanja berikutnya.
     */
    private function resetStoreLockIfEmpty($cart): void
    {
        if ($cart->items()->count() === 0 && $cart->store_id !== null) {
            $cart->update(['store_id' => null]);
        }
    }

    private function getCurrentStoreName($cart): string
    {
        return $cart->store?->name ?? 'toko lain';
    }
}
