<script setup>
import AppIcon from './AppIcon.vue';

defineProps({
    title: { type: String, default: 'Tidak ada data' },
    description: { type: String, default: '' },
    icon: { type: String, default: 'home' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
        <span class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">
            <AppIcon :name="icon" :size="28" />
        </span>
        <h3 class="mt-4 text-base font-semibold text-slate-800">{{ title }}</h3>
        <p v-if="description" class="mt-1 max-w-sm text-sm text-slate-500">{{ description }}</p>
        <slot />
    </div>
</template>
