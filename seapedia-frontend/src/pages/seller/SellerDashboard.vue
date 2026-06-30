<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="font-display font-bold text-2xl text-ink-900">Dashboard Penjual</h1>
        <p class="text-sm text-ink-500 mt-1">
          {{ store?.name ?? 'Belum punya toko' }}
        </p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 border-b border-ink-100 mb-6">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        @click="activeTab = tab.value"
        class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition-colors"
        :class="
          activeTab === tab.value
            ? 'border-emerald-600 text-emerald-700'
            : 'border-transparent text-ink-400 hover:text-ink-600'
        "
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- TAB: Overview -->
    <div v-if="activeTab === 'overview'" class="space-y-6">
      <div v-if="loading" class="bg-white rounded-2xl border border-ink-100 p-10 text-center">
        <p class="text-sm text-ink-500">Memuat...</p>
      </div>

      <template v-else>
        <!-- Store form -->
        <div class="bg-white rounded-2xl border border-ink-100 p-5">
          <h2 class="text-sm font-semibold text-ink-900 mb-3">
            {{ store ? 'Profil Toko' : 'Buat Toko' }}
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <input
              v-model="storeForm.name"
              type="text"
              placeholder="Nama toko"
              class="border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
            <input
              v-model="storeForm.description"
              type="text"
              placeholder="Deskripsi singkat"
              class="border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
          </div>
          <p v-if="storeError" class="text-xs text-red-600 mt-2">{{ storeError }}</p>
          <button
            @click="handleSaveStore"
            :disabled="storeSaving"
            class="mt-3 bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-emerald-800 disabled:opacity-50"
          >
            {{ storeSaving ? 'Menyimpan...' : store ? 'Update Toko' : 'Buat Toko' }}
          </button>
        </div>

        <!-- Income summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="bg-white rounded-2xl border border-ink-100 p-5">
            <p class="text-xs text-ink-500 mb-1">Total Pendapatan</p>
            <p class="font-display font-bold text-xl text-ink-900">
              {{ formatRupiah(income?.total_income ?? 0) }}
            </p>
          </div>
          <div class="bg-white rounded-2xl border border-ink-100 p-5">
            <p class="text-xs text-ink-500 mb-1">Pendapatan Tertunda</p>
            <p class="font-display font-bold text-xl text-ink-900">
              {{ formatRupiah(income?.pending_income ?? 0) }}
            </p>
          </div>
          <div class="bg-white rounded-2xl border border-ink-100 p-5">
            <p class="text-xs text-ink-500 mb-1">Total Produk</p>
            <p class="font-display font-bold text-xl text-ink-900">{{ products.length }}</p>
          </div>
        </div>
      </template>
    </div>

    <!-- TAB: Produk -->
    <div v-else-if="activeTab === 'products'" class="space-y-4">
      <!-- Form tambah produk -->
      <div class="bg-white rounded-2xl border border-ink-100 p-5">
        <h2 class="text-sm font-semibold text-ink-900 mb-3">
          {{ editingProductId ? 'Edit Produk' : 'Tambah Produk Baru' }}
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <input
            v-model="productForm.name"
            type="text"
            placeholder="Nama produk"
            class="border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
          />
          <input
            v-model.number="productForm.price"
            type="number"
            placeholder="Harga (Rp)"
            class="border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
          />
          <input
            v-model.number="productForm.stock"
            type="number"
            placeholder="Stok"
            class="border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
          />
          <input
            v-model="productForm.description"
            type="text"
            placeholder="Deskripsi"
            class="border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
          />
        </div>
        <p v-if="productError" class="text-xs text-red-600 mt-2">{{ productError }}</p>
        <div class="flex gap-2 mt-3">
          <button
            @click="handleSaveProduct"
            :disabled="productSaving"
            class="bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-emerald-800 disabled:opacity-50"
          >
            {{ productSaving ? 'Menyimpan...' : editingProductId ? 'Update Produk' : 'Tambah Produk' }}
          </button>
          <button
            v-if="editingProductId"
            @click="cancelEditProduct"
            class="border border-ink-200 text-ink-600 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-ink-50"
          >
            Batal
          </button>
        </div>
      </div>

      <!-- List produk -->
      <div class="bg-white rounded-2xl border border-ink-100 p-5">
        <h2 class="text-sm font-semibold text-ink-900 mb-3">Produk Saya ({{ products.length }})</h2>
        <div v-if="products.length === 0" class="text-sm text-ink-400 py-4 text-center">
          Belum ada produk.
        </div>
        <div v-else class="divide-y divide-ink-50">
          <div
            v-for="p in products"
            :key="p.id"
            class="flex items-center justify-between py-3"
          >
            <div>
              <p class="text-sm font-semibold text-ink-900">{{ p.name }}</p>
              <p class="text-xs text-ink-500 mt-0.5">
                {{ formatRupiah(p.price) }} · Stok: {{ p.stock }}
              </p>
            </div>
            <div class="flex gap-2">
              <button
                @click="startEditProduct(p)"
                class="text-xs font-semibold text-emerald-700 hover:text-emerald-800"
              >
                Edit
              </button>
              <button
                @click="handleDeleteProduct(p.id)"
                class="text-xs font-semibold text-red-600 hover:text-red-700"
              >
                Hapus
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB: Pesanan -->
    <div v-else-if="activeTab === 'orders'" class="bg-white rounded-2xl border border-ink-100 p-5">
      <h2 class="text-sm font-semibold text-ink-900 mb-3">Pesanan Masuk</h2>

      <div v-if="orders.length === 0" class="text-sm text-ink-400 py-4 text-center">
        Belum ada pesanan masuk.
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="order in orders"
          :key="order.id"
          class="border border-ink-100 rounded-xl p-4"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-semibold text-ink-900">{{ order.order_number }}</p>
              <p class="text-xs text-ink-500 mt-0.5">
                {{ order.items?.length ?? 0 }} produk · {{ formatRupiah(order.total_amount) }}
              </p>
            </div>
            <span
              class="text-[11px] font-semibold px-2.5 py-1 rounded-full"
              :class="statusBadge(order.status)"
            >
              {{ statusLabel(order.status) }}
            </span>
          </div>

          <div class="flex items-center justify-between mt-3 pt-3 border-t border-ink-50">
            <p class="text-xs text-ink-400">{{ formatDate(order.created_at) }}</p>
            <button
              v-if="order.status === 'sedang_dikemas'"
              @click="handleProcessOrder(order.id)"
              :disabled="processingId === order.id"
              class="bg-emerald-700 text-white text-xs font-semibold px-4 py-1.5 rounded-lg hover:bg-emerald-800 disabled:opacity-50"
            >
              {{ processingId === order.id ? 'Memproses...' : 'Proses Pesanan' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'

const activeTab = ref('overview')
const tabs = [
  { value: 'overview', label: 'Overview' },
  { value: 'products', label: 'Produk' },
  { value: 'orders', label: 'Pesanan' },
]

const loading = ref(true)
const store = ref(null)
const products = ref([])
const orders = ref([])
const income = ref(null)

// Store form
const storeForm = ref({ name: '', description: '' })
const storeSaving = ref(false)
const storeError = ref('')

// Product form
const productForm = ref({ name: '', price: null, stock: null, description: '' })
const editingProductId = ref(null)
const productSaving = ref(false)
const productError = ref('')

const processingId = ref(null)

async function loadAll() {
  loading.value = true
  try {
    const [storeRes, productRes, orderRes, incomeRes] = await Promise.allSettled([
      api.get('/seller/store'),
      api.get('/seller/products'),
      api.get('/seller/orders'),
      api.get('/seller/reports/income'),
    ])

    if (storeRes.status === 'fulfilled') {
      store.value = storeRes.value.data
      storeForm.value = {
        name: store.value?.name ?? '',
        description: store.value?.description ?? '',
      }
    }
    if (productRes.status === 'fulfilled') {
      products.value = productRes.value.data?.data ?? productRes.value.data ?? []
    }
    if (orderRes.status === 'fulfilled') {
      orders.value = orderRes.value.data?.data ?? orderRes.value.data ?? []
    }
    if (incomeRes.status === 'fulfilled') {
      income.value = incomeRes.value.data
    }
  } finally {
    loading.value = false
  }
}

async function handleSaveStore() {
  storeError.value = ''
  storeSaving.value = true
  try {
    const { data } = await api.post('/seller/store', storeForm.value)
    store.value = data
  } catch (err) {
    storeError.value = err.response?.data?.message ?? 'Gagal menyimpan toko.'
  } finally {
    storeSaving.value = false
  }
}

function startEditProduct(p) {
  editingProductId.value = p.id
  productForm.value = {
    name: p.name,
    price: p.price,
    stock: p.stock,
    description: p.description ?? '',
  }
}

function cancelEditProduct() {
  editingProductId.value = null
  productForm.value = { name: '', price: null, stock: null, description: '' }
  productError.value = ''
}

async function handleSaveProduct() {
  productError.value = ''
  productSaving.value = true
  try {
    if (editingProductId.value) {
      await api.put(`/seller/products/${editingProductId.value}`, productForm.value)
    } else {
      await api.post('/seller/products', productForm.value)
    }
    cancelEditProduct()
    const { data } = await api.get('/seller/products')
    products.value = data?.data ?? data ?? []
  } catch (err) {
    productError.value = err.response?.data?.message ?? 'Gagal menyimpan produk.'
  } finally {
    productSaving.value = false
  }
}

async function handleDeleteProduct(id) {
  if (!confirm('Hapus produk ini?')) return
  await api.delete(`/seller/products/${id}`)
  products.value = products.value.filter((p) => p.id !== id)
}

async function handleProcessOrder(orderId) {
  processingId.value = orderId
  try {
    await api.post(`/seller/orders/${orderId}/process`)
    const { data } = await api.get('/seller/orders')
    orders.value = data?.data ?? data ?? []
  } catch (err) {
    alert(err.response?.data?.message ?? 'Gagal memproses order.')
  } finally {
    processingId.value = null
  }
}

function formatRupiah(value) {
  return 'Rp' + Number(value ?? 0).toLocaleString('id-ID')
}

function formatDate(value) {
  if (!value) return '-'
  return new Date(value).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

const STATUS_LABELS = {
  sedang_dikemas: 'Sedang Dikemas',
  menunggu_pengirim: 'Menunggu Pengirim',
  sedang_dikirim: 'Sedang Dikirim',
  pesanan_selesai: 'Pesanan Selesai',
  dikembalikan: 'Dikembalikan',
}
function statusLabel(status) {
  return STATUS_LABELS[status] ?? status
}

function statusBadge(status) {
  const map = {
    sedang_dikemas: 'bg-amber-50 text-amber-700',
    menunggu_pengirim: 'bg-blue-50 text-blue-700',
    sedang_dikirim: 'bg-indigo-50 text-indigo-700',
    pesanan_selesai: 'bg-emerald-50 text-emerald-700',
    dikembalikan: 'bg-red-50 text-red-700',
  }
  return map[status] ?? 'bg-ink-50 text-ink-600'
}

onMounted(loadAll)
</script>
