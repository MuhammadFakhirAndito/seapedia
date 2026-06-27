import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import GuestLayout from '@/layouts/GuestLayout.vue'
import Home from '@/pages/public/Home.vue'
import ProductList from '@/pages/public/ProductList.vue'
import ProductDetail from '@/pages/public/ProductDetail.vue'
import Login from '@/pages/public/Login.vue'
import Register from '@/pages/public/Register.vue'
import SelectRole from '@/pages/public/SelectRole.vue'
import DashboardPlaceholder from '@/pages/DashboardPlaceholder.vue'

const routes = [
  {
    path: '/',
    component: GuestLayout,
    children: [
      { path: '', name: 'home', component: Home },
      { path: 'products', name: 'products', component: ProductList },
      { path: 'products/:id', name: 'product-detail', component: ProductDetail },
      { path: 'login', name: 'login', component: Login, meta: { guestOnly: true } },
      { path: 'register', name: 'register', component: Register, meta: { guestOnly: true } },
      {
        path: 'select-role',
        name: 'select-role',
        component: SelectRole,
        meta: { requiresAuth: true },
      },

      // Placeholder dashboard per role — diisi lengkap di level berikutnya
      {
        path: 'buyer/dashboard',
        name: 'buyer-dashboard',
        component: DashboardPlaceholder,
        meta: { requiresAuth: true, role: 'buyer' },
        props: { title: 'Dashboard Pembeli', emoji: '🛒' },
      },
      {
        path: 'seller/dashboard',
        name: 'seller-dashboard',
        component: DashboardPlaceholder,
        meta: { requiresAuth: true, role: 'seller' },
        props: { title: 'Dashboard Penjual', emoji: '🏪' },
      },
      {
        path: 'driver/dashboard',
        name: 'driver-dashboard',
        component: DashboardPlaceholder,
        meta: { requiresAuth: true, role: 'driver' },
        props: { title: 'Dashboard Pengantar', emoji: '🛵' },
      },
      {
        path: 'admin/dashboard',
        name: 'admin-dashboard',
        component: DashboardPlaceholder,
        meta: { requiresAuth: true, role: 'admin' },
        props: { title: 'Dashboard Admin', emoji: '🛠️' },
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

/**
 * Route guard global.
 *
 * Catatan: ini cuma penjaga di sisi FRONTEND untuk pengalaman pengguna
 * (supaya tidak nyasar ke halaman yang salah). Otorisasi SEBENARNYA tetap
 * harus selalu dicek di backend lewat middleware CheckActiveRole — guard
 * di sini TIDAK BOLEH dianggap sebagai satu-satunya proteksi.
 */
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.guestOnly && auth.isLoggedIn) {
    return next('/')
  }

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return next('/login')
  }

  if (to.meta.role && auth.activeRole !== to.meta.role && !(to.meta.role === 'admin' && auth.isAdmin)) {
    return next('/')
  }

  next()
})

export default router
