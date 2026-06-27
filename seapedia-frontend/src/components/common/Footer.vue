<template>
  <footer class="bg-ink-900 text-ink-300 mt-12">
    <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-2 md:grid-cols-4 gap-8 text-sm">
      <div>
        <h4 class="text-white font-semibold mb-3">Tentang SEAPEDIA</h4>
        <ul class="space-y-2">
          <li class="hover:text-white cursor-pointer">Tentang Kami</li>
          <li class="hover:text-white cursor-pointer">Karir</li>
          <li class="hover:text-white cursor-pointer">Blog</li>
        </ul>
      </div>
      <div>
        <h4 class="text-white font-semibold mb-3">Bantuan</h4>
        <ul class="space-y-2">
          <li class="hover:text-white cursor-pointer">Pusat Bantuan</li>
          <li class="hover:text-white cursor-pointer">Cara Belanja</li>
          <li class="hover:text-white cursor-pointer">Cara Jualan</li>
        </ul>
      </div>
      <div>
        <h4 class="text-white font-semibold mb-3">Jual di SEAPEDIA</h4>
        <ul class="space-y-2">
          <li class="hover:text-white cursor-pointer">Mulai Berjualan</li>
          <li class="hover:text-white cursor-pointer">Pusat Edukasi Seller</li>
        </ul>
      </div>
      <div>
        <h4 class="text-white font-semibold mb-3">Ikuti Kami</h4>
        <p class="text-ink-400">Marketplace yang menghubungkan penjual, pembeli, dan mitra pengantaran di seluruh Indonesia.</p>
      </div>
    </div>
    <div class="border-t border-ink-700 py-4 text-center text-xs text-ink-500">
      © {{ year }} SEAPEDIA. Dibuat untuk COMPFEST 18 Software Engineering Academy.
    </div>
  </footer>
</template>

<script setup>
const year = new Date().getFullYear()
</script>
