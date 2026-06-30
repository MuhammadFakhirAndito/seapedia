<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Loading -->
    <div v-if="loading" class="animate-pulse space-y-6">
      <div class="h-32 bg-ink-100 rounded-2xl" />
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
        <div v-for="i in 5" :key="i" class="aspect-[3/4.2] bg-ink-100 rounded-xl" />
      </div>
    </div>

    <!-- Not found -->
    <div v-else-if="!store" class="text-center py-20">
      <p class="text-ink-400">Toko tidak ditemukan.</p>
      <RouterLink to="/products" class="text-emerald-700 text-sm font-semibold hover:underline mt-2 inline-block">
        ← Kembali ke katalog
      </RouterLink>
    </div>

    <template v-else>
      <!-- Store header -->
      <div class="bg-white rounded-2xl border border-ink-100 p-6 mb-6">
        <div class="flex items-center gap-4">
          <span
            class="w-16 h-16 rounded-full bg-brand-100 flex items-center justify-center font-display font-bold text-2xl text-brand-700 shrink-0"
          >
            {{ store.name?.charAt(0) }}
          </span>
          <div>
            <h1 class="font-display font-bold text-xl text-ink-900">{{ store.name }}</h1>
            <p class="text-sm text-ink-500 mt-1">
              {{ store.description || 'Toko terpercaya di SEAPEDIA' }}
            </p>
            <p class="text-xs text-ink-400 mt-2">
              {{ products.length }} produk tersedia
            </p>
          </div>
        </div>
      </div>

      <!-- Products grid -->
      <h2 class="font-display font-bold text-lg text-ink-900 mb-3">Produk dari {{ store.name }}</h2>

      <div
        v-if="products.length === 0"
        class="bg-white rounded-2xl border border-ink-100 p-10 text-center text-sm text-ink-400"
      >
        Toko ini belum memiliki produk.
      </div>

      <div v-else class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
        <ProductCard v-for="p in products" :key="p.id" :product="p" />
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'
import ProductCard from '@/components/product/ProductCard.vue'

const route = useRoute()
const store = ref(null)
const products = ref([])
const loading = ref(true)

async function loadStore() {
  loading.value = true
  try {
    const { data } = await api.get(`/public/stores/${route.params.id}`)
    store.value = data.store ?? data
    products.value = data.products ?? data.store?.products ?? []
  } catch (e) {
    store.value = null
  } finally {
    loading.value = false
  }
}

onMounted(loadStore)
</script>
