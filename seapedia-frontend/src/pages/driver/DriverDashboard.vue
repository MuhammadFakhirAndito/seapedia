<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="font-display font-bold text-2xl text-ink-900 mb-1">Dashboard Pengantar</h1>
    <p class="text-sm text-ink-500 mb-6">Cari, ambil, dan selesaikan job pengantaran</p>

    <!-- Summary cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-2xl border border-ink-100 p-5">
        <p class="text-xs text-ink-500 mb-1">Total Penghasilan</p>
        <p class="font-display font-bold text-xl text-ink-900">
          {{ formatRupiah(history?.total_earning ?? 0) }}
        </p>
      </div>
      <div class="bg-white rounded-2xl border border-ink-100 p-5">
        <p class="text-xs text-ink-500 mb-1">Job Aktif</p>
        <p class="font-display font-bold text-xl text-ink-900">
          {{ history?.active_job ? 1 : 0 }}
        </p>
      </div>
      <div class="bg-white rounded-2xl border border-ink-100 p-5">
        <p class="text-xs text-ink-500 mb-1">Job Selesai</p>
        <p class="font-display font-bold text-xl text-ink-900">{{ history?.total_jobs ?? 0 }}</p>
      </div>
    </div>

    <!-- Job aktif (kalau ada) -->
    <div
      v-if="history?.active_job"
      class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 mb-6"
    >
      <p class="text-xs font-semibold text-emerald-700 mb-2">Sedang Diantar</p>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-sm font-semibold text-ink-900">
            {{ history.active_job.order?.order_number }}
          </p>
          <p class="text-xs text-ink-500 mt-0.5">
            {{ history.active_job.order?.store?.name }}
          </p>
        </div>
        <button
          @click="handleComplete(history.active_job.id)"
          :disabled="completingId === history.active_job.id"
          class="bg-emerald-700 text-white text-xs font-semibold px-4 py-2 rounded-lg hover:bg-emerald-800 disabled:opacity-50"
        >
          {{ completingId === history.active_job.id ? 'Memproses...' : 'Konfirmasi Selesai' }}
        </button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 border-b border-ink-100 mb-5">
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

    <!-- TAB: Job tersedia -->
    <div v-if="activeTab === 'available'">
      <div v-if="loading" class="bg-white rounded-2xl border border-ink-100 p-10 text-center">
        <p class="text-sm text-ink-500">Memuat job...</p>
      </div>

      <div
        v-else-if="availableJobs.length === 0"
        class="bg-white rounded-2xl border border-ink-100 p-10 text-center"
      >
        <p class="text-4xl mb-3">📦</p>
        <p class="text-sm font-semibold text-ink-900">Belum ada job tersedia</p>
        <p class="text-xs text-ink-500 mt-1">Job baru muncul setelah Seller memproses pesanan</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="job in availableJobs"
          :key="job.id"
          class="bg-white rounded-2xl border border-ink-100 p-5"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-semibold text-ink-900">{{ job.order?.order_number }}</p>
              <p class="text-xs text-ink-500 mt-0.5">{{ job.order?.store?.name }}</p>
              <p class="text-xs text-ink-400 mt-1">
                {{ job.order?.items?.length ?? 0 }} produk · {{ job.order?.buyer?.username }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-xs text-ink-500 mb-1">Estimasi earning</p>
              <p class="text-sm font-bold text-emerald-700">
                {{ formatRupiah(job.earning_amount) }}
              </p>
            </div>
          </div>

          <button
            @click="handleTake(job.id)"
            :disabled="takingId === job.id || !!history?.active_job"
            class="w-full mt-4 bg-emerald-700 text-white text-sm font-semibold py-2 rounded-lg hover:bg-emerald-800 disabled:opacity-40 disabled:cursor-not-allowed"
          >
            {{
              history?.active_job
                ? 'Selesaikan job aktif dulu'
                : takingId === job.id
                ? 'Mengambil job...'
                : 'Ambil Job'
            }}
          </button>
        </div>
      </div>
    </div>

    <!-- TAB: Riwayat -->
    <div v-else-if="activeTab === 'history'" class="bg-white rounded-2xl border border-ink-100 p-5">
      <div v-if="!history?.completed_jobs?.length" class="text-sm text-ink-400 py-4 text-center">
        Belum ada job yang diselesaikan.
      </div>
      <div v-else class="divide-y divide-ink-50">
        <div
          v-for="job in history.completed_jobs"
          :key="job.id"
          class="flex items-center justify-between py-3"
        >
          <div>
            <p class="text-sm font-semibold text-ink-900">{{ job.order?.order_number }}</p>
            <p class="text-xs text-ink-500 mt-0.5">
              {{ job.order?.store?.name }} · {{ formatDate(job.completed_at) }}
            </p>
          </div>
          <p class="text-sm font-bold text-emerald-700">{{ formatRupiah(job.earning_amount) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'

const activeTab = ref('available')
const tabs = [
  { value: 'available', label: 'Job Tersedia' },
  { value: 'history', label: 'Riwayat' },
]

const loading = ref(true)
const availableJobs = ref([])
const history = ref(null)

const takingId = ref(null)
const completingId = ref(null)

async function loadAll() {
  loading.value = true
  try {
    const [jobsRes, historyRes] = await Promise.all([
      api.get('/driver/jobs'),
      api.get('/driver/history'),
    ])
    availableJobs.value = jobsRes.data ?? []
    history.value = historyRes.data
  } finally {
    loading.value = false
  }
}

async function handleTake(deliveryId) {
  takingId.value = deliveryId
  try {
    await api.post(`/driver/jobs/${deliveryId}/take`)
    await loadAll()
    activeTab.value = 'available'
  } catch (err) {
    alert(err.response?.data?.message ?? 'Gagal mengambil job. Mungkin sudah diambil driver lain.')
    await loadAll()
  } finally {
    takingId.value = null
  }
}

async function handleComplete(deliveryId) {
  completingId.value = deliveryId
  try {
    await api.post(`/driver/jobs/${deliveryId}/complete`)
    await loadAll()
  } catch (err) {
    alert(err.response?.data?.message ?? 'Gagal menyelesaikan job.')
  } finally {
    completingId.value = null
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

onMounted(loadAll)
</script>
