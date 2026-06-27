<template>
  <header class="sticky top-0 z-50 bg-brand-600">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex items-center gap-3 md:gap-6 h-16">
        <!-- Logo -->
        <RouterLink to="/" class="flex items-center gap-1.5 shrink-0">
          <span class="font-display font-extrabold text-lg md:text-xl text-white tracking-tight">
            SEAPEDIA
          </span>
        </RouterLink>

        <!-- Search bar (desktop: inline; mobile: pindah ke baris terpisah di bawah) -->
        <div class="hidden md:block flex-1 max-w-2xl">
          <form @submit.prevent="handleSearch" class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari produk, brand, atau toko"
              class="w-full h-10 rounded-md pl-4 pr-11 text-sm text-ink-800 placeholder-ink-400 focus:outline-none focus:ring-2 focus:ring-accent-400"
            />
            <button
              type="submit"
              class="absolute right-0 top-0 h-10 w-11 flex items-center justify-center text-brand-600"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"
                />
              </svg>
            </button>
          </form>
        </div>

        <!-- Spacer supaya nav kanan tetap menempel ke kanan saat search bar disembunyikan -->
        <div class="flex-1 md:hidden" />

        <!-- Right nav -->
        <nav class="flex items-center gap-5 shrink-0 text-sm">
          <template v-if="!auth.isLoggedIn">
            <RouterLink to="/login" class="text-white/90 hover:text-white font-medium">
              Masuk
            </RouterLink>
            <RouterLink
              to="/register"
              class="bg-white text-brand-700 font-semibold px-4 py-1.5 rounded-md hover:bg-brand-50 transition-colors"
            >
              Daftar
            </RouterLink>
          </template>

          <template v-else>
            <RouterLink
              v-if="auth.activeRole === 'buyer'"
              to="/buyer/cart"
              class="relative text-white/90 hover:text-white"
              title="Keranjang"
            >
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m-9 4a1 1 0 102 0 1 1 0 00-2 0zm8 0a1 1 0 102 0 1 1 0 00-2 0z"
                />
              </svg>
              <span
                v-if="cart.totalItems > 0"
                class="absolute -top-1.5 -right-1.5 bg-accent-500 text-white text-[10px] font-bold rounded-full w-4.5 h-4.5 flex items-center justify-center min-w-[18px] px-0.5"
              >
                {{ cart.totalItems }}
              </span>
            </RouterLink>

            <div class="relative">
              <button
                @click="menuOpen = !menuOpen"
                class="flex items-center gap-2 text-white/90 hover:text-white"
              >
                <span
                  class="w-7 h-7 rounded-full bg-brand-500 flex items-center justify-center text-xs font-bold uppercase"
                >
                  {{ auth.user?.username?.charAt(0) }}
                </span>
                <span class="font-medium">{{ auth.user?.username }}</span>
                <span
                  v-if="auth.activeRole"
                  class="text-[10px] uppercase bg-white/15 px-1.5 py-0.5 rounded"
                >
                  {{ auth.activeRole }}
                </span>
              </button>

              <div
                v-if="menuOpen"
                @click="menuOpen = false"
                class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-ink-100 py-1.5 text-ink-700"
              >
                <RouterLink
                  v-if="auth.hasMultipleRoles"
                  to="/select-role"
                  class="block px-4 py-2 text-sm hover:bg-ink-50"
                >
                  Ganti Role Aktif
                </RouterLink>
                <RouterLink
                  :to="dashboardLink"
                  class="block px-4 py-2 text-sm hover:bg-ink-50"
                >
                  Dashboard Saya
                </RouterLink>
                <button
                  @click="handleLogout"
                  class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                >
                  Keluar
                </button>
              </div>
            </div>
          </template>
        </nav>
      </div>

      <!-- Search bar khusus mobile -->
      <form @submit.prevent="handleSearch" class="relative pb-2.5 md:hidden">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari produk, brand, atau toko"
          class="w-full h-9 rounded-md pl-3.5 pr-10 text-sm text-ink-800 placeholder-ink-400 focus:outline-none focus:ring-2 focus:ring-accent-400"
        />
        <button
          type="submit"
          class="absolute right-0 top-0 h-9 w-10 flex items-center justify-center text-brand-600"
        >
          <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"
            />
          </svg>
        </button>
      </form>

      <!-- Category bar -->
      <div class="flex items-center gap-6 h-10 text-xs text-white/85 overflow-x-auto">
        <RouterLink to="/products" class="hover:text-white whitespace-nowrap">Semua Produk</RouterLink>
        <span
          v-for="cat in categories"
          :key="cat"
          class="hover:text-white whitespace-nowrap cursor-pointer"
        >
          {{ cat }}
        </span>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'

const router = useRouter()
const auth = useAuthStore()
const cart = useCartStore()

const searchQuery = ref('')
const menuOpen = ref(false)

const categories = [
  'Elektronik',
  'Fashion',
  'Kesehatan',
  'Rumah Tangga',
  'Hobi & Koleksi',
  'Olahraga',
]

const dashboardLink = computed(() => {
  switch (auth.activeRole) {
    case 'seller':
      return '/seller/dashboard'
    case 'driver':
      return '/driver/dashboard'
    default:
      return auth.isAdmin ? '/admin/dashboard' : '/buyer/dashboard'
  }
})

function handleSearch() {
  if (!searchQuery.value.trim()) return
  router.push({ path: '/products', query: { q: searchQuery.value } })
}

async function handleLogout() {
  await auth.logout()
  router.push('/')
}
</script>
