<script setup>
import { computed } from 'vue';
import StarRating from './StarRating.vue';
import AppIcon from './AppIcon.vue';
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
        class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-card"
    >
        <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
            <img
                v-if="kos.primary_photo"
                :src="kos.primary_photo.url"
                :alt="'Foto ' + kos.name"
                loading="lazy"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            />
            <div v-else class="grid h-full w-full place-items-center text-slate-300">
                <AppIcon name="image" :size="40" />
            </div>

            <div class="absolute inset-x-2 top-2 flex items-start justify-between gap-2">
                <span class="badge shadow-sm backdrop-blur" :class="typeClass">Kos {{ TYPE_LABELS[kos.type] || kos.type }}</span>
                <span class="badge shadow-sm" :class="full ? 'bg-rose-600 text-white' : 'bg-white/90 text-emerald-700'">
                    {{ full ? 'Penuh' : kos.available_rooms + ' kamar' }}
                </span>
            </div>
        </div>

        <div class="flex flex-1 flex-col gap-2 p-3.5">
            <h3 class="line-clamp-2 text-sm font-semibold leading-snug text-slate-800 transition group-hover:text-brand-700">{{ kos.name }}</h3>
            <p class="flex items-center gap-1 text-xs text-slate-500">
                <AppIcon name="pin" :size="14" class="text-slate-400" />
                <span class="truncate">{{ kos.district }}</span>
            </p>

            <div class="mt-auto pt-1">
                <p class="text-base font-bold text-slate-900">
                    {{ priceRange(kos.price_min, kos.price_max) }}
                    <span class="text-xs font-normal text-slate-500">/bulan</span>
                </p>
                <div class="mt-1.5 flex items-center gap-1.5">
                    <StarRating :model-value="Math.round(kos.rating_avg || 0)" size="sm" />
                    <span class="text-xs text-slate-500">
                        <template v-if="kos.rating_avg">{{ kos.rating_avg.toFixed(1) }}</template>
                        <template v-else>Belum ada</template>
                        · {{ kos.reviews_count }} ulasan
                    </span>
                </div>
            </div>
        </div>
    </RouterLink>
</template>
