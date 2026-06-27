import { defineStore } from 'pinia'
import api from '@/api/axios'

/**
 * useAuthStore
 *
 * Menyimpan state autentikasi: data user, daftar role yang dimiliki,
 * dan active_role yang sedang dipakai. Dipakai di seluruh app untuk
 * menentukan navigasi mana yang ditampilkan dan dashboard mana yang
 * bisa diakses (sesuai business rule: otorisasi berdasarkan active_role).
 */
export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null, // { id, username, email, active_role, roles: [...] }
    token: localStorage.getItem('seapedia_token') || null,
    loading: false,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token && !!state.user,
    roleNames: (state) => state.user?.roles?.map((r) => r.name) ?? [],
    hasMultipleRoles: (state) => (state.user?.roles?.length ?? 0) > 1,
    activeRole: (state) => state.user?.active_role ?? null,
    isAdmin: (state) => state.user?.roles?.some((r) => r.name === 'admin') ?? false,
  },

  actions: {
    async login(username, password) {
      this.loading = true
      try {
        const { data } = await api.post('/login', { username, password })
        this.token = data.token
        this.user = data.user
        localStorage.setItem('seapedia_token', data.token)
        return data
      } finally {
        this.loading = false
      }
    },

    async register(payload) {
      this.loading = true
      try {
        const { data } = await api.post('/register', payload)
        this.token = data.token
        this.user = data.user
        localStorage.setItem('seapedia_token', data.token)
        return data
      } finally {
        this.loading = false
      }
    },

    async fetchProfile() {
      const { data } = await api.get('/me')
      this.user = data
      return data
    },

    async selectRole(role) {
      const { data } = await api.post('/select-role', { role })
      this.user = data.user
      return data
    },

    async logout() {
      try {
        await api.post('/logout')
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('seapedia_token')
      }
    },
  },
})
