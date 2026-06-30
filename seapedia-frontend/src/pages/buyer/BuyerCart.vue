<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="font-display font-bold text-2xl text-ink-900 mb-6">Keranjang Belanja</h1>

    <!-- Loading -->
    <div v-if="cart.loading" class="bg-white rounded-2xl border border-ink-100 p-10 text-center">
      <p class="text-sm text-ink-500">Memuat keranjang...</p>
    </div>

    <!-- Empty cart -->
    <div
      v-else-if="cart.isEmpty"
      class="bg-white rounded-2xl border border-ink-100 p-10 text-center"
    >
      <p class="text-4xl mb-3">🛒</p>
      <p class="text-sm font-semibold text-ink-900">Keranjang kamu masih kosong</p>
      <p class="text-xs text-ink-500 mt-1 mb-4">Yuk mulai cari produk favoritmu</p>
      <RouterLink
        to="/products"
        class="inline-block bg-emerald-700 text-white text-sm font-semibold px-5 py-2 rounded-lg hover:bg-emerald-800"
      >
        Mulai Belanja
      </RouterLink>
    </div>

    <!-- Cart content -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: items -->
      <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-2xl border border-ink-100 p-5">
          <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-semibold text-ink-900">
              Toko: <span class="text-emerald-700">{{ cart.storeName }}</span>
            </p>
            <button
              @click="handleClearCart"
              class="text-xs font-semibold text-red-600 hover:text-red-700"
            >
              Kosongkan Keranjang
            </button>
          </div>

          <div class="divide-y divide-ink-50">
            <div
              v-for="item in cart.items"
              :key="item.id"
              class="flex items-center gap-4 py-4"
            >
              <div
                class="w-16 h-16 rounded-lg bg-ink-50 shrink-0 flex items-center justify-center text-ink-300 text-xs overflow-hidden"
              >
                <img
                  v-if="item.product.image_url"
                  :src="item.product.image_url"
                  :alt="item.product.name"
                  class="w-full h-full object-cover"
                />
                <span v-else>No Img</span>
              </div>

              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-ink-900 truncate">
                  {{ item.product.name }}
                </p>
                <p class="text-xs text-ink-500 mt-0.5">{{ formatRupiah(item.product.price) }}</p>
              </div>

              <div class="flex items-center gap-2">
                <button
                  @click="changeQty(item, item.quantity - 1)"
                  :disabled="updatingId === item.id"
                  class="w-7 h-7 rounded-md border border-ink-200 text-ink-600 hover:bg-ink-50 disabled:opacity-40"
                >
                  −
                </button>
                <span class="w-8 text-center text-sm font-semibold text-ink-900">
                  {{ item.quantity }}
                </span>
                <button
                  @click="changeQty(item, item.quantity + 1)"
                  :disabled="updatingId === item.id || item.quantity >= item.product.stock"
                  class="w-7 h-7 rounded-md border border-ink-200 text-ink-600 hover:bg-ink-50 disabled:opacity-40"
                >
                  +
                </button>
              </div>

              <p class="w-28 text-right text-sm font-bold text-ink-900">
                {{ formatRupiah(item.product.price * item.quantity) }}
              </p>

              <button
                @click="handleRemoveItem(item.id)"
                class="text-ink-300 hover:text-red-600"
                title="Hapus produk"
              >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Alamat -->
        <div class="bg-white rounded-2xl border border-ink-100 p-5">
          <h2 class="text-sm font-semibold text-ink-900 mb-3">Alamat Pengiriman</h2>
          <div v-if="addresses.length === 0" class="text-xs text-ink-400">
            Belum ada alamat. Tambahkan dulu sebelum checkout.
          </div>
          <div v-else class="space-y-2">
            <label
              v-for="addr in addresses"
              :key="addr.id"
              class="flex items-start gap-3 border rounded-lg p-3 cursor-pointer"
              :class="
                selectedAddressId === addr.id
                  ? 'border-emerald-500 bg-emerald-50/40'
                  : 'border-ink-100'
              "
            >
              <input
                type="radio"
                :value="addr.id"
                v-model="selectedAddressId"
                class="mt-1 accent-emerald-600"
              />
              <div>
                <p class="text-sm font-semibold text-ink-900">{{ addr.recipient_name }}</p>
                <p class="text-xs text-ink-500 mt-0.5">{{ addr.full_address }}</p>
              </div>
            </label>
          </div>
        </div>

        <!-- Delivery method -->
        <div class="bg-white rounded-2xl border border-ink-100 p-5">
          <h2 class="text-sm font-semibold text-ink-900 mb-3">Metode Pengiriman</h2>
          <div class="grid grid-cols-3 gap-2">
            <label
              v-for="method in deliveryMethods"
              :key="method.value"
              class="border rounded-lg p-3 text-center cursor-pointer"
              :class="
                selectedDelivery === method.value
                  ? 'border-emerald-500 bg-emerald-50/40'
                  : 'border-ink-100'
              "
            >
              <input
                type="radio"
                :value="method.value"
                v-model="selectedDelivery"
                class="sr-only"
              />
              <p class="text-sm font-semibold text-ink-900">{{ method.label }}</p>
              <p class="text-xs text-ink-500 mt-0.5">{{ formatRupiah(method.fee) }}</p>
            </label>
          </div>
        </div>
      </div>

      <!-- Right: summary -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-ink-100 p-5 sticky top-24">
          <h2 class="text-sm font-semibold text-ink-900 mb-4">Ringkasan Belanja</h2>

          <!-- Voucher input -->
          <div class="mb-4">
            <label class="text-xs text-ink-500 mb-1.5 block">Kode Voucher / Promo</label>
            <div class="flex gap-2">
              <input
                v-model="discountCode"
                type="text"
                placeholder="Contoh: SEAPEDIA10"
                class="flex-1 border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
              />
            </div>
            <p v-if="discountMessage" class="text-xs mt-1.5" :class="discountValid ? 'text-emerald-600' : 'text-red-600'">
              {{ discountMessage }}
            </p>
          </div>

          <div class="space-y-2 text-sm border-t border-ink-50 pt-4">
            <div class="flex justify-between text-ink-600">
              <span>Subtotal</span>
              <span>{{ formatRupiah(cart.subtotal) }}</span>
            </div>
            <div v-if="previewDiscount > 0" class="flex justify-between text-emerald-600">
              <span>Diskon</span>
              <span>−{{ formatRupiah(previewDiscount) }}</span>
            </div>
            <div class="flex justify-between text-ink-600">
              <span>Ongkos Kirim</span>
              <span>{{ formatRupiah(selectedDeliveryFee) }}</span>
            </div>
            <div class="flex justify-between text-ink-600">
              <span>PPN 12%</span>
              <span>{{ formatRupiah(ppnAmount) }}</span>
            </div>
          </div>

          <div class="flex justify-between items-center border-t border-ink-100 mt-4 pt-4">
            <span class="text-sm font-semibold text-ink-900">Total</span>
            <span class="font-display font-bold text-lg text-ink-900">
              {{ formatRupiah(totalAmount) }}
            </span>
          </div>

          <p v-if="checkoutError" class="text-xs text-red-600 mt-3">{{ checkoutError }}</p>

          <button
            @click="handleCheckout"
            :disabled="!canCheckout || checkoutLoading"
            class="w-full mt-4 bg-emerald-700 text-white font-semibold text-sm py-2.5 rounded-lg hover:bg-emerald-800 disabled:opacity-40 disabled:cursor-not-allowed"
          >
            {{ checkoutLoading ? 'Memproses...' : 'Checkout Sekarang' }}
          </button>

          <p class="text-[11px] text-ink-400 mt-3 text-center">
            Satu keranjang hanya bisa berisi produk dari satu toko yang sama.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import api from '@/api/axios'

