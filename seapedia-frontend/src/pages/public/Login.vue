<template>
  <div class="flex items-center justify-center min-h-[calc(100vh-180px)] px-4 py-10">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-sm border border-ink-100 p-7">
      <h1 class="font-display font-bold text-2xl text-ink-900 text-center">Masuk</h1>
      <p class="text-sm text-ink-500 text-center mt-1">
        Selamat datang kembali di SEAPEDIA
      </p>

      <form @submit.prevent="handleSubmit" class="mt-6 space-y-4">
        <div>
          <label class="text-xs font-medium text-ink-600 mb-1 block">Username</label>
          <input
            v-model="form.username"
            type="text"
            required
            class="w-full h-11 rounded-lg border border-ink-200 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
            placeholder="contoh: buyer_demo"
          />
        </div>

        <div>
          <label class="text-xs font-medium text-ink-600 mb-1 block">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            class="w-full h-11 rounded-lg border border-ink-200 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
            placeholder="••••••••"
          />
        </div>

        <p v-if="errorMessage" class="text-sm text-red-600 bg-red-50 rounded-md px-3 py-2">
          {{ errorMessage }}
        </p>

        <BaseButton type="submit" class="w-full" size="lg" :loading="auth.loading">
          Masuk
        </BaseButton>
      </form>

      <p class="text-sm text-ink-500 text-center mt-5">
        Belum punya akun?
        <RouterLink to="/register" class="text-brand-600 font-semibold hover:underline">
          Daftar di sini
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

const form = reactive({ username: '', password: '' })
const errorMessage = ref('')

async function handleSubmit() {
  errorMessage.value = ''
  try {
    await auth.login(form.username, form.password)

    if (auth.hasMultipleRoles && !auth.activeRole) {
      router.push('/select-role')
    } else if (auth.activeRole === 'seller') {
      router.push('/seller/dashboard')
    } else if (auth.activeRole === 'driver') {
      router.push('/driver/dashboard')
    } else if (auth.isAdmin) {
      router.push('/admin/dashboard')
    } else {
      router.push('/buyer/dashboard')
    }
  } catch (e) {
    errorMessage.value =
      e.response?.data?.message || 'Username atau password salah.'
  }
}
</script>
