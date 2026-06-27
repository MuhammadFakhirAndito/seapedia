<template>
  <RouterLink
    :to="`/products/${product.id}`"
    class="group flex flex-col bg-white rounded-xl border border-ink-100 overflow-hidden hover:shadow-md hover:border-brand-200 transition-all duration-150"
  >
    <div class="relative aspect-square bg-ink-50 overflow-hidden">
      <img
        :src="product.image || placeholderImage"
        :alt="product.name"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
      />
      <span v-if="discountPercent" class="discount-ribbon">
        {{ discountPercent }}%
      </span>
    </div>

    <div class="flex flex-col gap-1 p-3">
      <h3 class="text-sm text-ink-800 line-clamp-2 leading-snug min-h-[2.5rem]">
        {{ product.name }}
      </h3>

      <div class="flex items-baseline gap-1.5 mt-0.5">
        <span class="text-base font-bold text-ink-900">
          {{ formatPrice(discountedPrice) }}
        </span>
      </div>
      <span v-if="discountPercent" class="text-xs text-ink-400 line-through">
        {{ formatPrice(product.price) }}
      </span>

      <div class="flex items-center gap-1 mt-1 text-xs text-ink-500">
        <svg class="w-3.5 h-3.5 text-accent-500 fill-current" viewBox="0 0 20 20">
          <path
            d="M10 1l2.6 6.3 6.8.5-5.2 4.4 1.7 6.6L10 15.5 4.1 18.8l1.7-6.6L.6 7.8l6.8-.5L10 1z"
          />
        </svg>
        <span>{{ product.rating ?? '5.0' }}</span>
        <span class="text-ink-300">•</span>
        <span>{{ formatSold(product.sold_count) }} terjual</span>
      </div>

      <div class="flex items-center gap-1 text-xs text-ink-400 mt-0.5">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
          />
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
          />
        </svg>
        <span class="truncate">{{ product.store_city || 'Indonesia' }}</span>
      </div>
    </div>
  </RouterLink>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  product: {
    type: Object,
    required: true,
    // expected shape: { id, name, price, image, rating, sold_count, store_city, discount_percent }
  },
})

const placeholderImage =
  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"%3E%3Crect fill="%23ececea" width="200" height="200"/%3E%3C/svg%3E'

const discountPercent = computed(() => props.product.discount_percent || 0)

const discountedPrice = computed(() => {
  if (!discountPercent.value) return props.product.price
  return Math.round(props.product.price * (1 - discountPercent.value / 100))
})

function formatPrice(value) {
  return 'Rp' + Number(value).toLocaleString('id-ID')
}

function formatSold(count) {
  if (!count) return '0'
  if (count >= 1000) return `${(count / 1000).toFixed(1)}rb`
  return String(count)
}
</script>
