<script setup>
import { computed } from 'vue';
import StarRating from './StarRating.vue';
import { priceRange, TYPE_LABELS, TYPE_CLASSES } from '../utils/format';

const props = defineProps({
    kos: { type: Object, required: true },
});

const full = computed(() => props.kos.available_rooms <= 0);
const typeClass = computed(() => TYPE_CLASSES[props.kos.type] || 'bg-slate-100 text-slate-700');
</script>

<template>
    <RouterLink
        :to="{ name: 'kos.detail', params: { slug: kos.slug } }"
        class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
    >
        <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
            <img
                v-if="kos.primary_photo"
                :src="kos.primary_photo.url"
                :alt="'Foto ' + kos.name"
                loading="lazy"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
            />
            <div v-else class="grid h-full w-full place-items-center text-4xl text-slate-300">🏠</div>

            <span class="absolute left-2 top-2 rounded-full px-2.5 py-1 text-xs font-semibold backdrop-blur" :class="typeClass">
                Kos {{ TYPE_LABELS[kos.type] || kos.type }}
            </span>
            <span
                class="absolute right-2 top-2 rounded-full px-2.5 py-1 text-xs font-semibold text-white shadow"
                :class="full ? 'bg-rose-600' : 'bg-emerald-600'"
            >
                {{ full ? 'Penuh' : kos.available_rooms + ' kamar' }}
            </span>
        </div>

        <div class="flex flex-1 flex-col gap-2 p-3">
            <h3 class="line-clamp-2 text-sm font-semibold text-slate-800 group-hover:text-brand-700">{{ kos.name }}</h3>
            <p class="flex items-center gap-1 text-xs text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="truncate">{{ kos.district }}</span>
            </p>

            <div class="mt-auto">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-brand-700">
                        {{ priceRange(kos.price_min, kos.price_max) }}
                        <span class="text-xs font-normal text-slate-500">/bulan</span>
                    </p>
                </div>
                <div class="mt-1 flex items-center gap-1.5">
                    <StarRating :model-value="Math.round(kos.rating_avg || 0)" size="sm" />
                    <span class="text-xs text-slate-500">
                        <template v-if="kos.rating_avg">{{ kos.rating_avg.toFixed(1) }}</template>
                        <template v-else>Belum ada</template>
                        ({{ kos.reviews_count }})
                    </span>
                </div>
            </div>
        </div>
    </RouterLink>
</template>
