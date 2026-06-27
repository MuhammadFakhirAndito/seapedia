<template>
  <div class="flex items-center justify-center min-h-[calc(100vh-180px)] px-4 py-10">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-sm border border-ink-100 p-7">
      <h1 class="font-display font-bold text-2xl text-ink-900 text-center">Daftar Akun</h1>
      <p class="text-sm text-ink-500 text-center mt-1">
        Mulai belanja dan berjualan di SEAPEDIA
      </p>

      <form @submit.prevent="handleSubmit" class="mt-6 space-y-4">
        <div>
          <label class="text-xs font-medium text-ink-600 mb-1 block">Username</label>
          <input
            v-model="form.username"
            type="text"
            required
            class="w-full h-11 rounded-lg border border-ink-200 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
          />
        </div>

        <div>
          <label class="text-xs font-medium text-ink-600 mb-1 block">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full h-11 rounded-lg border border-ink-200 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
          />
        </div>

        <div>
          <label class="text-xs font-medium text-ink-600 mb-1 block">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            minlength="8"
            class="w-full h-11 rounded-lg border border-ink-200 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
          />
        </div>

        <div>
          <label class="text-xs font-medium text-ink-600 mb-2 block">Daftar sebagai</label>
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="role in ['buyer', 'seller', 'driver']"
              :key="role"
              type="button"
              @click="toggleRole(role)"
              class="text-xs font-semibold py-2 rounded-lg border-2 transition-colors capitalize"
              :class="
                form.roles.includes(role)
                  ? 'border-brand-500 bg-brand-50 text-brand-700'
                  : 'border-ink-150 text-ink-500'
              "
            >
              {{ role === 'buyer' ? 'Pembeli' : role === 'seller' ? 'Penjual' : 'Pengantar' }}
            </button>
          </div>
          <p class="text-[11px] text-ink-400 mt-1.5">
            Bisa pilih lebih dari satu. Anda akan diminta memilih role aktif setelah login.
          </p>
        </div>

        <p v-if="errorMessage" class="text-sm text-red-600 bg-red-50 rounded-md px-3 py-2">
          {{ errorMessage }}
        </p>

        <BaseButton type="submit" class="w-full" size="lg" :loading="auth.loading">
          Daftar
        </BaseButton>
      </form>

      <p class="text-sm text-ink-500 text-center mt-5">
        Sudah punya akun?
        <RouterLink to="/login" class="text-brand-600 font-semibold hover:underline">
          Masuk
        </RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseButton from '@/components/common/BaseButton.vue'

const router = useRouter()
const auth = useAuthStore()

const form = reactive({
  username: '',
  email: '',
  password: '',
  roles: ['buyer'],
})
const errorMessage = ref('')

function toggleRole(role) {
  if (form.roles.includes(role)) {
    form.roles = form.roles.filter((r) => r !== role)
  } else {
    form.roles.push(role)
  }
}

async function handleSubmit() {
  errorMessage.value = ''
  if (form.roles.length === 0) {
    errorMessage.value = 'Pilih minimal satu role.'
    return
  }
  try {
    await auth.register(form)
    if (auth.hasMultipleRoles) {
      router.push('/select-role')
    } else {
      router.push('/buyer/dashboard')
    }
  } catch (e) {
    errorMessage.value =
      e.response?.data?.message || 'Gagal mendaftar. Periksa kembali data Anda.'
  }
}
</script>
