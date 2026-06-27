<template>
  <div class="flex items-center justify-center min-h-[calc(100vh-180px)] px-4 py-10">
    <div class="w-full max-w-md text-center">
      <h1 class="font-display font-bold text-2xl text-ink-900">Pilih Role Aktif</h1>
      <p class="text-sm text-ink-500 mt-1.5">
        Akun Anda memiliki lebih dari satu role. Pilih salah satu untuk
        melanjutkan ke dashboard yang sesuai.
      </p>

      <div class="grid grid-cols-1 gap-3 mt-7">
        <button
          v-for="role in roleOptions"
          :key="role.value"
          @click="handleSelect(role.value)"
          :disabled="submitting"
          class="flex items-center gap-4 p-4 bg-white rounded-xl border-2 border-ink-100 hover:border-brand-400 hover:bg-brand-50 transition-colors text-left disabled:opacity-50"
        >
          <span class="text-3xl">{{ role.emoji }}</span>
          <span>
            <span class="block font-semibold text-ink-900">{{ role.label }}</span>
            <span class="block text-xs text-ink-500">{{ role.description }}</span>
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()
const submitting = ref(false)

const allRoles = {
  buyer: { value: 'buyer', label: 'Pembeli', emoji: '🛒', description: 'Belanja produk dari berbagai toko', redirect: '/buyer/dashboard' },
  seller: { value: 'seller', label: 'Penjual', emoji: '🏪', description: 'Kelola toko dan produk Anda', redirect: '/seller/dashboard' },
  driver: { value: 'driver', label: 'Pengantar', emoji: '🛵', description: 'Cari dan ambil pekerjaan pengantaran', redirect: '/driver/dashboard' },
}

const roleOptions = computed(() =>
  auth.roleNames.filter((r) => r !== 'admin').map((r) => allRoles[r])
)

async function handleSelect(role) {
  submitting.value = true
  try {
    await auth.selectRole(role)
    router.push(allRoles[role].redirect)
  } finally {
    submitting.value = false
  }
}
</script>
