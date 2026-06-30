<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="font-display font-bold text-2xl text-ink-900 mb-1">Dashboard Admin</h1>
    <p class="text-sm text-ink-500 mb-6">Monitoring marketplace dan operasional</p>

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

    <!-- TAB: Monitoring -->
    <div v-if="activeTab === 'monitoring'" class="space-y-6">
      <div v-if="loading" class="bg-white rounded-2xl border border-ink-100 p-10 text-center">
        <p class="text-sm text-ink-500">Memuat data monitoring...</p>
      </div>

      <template v-else-if="summary">
        <!-- Users & Stores -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
          <div class="bg-white rounded-2xl border border-ink-100 p-4">
            <p class="text-xs text-ink-500 mb-1">Total User</p>
            <p class="font-display font-bold text-lg text-ink-900">{{ summary.users?.total ?? 0 }}</p>
          </div>
          <div class="bg-white rounded-2xl border border-ink-100 p-4">
            <p class="text-xs text-ink-500 mb-1">Buyer</p>
            <p class="font-display font-bold text-lg text-ink-900">{{ summary.users?.buyers ?? 0 }}</p>
          </div>
          <div class="bg-white rounded-2xl border border-ink-100 p-4">
            <p class="text-xs text-ink-500 mb-1">Seller</p>
            <p class="font-display font-bold text-lg text-ink-900">{{ summary.users?.sellers ?? 0 }}</p>
          </div>
          <div class="bg-white rounded-2xl border border-ink-100 p-4">
            <p class="text-xs text-ink-500 mb-1">Driver</p>
            <p class="font-display font-bold text-lg text-ink-900">{{ summary.users?.drivers ?? 0 }}</p>
          </div>
          <div class="bg-white rounded-2xl border border-ink-100 p-4">
            <p class="text-xs text-ink-500 mb-1">Toko</p>
            <p class="font-display font-bold text-lg text-ink-900">{{ summary.stores?.total ?? 0 }}</p>
          </div>
        </div>

        <!-- Orders breakdown -->
        <div class="bg-white rounded-2xl border border-ink-100 p-5">
          <h2 class="text-sm font-semibold text-ink-900 mb-3">Status Pesanan</h2>
          <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div
              v-for="(count, status) in orderStatusEntries"
              :key="status"
              class="rounded-xl p-3 text-center"
              :class="statusBadge(status)"
            >
              <p class="text-lg font-bold">{{ count }}</p>
              <p class="text-[11px] mt-0.5">{{ statusLabel(status) }}</p>
            </div>
          </div>
          <p class="text-xs text-ink-500 mt-4">
            Total revenue (pesanan selesai):
            <span class="font-bold text-ink-900">{{ formatRupiah(summary.orders?.total_revenue) }}</span>
          </p>
        </div>

        <!-- Overdue alert -->
        <div
          v-if="summary.overdue_orders?.count > 0"
          class="bg-red-50 border border-red-200 rounded-2xl p-5"
        >
          <p class="text-sm font-semibold text-red-700 mb-1">
            ⚠ {{ summary.overdue_orders.count }} pesanan overdue
          </p>
          <p class="text-xs text-red-600">
            Pesanan ini sudah melewati SLA pengiriman dan menunggu diproses sebagai
            auto-refund. Gunakan tab "Overdue" untuk menjalankan handling.
          </p>
        </div>

        <!-- Vouchers & Promos & Deliveries -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="bg-white rounded-2xl border border-ink-100 p-5">
            <p class="text-xs text-ink-500 mb-1">Voucher</p>
            <p class="font-display font-bold text-lg text-ink-900">
              {{ summary.vouchers?.total ?? 0 }} total
            </p>
            <p class="text-xs text-ink-400 mt-1">{{ summary.vouchers?.active ?? 0 }} aktif</p>
          </div>
          <div class="bg-white rounded-2xl border border-ink-100 p-5">
            <p class="text-xs text-ink-500 mb-1">Promo</p>
            <p class="font-display font-bold text-lg text-ink-900">
              {{ summary.promos?.total ?? 0 }} total
            </p>
            <p class="text-xs text-ink-400 mt-1">{{ summary.promos?.active ?? 0 }} aktif</p>
          </div>
          <div class="bg-white rounded-2xl border border-ink-100 p-5">
            <p class="text-xs text-ink-500 mb-1">Delivery Job</p>
            <p class="font-display font-bold text-lg text-ink-900">
              {{ summary.delivery_jobs?.total ?? 0 }} total
            </p>
            <p class="text-xs text-ink-400 mt-1">
              {{ summary.delivery_jobs?.available ?? 0 }} tersedia ·
              {{ summary.delivery_jobs?.completed ?? 0 }} selesai
            </p>
          </div>
        </div>
      </template>
    </div>

    <!-- TAB: Voucher & Promo -->
    <div v-else-if="activeTab === 'discounts'" class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Generate voucher -->
        <div class="bg-white rounded-2xl border border-ink-100 p-5">
          <h2 class="text-sm font-semibold text-ink-900 mb-3">Generate Voucher</h2>
          <div class="space-y-2">
            <input
              v-model="voucherForm.code"
              type="text"
              placeholder="Kode voucher"
              class="w-full border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
            <input
              v-model.number="voucherForm.discount_value"
              type="number"
              placeholder="Nilai diskon (Rp)"
              class="w-full border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
            <input
              v-model.number="voucherForm.usage_limit"
              type="number"
              placeholder="Limit penggunaan"
              class="w-full border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
            <input
              v-model="voucherForm.expires_at"
              type="date"
              class="w-full border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
          </div>
          <p v-if="voucherError" class="text-xs text-red-600 mt-2">{{ voucherError }}</p>
          <button
            @click="handleCreateVoucher"
            :disabled="voucherSaving"
            class="w-full mt-3 bg-emerald-700 text-white text-sm font-semibold py-2 rounded-lg hover:bg-emerald-800 disabled:opacity-50"
          >
            {{ voucherSaving ? 'Menyimpan...' : 'Buat Voucher' }}
          </button>
        </div>

        <!-- Generate promo -->
        <div class="bg-white rounded-2xl border border-ink-100 p-5">
          <h2 class="text-sm font-semibold text-ink-900 mb-3">Generate Promo</h2>
          <div class="space-y-2">
            <input
              v-model="promoForm.name"
              type="text"
              placeholder="Nama promo"
              class="w-full border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
            <input
              v-model.number="promoForm.discount_value"
              type="number"
              placeholder="Nilai diskon (Rp)"
              class="w-full border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
            <input
              v-model="promoForm.expires_at"
              type="date"
              class="w-full border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
          </div>
          <p v-if="promoError" class="text-xs text-red-600 mt-2">{{ promoError }}</p>
          <button
            @click="handleCreatePromo"
            :disabled="promoSaving"
            class="w-full mt-3 bg-emerald-700 text-white text-sm font-semibold py-2 rounded-lg hover:bg-emerald-800 disabled:opacity-50"
          >
            {{ promoSaving ? 'Menyimpan...' : 'Buat Promo' }}
          </button>
        </div>
      </div>

      <!-- List voucher -->
      <div class="bg-white rounded-2xl border border-ink-100 p-5">
        <h2 class="text-sm font-semibold text-ink-900 mb-3">Daftar Voucher</h2>
        <div v-if="vouchers.length === 0" class="text-sm text-ink-400 py-3 text-center">
          Belum ada voucher.
        </div>
        <div v-else class="divide-y divide-ink-50">
          <div v-for="v in vouchers" :key="v.id" class="flex items-center justify-between py-2.5">
            <div>
              <p class="text-sm font-semibold text-ink-900">{{ v.code }}</p>
              <p class="text-xs text-ink-500">
                {{ formatRupiah(v.discount_value) }} · sisa {{ v.usage_limit - (v.usage_count ?? 0) }}/{{ v.usage_limit }}
              </p>
            </div>
            <span
              class="text-[11px] font-semibold px-2 py-1 rounded-full"
              :class="isExpired(v.expires_at) ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'"
            >
              {{ isExpired(v.expires_at) ? 'Expired' : 'Aktif' }}
            </span>
          </div>
        </div>
      </div>

      <!-- List promo -->
      <div class="bg-white rounded-2xl border border-ink-100 p-5">
        <h2 class="text-sm font-semibold text-ink-900 mb-3">Daftar Promo</h2>
        <div v-if="promos.length === 0" class="text-sm text-ink-400 py-3 text-center">
          Belum ada promo.
        </div>
        <div v-else class="divide-y divide-ink-50">
          <div v-for="p in promos" :key="p.id" class="flex items-center justify-between py-2.5">
            <div>
              <p class="text-sm font-semibold text-ink-900">{{ p.name }}</p>
              <p class="text-xs text-ink-500">{{ formatRupiah(p.discount_value) }}</p>
            </div>
            <span
              class="text-[11px] font-semibold px-2 py-1 rounded-full"
              :class="isExpired(p.expires_at) ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'"
            >
              {{ isExpired(p.expires_at) ? 'Expired' : 'Aktif' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB: Overdue -->
    <div v-else-if="activeTab === 'overdue'" class="space-y-4">
      <div class="bg-white rounded-2xl border border-ink-100 p-5">
        <h2 class="text-sm font-semibold text-ink-900 mb-2">Simulasi & Trigger Overdue</h2>
        <p class="text-xs text-ink-500 mb-4">
          Majukan waktu sistem untuk mendemokan auto-refund/return pada order yang
          melewati SLA pengiriman (Instant 24 jam, Next Day 48 jam, Regular 72 jam).
        </p>

        <div class="flex items-center gap-3">
          <input
            v-model.number="simulateDays"
            type="number"
            min="1"
            max="30"
            class="w-24 border border-ink-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
          />
          <span class="text-sm text-ink-500">hari ke depan</span>
          <button
            @click="handleSimulate"
            :disabled="simulating"
            class="bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-amber-700 disabled:opacity-50"
          >
            {{ simulating ? 'Memproses...' : 'Jalankan Simulasi' }}
          </button>
        </div>

        <div v-if="simulateResult" class="mt-4 bg-ink-50 rounded-lg p-4">
          <p class="text-xs font-semibold text-ink-900 mb-2">{{ simulateResult.message }}</p>
          <pre class="text-[11px] text-ink-600 whitespace-pre-wrap font-mono">{{ simulateResult.output }}</pre>
        </div>
      </div>

      <!-- List overdue orders -->
      <div class="bg-white rounded-2xl border border-ink-100 p-5">
        <h2 class="text-sm font-semibold text-ink-900 mb-3">
          Pesanan Overdue Saat Ini ({{ overdueOrders.length }})
        </h2>
        <div v-if="overdueOrders.length === 0" class="text-sm text-ink-400 py-3 text-center">
          Tidak ada pesanan overdue.
        </div>
        <div v-else class="divide-y divide-ink-50">
          <div
            v-for="order in overdueOrders"
            :key="order.id"
            class="flex items-center justify-between py-2.5"
          >
            <div>
              <p class="text-sm font-semibold text-ink-900">{{ order.order_number }}</p>
              <p class="text-xs text-ink-500">
                {{ order.delivery_method }} · {{ formatRupiah(order.total_amount) }}
              </p>
            </div>
            <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-amber-50 text-amber-700">
              {{ statusLabel(order.status) }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'

const activeTab = ref('monitoring')
const tabs = [
  { value: 'monitoring', label: 'Monitoring' },
  { value: 'discounts', label: 'Voucher & Promo' },
  { value: 'overdue', label: 'Overdue' },
]

const loading = ref(true)
const summary = ref(null)
const vouchers = ref([])
const promos = ref([])
const overdueOrders = ref([])

const voucherForm = ref({ code: '', discount_value: null, usage_limit: 10, expires_at: '' })
const voucherSaving = ref(false)
const voucherError = ref('')

const promoForm = ref({ name: '', discount_value: null, expires_at: '' })
const promoSaving = ref(false)
const promoError = ref('')

const simulateDays = ref(4)
const simulating = ref(false)
const simulateResult = ref(null)

const orderStatusEntries = computed(() => {
  if (!summary.value?.orders) return {}
  const { total, total_revenue, ...statuses } = summary.value.orders
  return statuses
})

async function loadAll() {
  loading.value = true
  try {
    const [summaryRes, voucherRes, promoRes, overdueRes] = await Promise.allSettled([
      api.get('/admin/monitoring'),
      api.get('/admin/vouchers'),
      api.get('/admin/promos'),
      api.get('/admin/monitoring/overdue'),
    ])

    if (summaryRes.status === 'fulfilled') summary.value = summaryRes.value.data
    if (voucherRes.status === 'fulfilled') {
      vouchers.value = voucherRes.value.data?.data ?? voucherRes.value.data ?? []
    }
    if (promoRes.status === 'fulfilled') {
      promos.value = promoRes.value.data?.data ?? promoRes.value.data ?? []
    }
    if (overdueRes.status === 'fulfilled') {
      overdueOrders.value = overdueRes.value.data?.orders ?? []
    }
  } finally {
    loading.value = false
  }
}

async function handleCreateVoucher() {
  voucherError.value = ''
  voucherSaving.value = true
  try {
    await api.post('/admin/vouchers', {
      code: voucherForm.value.code,
      discount_type: 'fixed',
      discount_value: voucherForm.value.discount_value,
      usage_limit: voucherForm.value.usage_limit,
      expires_at: voucherForm.value.expires_at,
    })
    voucherForm.value = { code: '', discount_value: null, usage_limit: 10, expires_at: '' }
    const { data } = await api.get('/admin/vouchers')
    vouchers.value = data?.data ?? data ?? []
  } catch (err) {
    voucherError.value = err.response?.data?.message ?? 'Gagal membuat voucher.'
  } finally {
    voucherSaving.value = false
  }
}

async function handleCreatePromo() {
  promoError.value = ''
  promoSaving.value = true
  try {
    await api.post('/admin/promos', promoForm.value)
    promoForm.value = { name: '', discount_value: null, expires_at: '' }
    const { data } = await api.get('/admin/promos')
    promos.value = data?.data ?? data ?? []
  } catch (err) {
    promoError.value = err.response?.data?.message ?? 'Gagal membuat promo.'
  } finally {
    promoSaving.value = false
  }
}

async function handleSimulate() {
  simulating.value = true
  simulateResult.value = null
  try {
    const { data } = await api.post('/admin/simulate-next-day', { days: simulateDays.value })
    simulateResult.value = data
    await loadAll()
  } catch (err) {
    simulateResult.value = {
      message: err.response?.data?.message ?? 'Gagal menjalankan simulasi.',
      output: '',
    }
  } finally {
    simulating.value = false
  }
}

function formatRupiah(value) {
  return 'Rp' + Number(value ?? 0).toLocaleString('id-ID')
}

function isExpired(dateStr) {
  if (!dateStr) return false
  return new Date(dateStr) < new Date()
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
