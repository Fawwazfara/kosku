<script setup>
import { computed } from 'vue';
import AppIcon from './AppIcon.vue';

const props = defineProps({
    meta: { type: Object, required: true },
});

const emit = defineEmits(['change']);

const pages = computed(() => {
    const { current_page: current, last_page: last } = props.meta;
    const set = new Set([1, last, current, current - 1, current + 1]);
    return [...set].filter((p) => p >= 1 && p <= last).sort((a, b) => a - b);
});
</script>

<template>
    <div v-if="meta.last_page > 1" class="flex flex-wrap items-center justify-center gap-2">
        <button class="btn btn-secondary" :disabled="meta.current_page === 1" @click="emit('change', meta.current_page - 1)">
            <AppIcon name="chevron-left" :size="16" />
            <span class="hidden sm:inline">Sebelumnya</span>
        </button>

        <template v-for="(page, index) in pages" :key="page">
            <span v-if="index > 0 && page - pages[index - 1] > 1" class="px-1 text-slate-400">…</span>
            <button
                class="btn min-w-10 px-3"
                :class="page === meta.current_page ? 'bg-brand-600 text-white shadow-soft' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                @click="emit('change', page)"
            >
                {{ page }}
            </button>
        </template>

        <button class="btn btn-secondary" :disabled="meta.current_page === meta.last_page" @click="emit('change', meta.current_page + 1)">
            <span class="hidden sm:inline">Berikutnya</span>
            <AppIcon name="chevron-right" :size="16" />
        </button>
    </div>
</template>
