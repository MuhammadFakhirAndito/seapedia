<template>
  <div v-if="checkingSession" class="min-h-screen flex items-center justify-center bg-white">
    <p class="text-sm text-ink-400">Memuat SEAPEDIA...</p>
  </div>
  <RouterView v-else />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const checkingSession = ref(true)

onMounted(async () => {
  await auth.initSession()
  checkingSession.value = false
})
</script>