const router = useRouter()
const cart = useCartStore()

const addresses = ref([])
const selectedAddressId = ref(null)

const deliveryMethods = [
  { value: 'instant', label: 'Instant', fee: 15000 },
  { value: 'next_day', label: 'Next Day', fee: 12000 },
  { value: 'regular', label: 'Regular', fee: 9000 },
]
const selectedDelivery = ref('regular')

const discountCode = ref('')
const discountMessage = ref('')
const discountValid = ref(false)
const previewDiscount = ref(0)

const updatingId = ref(null)
const checkoutLoading = ref(false)
const checkoutError = ref('')

const selectedDeliveryFee = computed(() => {
  return deliveryMethods.find((m) => m.value === selectedDelivery.value)?.fee ?? 0
})

const ppnAmount = computed(() => {
  const taxable = cart.subtotal - previewDiscount.value + selectedDeliveryFee.value
  return Math.round(taxable * 0.12)
})

const totalAmount = computed(() => {
  return cart.subtotal - previewDiscount.value + selectedDeliveryFee.value + ppnAmount.value
})

const canCheckout = computed(() => {
  return !cart.isEmpty && selectedAddressId.value && selectedDelivery.value
})

async function changeQty(item, newQty) {
  if (newQty < 1) return
  updatingId.value = item.id
  try {
    await cart.updateQuantity(item.id, newQty)
  } finally {
    updatingId.value = null
  }
}

async function handleRemoveItem(itemId) {
  await cart.removeItem(itemId)
}

async function handleClearCart() {
  if (!confirm('Kosongkan seluruh keranjang?')) return
  await cart.clearCart()
}

async function loadAddresses() {
  const { data } = await api.get('/buyer/addresses')
  addresses.value = data?.data ?? data ?? []
  const def = addresses.value.find((a) => a.is_default)
  selectedAddressId.value = def?.id ?? addresses.value[0]?.id ?? null
}

// Preview diskon dilakukan secara optimis di frontend untuk UX,
// validasi FINAL tetap di backend saat checkout (sesuai business rule).
async function previewDiscountCode() {
  if (!discountCode.value.trim()) {
    discountMessage.value = ''
    previewDiscount.value = 0
    return
  }
  // Validasi sebenarnya terjadi saat checkout; di sini kita cuma
  // beri sinyal visual bahwa kode akan dicoba dipakai.
  discountMessage.value = 'Kode akan divalidasi saat checkout.'
  discountValid.value = true
}

async function handleCheckout() {
  checkoutError.value = ''
  checkoutLoading.value = true
  try {
    const payload = {
      address_id: selectedAddressId.value,
      delivery_method: selectedDelivery.value,
    }
    if (discountCode.value.trim()) {
      payload.discount_code = discountCode.value.trim()
    }

    const { data } = await api.post('/buyer/checkout', payload)

    await cart.fetchCart()
    router.push({
      path: '/buyer/dashboard',
      query: { checkout: 'success', order: data.order?.order_number },
    })
  } catch (err) {
    checkoutError.value =
      err.response?.data?.message ?? 'Checkout gagal. Silakan coba lagi.'
  } finally {
    checkoutLoading.value = false
  }
}

function formatRupiah(value) {
  return 'Rp' + Number(value ?? 0).toLocaleString('id-ID')
}

onMounted(async () => {
  await cart.fetchCart()
  await loadAddresses()
})
</script>
