<template>
  <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="font-display font-bold text-2xl text-ink-900">Dashboard Pembeli</h1>
        <p class="text-sm text-ink-500 mt-1">
          Selamat datang kembali, <span class="font-semibold">{{ auth.user?.username }}</span>
        </p>
      </div>
      <RouterLink
        to="/products"
        class="text-sm font-semibold text-emerald-700 hover:text-emerald-800"
      >
        Belanja lagi →
      </RouterLink>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="bg-white rounded-2xl border border-ink-100 p-10 text-center">
      <p class="text-sm text-ink-500">Memuat data dashboard...</p>
    </div>

    <!-- Error state -->
    <div
      v-else-if="errorMessage"
      class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center"
    >
      <p class="text-sm text-red-700">{{ errorMessage }}</p>
      <button
        @click="loadAll"
        class="mt-3 text-sm font-semibold text-red-700 underline"
      >
        Coba lagi
      </button>
    </div>

    <template v-else>
      <!-- Summary cards: Wallet & Cart -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-ink-100 p-5">
          <p class="text-xs text-ink-500 mb-1">Saldo Wallet</p>
          <p class="font-display font-bold text-xl text-ink-900">
            {{ formatRupiah(wallet?.balance ?? 0) }}
          </p>
          <button
            @click="showTopup = true"
            class="mt-3 text-xs font-semibold text-emerald-700 hover:text-emerald-800"
          >
            + Top Up
          </button>
        </div>

        <div class="bg-white rounded-2xl border border-ink-100 p-5">
          <p class="text-xs text-ink-500 mb-1">Item di Keranjang</p>
          <p class="font-display font-bold text-xl text-ink-900">
            {{ cartItems.length }} produk
          </p>
          <RouterLink
            v-if="cartItems.length > 0"
            to="/products"
            class="mt-3 inline-block text-xs font-semibold text-emerald-700 hover:text-emerald-800"
          >
            Lanjut checkout →
          </RouterLink>
          <p v-else class="mt-3 text-xs text-ink-400">Keranjang masih kosong</p>
        </div>

        <div class="bg-white rounded-2xl border border-ink-100 p-5">
          <p class="text-xs text-ink-500 mb-1">Total Belanja</p>
          <p class="font-display font-bold text-xl text-ink-900">
            {{ formatRupiah(spending?.total_spent ?? 0) }}
          </p>
          <p class="mt-3 text-xs text-ink-400">
            {{ spending?.total_orders ?? 0 }} pesanan selesai
          </p>
        </div>
      </div>

      <!-- Top up modal sederhana -->
      <div
        v-if="showTopup"
        class="fixed inset-0 bg-black/30 flex items-center justify-center z-50 px-4"
        @click.self="showTopup = false"
      >
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm">
          <h3 class="font-display font-bold text-lg text-ink-900 mb-3">Top Up Wallet</h3>
          <input
            v-model.number="topupAmount"
            type="number"
            placeholder="Jumlah top up"
            class="w-full border border-ink-200 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          />
          <div class="flex gap-2">
            <button
              @click="showTopup = false"
              class="flex-1 border border-ink-200 rounded-lg py-2 text-sm font-semibold text-ink-600"
            >
              Batal
            </button>
            <button
              @click="handleTopup"
              :disabled="topupLoading || !topupAmount"
              class="flex-1 bg-emerald-700 text-white rounded-lg py-2 text-sm font-semibold disabled:opacity-50"
            >
              {{ topupLoading ? 'Memproses...' : 'Top Up' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Alamat Pengiriman -->
      <div class="bg-white rounded-2xl border border-ink-100 p-5">
        <h2 class="font-display font-bold text-base text-ink-900 mb-3">Alamat Pengiriman</h2>
        <div v-if="addresses.length === 0" class="text-sm text-ink-400">
          Belum ada alamat tersimpan.
        </div>
        <div v-else class="space-y-2">
          <div
            v-for="addr in addresses"
            :key="addr.id"
            class="flex items-start justify-between border border-ink-100 rounded-lg p-3"
          >
            <div>
              <p class="text-sm font-semibold text-ink-900">
                {{ addr.recipient_name }}
                <span
                  v-if="addr.is_default"
                  class="ml-2 text-[10px] bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full font-semibold"
                >
                  Utama
                </span>
              </p>
              <p class="text-xs text-ink-500 mt-1">{{ addr.full_address }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat Pesanan -->
      <div class="bg-white rounded-2xl border border-ink-100 p-5">
        <h2 class="font-display font-bold text-base text-ink-900 mb-3">Riwayat Pesanan</h2>

        <div v-if="orders.length === 0" class="text-sm text-ink-400 py-4 text-center">
          Belum ada pesanan. Yuk mulai belanja!
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
                <p class="text-xs text-ink-500 mt-0.5">{{ order.store?.name }}</p>
              </div>
              <span
                class="text-[11px] font-semibold px-2.5 py-1 rounded-full"
                :class="statusBadge(order.status)"
              >
                {{ statusLabel(order.status) }}
              </span>
            </div>

            <div class="flex items-center justify-between mt-3 pt-3 border-t border-ink-50">
              <p class="text-xs text-ink-500">
                {{ order.items?.length ?? 0 }} produk · {{ formatDate(order.created_at) }}
              </p>
              <p class="text-sm font-bold text-ink-900">
                {{ formatRupiah(order.total_amount) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const auth = useAuthStore()

const loading = ref(true)
const errorMessage = ref('')

const wallet = ref(null)
const addresses = ref([])
const cartItems = ref([])
const orders = ref([])
const spending = ref(null)

const showTopup = ref(false)
const topupAmount = ref(null)
const topupLoading = ref(false)

async function loadAll() {
  loading.value = true
  errorMessage.value = ''

  try {
    const [walletRes, addressRes, cartRes, orderRes, spendingRes] = await Promise.all([
      api.get('/buyer/wallet'),
      api.get('/buyer/addresses'),
      api.get('/buyer/cart-items'),
      api.get('/buyer/orders'),
      api.get('/buyer/reports/spending'),
    ])

    wallet.value = walletRes.data
    addresses.value = addressRes.data?.data ?? addressRes.data ?? []
    cartItems.value = cartRes.data?.items ?? cartRes.data ?? []
    orders.value = orderRes.data?.data ?? orderRes.data ?? []
    spending.value = spendingRes.data
  } catch (err) {
    errorMessage.value =
      err.response?.data?.message ?? 'Gagal memuat dashboard. Pastikan kamu login sebagai Buyer.'
  } finally {
    loading.value = false
  }
}

async function handleTopup() {
  if (!topupAmount.value || topupAmount.value <= 0) return
  topupLoading.value = true
  try {
    await api.post('/buyer/wallet/topup', { amount: topupAmount.value })
    const { data } = await api.get('/buyer/wallet')
    wallet.value = data
    showTopup.value = false
    topupAmount.value = null
  } catch (err) {
    alert(err.response?.data?.message ?? 'Top up gagal.')
  } finally {
    topupLoading.value = false
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
