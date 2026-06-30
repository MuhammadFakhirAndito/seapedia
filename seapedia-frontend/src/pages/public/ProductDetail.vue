<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <div v-if="loading" class="animate-pulse grid md:grid-cols-2 gap-8">
      <div class="aspect-square bg-ink-100 rounded-xl" />
      <div class="space-y-3">
        <div class="h-6 bg-ink-100 rounded w-3/4" />
        <div class="h-4 bg-ink-100 rounded w-1/3" />
        <div class="h-10 bg-ink-100 rounded w-1/2" />
      </div>
    </div>

    <div v-else-if="!product" class="text-center py-20 text-ink-400">
      Produk tidak ditemukan.
    </div>

    <div v-else class="grid md:grid-cols-2 gap-8">
      <div class="aspect-square bg-ink-50 rounded-xl overflow-hidden">
        <img
          :src="product.image_url || product.image || placeholder"
          :alt="product.name"
          class="w-full h-full object-cover"
          @error="$event.target.src = placeholder"
        />
      </div>

      <div>
        <h1 class="font-display font-bold text-2xl text-ink-900">{{ product.name }}</h1>

        <div class="flex items-center gap-2 mt-2 text-sm text-ink-500">
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4 text-accent-500 fill-current" viewBox="0 0 20 20">
              <path d="M10 1l2.6 6.3 6.8.5-5.2 4.4 1.7 6.6L10 15.5 4.1 18.8l1.7-6.6L.6 7.8l6.8-.5L10 1z" />
            </svg>
            {{ product.rating ?? '5.0' }}
          </span>
          <span>•</span>
          <span>{{ product.sold_count ?? 0 }} terjual</span>
        </div>

        <p class="font-display font-extrabold text-3xl text-ink-900 mt-4">
          {{ formatPrice(product.price) }}
        </p>

        <div class="mt-5 pt-5 border-t border-ink-100">
          <p class="text-sm font-semibold text-ink-700 mb-1">Toko</p>
          <RouterLink
            :to="`/stores/${product.store?.id}`"
            class="flex items-center gap-2 text-sm text-brand-600 hover:underline"
          >
            <span class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center font-bold text-brand-700">
              {{ product.store?.name?.charAt(0) }}
            </span>
            {{ product.store?.name }}
          </RouterLink>
        </div>

        <div class="mt-5 pt-5 border-t border-ink-100">
          <p class="text-sm font-semibold text-ink-700 mb-2">Deskripsi Produk</p>
          <p class="text-sm text-ink-600 leading-relaxed whitespace-pre-line">
            {{ product.description || 'Tidak ada deskripsi.' }}
          </p>
        </div>

        <div class="flex gap-3 mt-7">
          <div class="flex items-center border border-ink-200 rounded-lg">
            <button
              @click="quantity > 1 && quantity--"
              class="w-9 h-10 flex items-center justify-center text-ink-500 hover:text-ink-800"
            >
              −
            </button>
            <span class="w-10 text-center text-sm font-medium">{{ quantity }}</span>
            <button
              @click="quantity++"
              class="w-9 h-10 flex items-center justify-center text-ink-500 hover:text-ink-800"
            >
              +
            </button>
          </div>

          <BaseButton variant="outline" size="lg" class="flex-1" @click="handleAddToCart">
            + Keranjang
          </BaseButton>
          <BaseButton variant="secondary" size="lg" class="flex-1" @click="handleBuyNow">
            Beli Sekarang
          </BaseButton>
        </div>

        <p v-if="cartError" class="text-sm text-red-600 bg-red-50 rounded-md px-3 py-2 mt-3">
          {{ cartError }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import BaseButton from '@/components/common/BaseButton.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const cart = useCartStore()

const product = ref(null)
const loading = ref(true)
const quantity = ref(1)
const cartError = ref('')

const placeholder =
  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400"%3E%3Crect fill="%23ececea" width="400" height="400"/%3E%3C/svg%3E'

async function loadProduct() {
  loading.value = true
  try {
    const { data } = await api.get(`/public/products/${route.params.id}`)
    product.value = data
  } catch (e) {
    product.value = null
  } finally {
    loading.value = false
  }
}

function formatPrice(value) {
  return 'Rp' + Number(value).toLocaleString('id-ID')
}

async function handleAddToCart() {
  cartError.value = ''
  if (!auth.isLoggedIn) {
    router.push('/login')
    return
  }
  try {
    await cart.addItem(product.value.id, quantity.value)
  } catch (e) {
    if (e.response?.status === 409) {
      cartError.value =
        'Keranjang Anda berisi produk dari toko lain. Kosongkan keranjang dulu untuk menambah produk ini.'
    } else {
      cartError.value = 'Gagal menambahkan ke keranjang.'
    }
  }
}

async function handleBuyNow() {
  await handleAddToCart()
  if (!cartError.value) router.push('/buyer/cart')
}

onMounted(loadProduct)
</script>
