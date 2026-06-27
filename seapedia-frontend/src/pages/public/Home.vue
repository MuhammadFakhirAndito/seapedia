<template>
  <div class="max-w-7xl mx-auto px-4 py-5">
    <!-- Hero banner -->
    <section
      class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-brand-600 to-brand-800 mb-6"
    >
      <div class="px-8 py-10 md:px-14 md:py-14 max-w-xl">
        <span
          class="inline-block bg-accent-500 text-white text-xs font-bold px-3 py-1 rounded-full mb-4"
        >
          PROMO PENGGUNA BARU
        </span>
        <h1 class="font-display text-3xl md:text-4xl font-extrabold text-white leading-tight">
          Belanja Apa Saja, Diantar Hari Ini
        </h1>
        <p class="text-brand-100 mt-3 text-sm md:text-base">
          Ribuan produk dari penjual terpercaya, dikirim oleh mitra pengantaran
          SEAPEDIA langsung ke depan pintu Anda.
        </p>
        <RouterLink
          to="/products"
          class="inline-block mt-6 bg-white text-brand-700 font-semibold px-6 py-2.5 rounded-md hover:bg-brand-50 transition-colors text-sm"
        >
          Mulai Belanja
        </RouterLink>
      </div>
      <div
        class="hidden md:block absolute right-0 top-0 bottom-0 w-72 opacity-20"
        style="background: radial-gradient(circle at 70% 50%, white, transparent 60%)"
      />
    </section>

    <!-- Category grid -->
    <section class="mb-8">
      <div class="grid grid-cols-4 md:grid-cols-8 gap-3">
        <button
          v-for="cat in categories"
          :key="cat.name"
          class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-white hover:shadow-sm transition-all"
        >
          <span
            class="w-12 h-12 rounded-full flex items-center justify-center text-2xl"
            :style="{ background: cat.bg }"
          >
            {{ cat.emoji }}
          </span>
          <span class="text-xs text-ink-600 text-center leading-tight">{{ cat.name }}</span>
        </button>
      </div>
    </section>

    <!-- Voucher strip -->
    <section class="mb-8">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-display font-bold text-lg text-ink-900">Voucher Spesial Hari Ini</h2>
      </div>
      <div class="flex gap-3 overflow-x-auto pb-1">
        <div
          v-for="v in vouchers"
          :key="v.code"
          class="shrink-0 w-64 flex items-center bg-white rounded-xl border border-dashed border-accent-300 overflow-hidden"
        >
          <div
            class="w-16 h-full flex items-center justify-center bg-accent-50 text-accent-600"
          >
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.8"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
          </div>
          <div class="flex-1 px-3 py-2.5">
            <p class="text-sm font-bold text-ink-800">{{ v.label }}</p>
            <p class="text-xs text-ink-500">Kode: {{ v.code }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Product grid -->
    <section>
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-display font-bold text-lg text-ink-900">Rekomendasi Untuk Anda</h2>
        <RouterLink to="/products" class="text-sm text-brand-600 font-medium hover:underline">
          Lihat Semua
        </RouterLink>
      </div>

      <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
        <div
          v-for="i in 10"
          :key="i"
          class="bg-white rounded-xl border border-ink-100 aspect-[3/4.2] animate-pulse"
        />
      </div>

      <div v-else-if="products.length === 0" class="text-center py-16">
        <p class="text-ink-400">Belum ada produk tersedia saat ini.</p>
      </div>

      <div v-else class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
        <ProductCard v-for="p in products" :key="p.id" :product="p" />
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'
import ProductCard from '@/components/product/ProductCard.vue'

const products = ref([])
const loading = ref(true)

const categories = [
  { name: 'Elektronik', emoji: '📱', bg: '#eefcf6' },
  { name: 'Fashion', emoji: '👕', bg: '#fff7ed' },
  { name: 'Kesehatan', emoji: '💊', bg: '#eefcf6' },
  { name: 'Rumah Tangga', emoji: '🏠', bg: '#fff7ed' },
  { name: 'Hobi', emoji: '🎮', bg: '#eefcf6' },
  { name: 'Olahraga', emoji: '⚽', bg: '#fff7ed' },
  { name: 'Otomotif', emoji: '🚗', bg: '#eefcf6' },
  { name: 'Lainnya', emoji: '✨', bg: '#fff7ed' },
]

const vouchers = [
  { code: 'SEAPEDIA10', label: 'Diskon 10% s/d Rp50rb' },
  { code: 'HEMAT20K', label: 'Potongan Rp20.000' },
]

async function loadProducts() {
  loading.value = true
  try {
    const { data } = await api.get('/public/products')
    products.value = data.data ?? data
  } catch (e) {
    // Backend belum siap / belum ada produk — tampilkan empty state, jangan error ke user
    products.value = []
  } finally {
    loading.value = false
  }
}

onMounted(loadProducts)
</script>
