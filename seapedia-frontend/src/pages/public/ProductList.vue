<template>
  <div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex items-center gap-3 mb-4">
      <h1 class="font-display font-bold text-xl text-ink-900">
        {{ headerTitle }}
      </h1>
      <button
        v-if="categoryFilter"
        @click="clearCategory"
        class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-full"
      >
        {{ categoryFilter }} ✕
      </button>
    </div>

    <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
      <div
        v-for="i in 10"
        :key="i"
        class="bg-white rounded-xl border border-ink-100 aspect-[3/4.2] animate-pulse"
      />
    </div>

    <div v-else-if="products.length === 0" class="text-center py-20">
      <p class="text-ink-400">Produk tidak ditemukan.</p>
    </div>

    <div v-else class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
      <ProductCard v-for="p in products" :key="p.id" :product="p" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'
import ProductCard from '@/components/product/ProductCard.vue'

const route = useRoute()
const products = ref([])
const loading = ref(true)
const searchQuery = ref(route.query.q || '')
const categoryFilter = ref(route.query.category || '')

const headerTitle = computed(() => {
  if (searchQuery.value) return `Hasil untuk "${searchQuery.value}"`
  if (categoryFilter.value) return categoryFilter.value
  return 'Semua Produk'
})

async function loadProducts() {
  loading.value = true
  try {
    const { data } = await api.get('/public/products', {
      params: {
        q: searchQuery.value || undefined,
        category: categoryFilter.value || undefined,
      },
    })
    products.value = data.data ?? data
  } catch (e) {
    products.value = []
  } finally {
    loading.value = false
  }
}

function clearCategory() {
  categoryFilter.value = ''
  const query = { ...route.query }
  delete query.category
  history.pushState({}, '', `${route.path}${Object.keys(query).length ? '?' + new URLSearchParams(query) : ''}`)
  loadProducts()
}

watch(
  () => [route.query.q, route.query.category],
  ([q, category]) => {
    searchQuery.value = q || ''
    categoryFilter.value = category || ''
    loadProducts()
  }
)

onMounted(loadProducts)
</script>
