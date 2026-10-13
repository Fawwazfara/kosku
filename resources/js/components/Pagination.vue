<script setup>
import { computed } from 'vue';

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
        <button
            class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 disabled:opacity-40"
            :disabled="meta.current_page === 1"
            @click="emit('change', meta.current_page - 1)"
        >
            Sebelumnya
        </button>

        <template v-for="(page, index) in pages" :key="page">
            <span v-if="index > 0 && page - pages[index - 1] > 1" class="px-1 text-slate-400">…</span>
            <button
                class="min-w-10 rounded-lg border px-3 py-2 text-sm font-medium"
                :class="page === meta.current_page ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                @click="emit('change', page)"
            >
                {{ page }}
            </button>
        </template>

        <button
            class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 disabled:opacity-40"
            :disabled="meta.current_page === meta.last_page"
            @click="emit('change', meta.current_page + 1)"
        >
            Berikutnya
        </button>
    </div>
</template>
