<script setup>
defineProps({
    title: { type: String, default: 'Tidak ada data' },
    description: { type: String, default: '' },
    icon: { type: String, default: '🏠' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
        <span class="text-4xl">{{ icon }}</span>
        <h3 class="mt-3 text-base font-semibold text-slate-700">{{ title }}</h3>
        <p v-if="description" class="mt-1 max-w-sm text-sm text-slate-500">{{ description }}</p>
        <slot />
    </div>
</template>
