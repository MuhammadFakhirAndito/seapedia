import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import GuestLayout from '@/layouts/GuestLayout.vue'
import Home from '@/pages/public/Home.vue'
import ProductList from '@/pages/public/ProductList.vue'
import ProductDetail from '@/pages/public/ProductDetail.vue'
import StoreProfile from '@/pages/public/StoreProfile.vue'
import Login from '@/pages/public/Login.vue'
import Register from '@/pages/public/Register.vue'
import SelectRole from '@/pages/public/SelectRole.vue'
import BuyerDashboard from '@/pages/buyer/BuyerDashboard.vue'
import BuyerCart from '@/pages/buyer/BuyerCart.vue'
import SellerDashboard from '@/pages/seller/SellerDashboard.vue'
import DriverDashboard from '@/pages/driver/DriverDashboard.vue'
import AdminDashboard from '@/pages/admin/AdminDashboard.vue'

const routes = [
  {
    path: '/',
    component: GuestLayout,
    children: [
      { path: '', name: 'home', component: Home },
      { path: 'products', name: 'products', component: ProductList },
      { path: 'products/:id', name: 'product-detail', component: ProductDetail },
      { path: 'stores/:id', name: 'store-profile', component: StoreProfile },
      { path: 'login', name: 'login', component: Login, meta: { guestOnly: true } },
      { path: 'register', name: 'register', component: Register, meta: { guestOnly: true } },
      {
        path: 'select-role',
        name: 'select-role',
        component: SelectRole,
        meta: { requiresAuth: true },
      },

      {
        path: 'buyer/dashboard',
        name: 'buyer-dashboard',
        component: BuyerDashboard,
        meta: { requiresAuth: true, role: 'buyer' },
      },
      {
        path: 'buyer/cart',
        name: 'buyer-cart',
        component: BuyerCart,
        meta: { requiresAuth: true, role: 'buyer' },
      },
      {
        path: 'seller/dashboard',
        name: 'seller-dashboard',
        component: SellerDashboard,
        meta: { requiresAuth: true, role: 'seller' },
      },
      {
        path: 'driver/dashboard',
        name: 'driver-dashboard',
        component: DriverDashboard,
        meta: { requiresAuth: true, role: 'driver' },
      },
      {
        path: 'admin/dashboard',
        name: 'admin-dashboard',
        component: AdminDashboard,
        meta: { requiresAuth: true, role: 'admin' },
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()

  await auth.initSession()

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
