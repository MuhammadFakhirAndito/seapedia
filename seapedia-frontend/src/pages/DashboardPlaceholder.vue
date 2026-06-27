<template>
  <div class="max-w-5xl mx-auto px-4 py-10">
    <div class="bg-white rounded-2xl border border-ink-100 p-8 text-center">
      <span class="text-4xl">{{ emoji }}</span>
      <h1 class="font-display font-bold text-xl text-ink-900 mt-3">{{ title }}</h1>
      <p class="text-sm text-ink-500 mt-1">
        Dashboard ini akan dilengkapi pada level pengembangan selanjutnya.
      </p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: 'Dashboard' },
  emoji: { type: String, default: '📊' },
})
</script>
