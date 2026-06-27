import { defineStore } from 'pinia'
import api from '@/api/axios'

/**
 * useCartStore
 *
 * Menyimpan state keranjang belanja buyer. Memuat aturan single-store
 * checkout: cart hanya boleh berisi produk dari satu store.
 */
export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [], // [{ id, product, quantity }]
    storeId: null,
    storeName: null,
    loading: false,
  }),

  getters: {
    isEmpty: (state) => state.items.length === 0,
    totalItems: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
    subtotal: (state) =>
      state.items.reduce((sum, item) => sum + item.product.price * item.quantity, 0),
  },

  actions: {
    async fetchCart() {
      this.loading = true
      try {
        const { data } = await api.get('/buyer/cart-items')
        this.items = data.items
        this.storeId = data.store_id
        this.storeName = data.store_name
      } finally {
        this.loading = false
      }
    },

    /**
     * Menambah produk ke cart. Kalau produk dari store berbeda dari isi
     * cart saat ini, backend akan menolak dengan 409 — komponen pemanggil
     * harus menangkap error ini dan menampilkan modal konfirmasi
     * "kosongkan cart dulu?" (lihat business rule single-store checkout).
     */
    async addItem(productId, quantity = 1) {
      const { data } = await api.post('/buyer/cart-items', {
        product_id: productId,
        quantity,
      })
      await this.fetchCart()
      return data
    },

    async updateQuantity(cartItemId, quantity) {
      await api.put(`/buyer/cart-items/${cartItemId}`, { quantity })
      await this.fetchCart()
    },

    async removeItem(cartItemId) {
      await api.delete(`/buyer/cart-items/${cartItemId}`)
      await this.fetchCart()
    },

    async clearCart() {
      await api.delete('/buyer/cart-items')
      this.items = []
      this.storeId = null
      this.storeName = null
    },
  },
})
