<script setup>
import { computed, ref } from 'vue';
import AppIcon from './AppIcon.vue';

const props = defineProps({
    photos: { type: Array, default: () => [] },
    name: { type: String, default: '' },
});

const active = ref(0);

const current = computed(() => props.photos[active.value] || null);

function next() {
    active.value = (active.value + 1) % props.photos.length;
}
function prev() {
    active.value = (active.value - 1 + props.photos.length) % props.photos.length;
}
</script>

<template>
    <div>
        <div class="relative aspect-[16/10] overflow-hidden rounded-2xl bg-slate-100 sm:aspect-[16/9]">
            <img
                v-if="current"
                :src="current.url"
                :alt="'Foto ' + name"
                class="h-full w-full object-cover"
            />
            <div v-else class="grid h-full w-full place-items-center text-slate-300">
                <AppIcon name="image" :size="56" />
            </div>

            <template v-if="photos.length > 1">
                <button
                    class="absolute left-2 top-1/2 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-full bg-black/40 text-white backdrop-blur transition hover:bg-black/60"
                    aria-label="Foto sebelumnya"
                    @click="prev"
                >
                    <AppIcon name="chevron-left" :size="18" />
                </button>
                <button
                    class="absolute right-2 top-1/2 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-full bg-black/40 text-white backdrop-blur transition hover:bg-black/60"
                    aria-label="Foto berikutnya"
                    @click="next"
                >
                    <AppIcon name="chevron-right" :size="18" />
                </button>
                <span class="absolute bottom-2 right-2 rounded-full bg-black/50 px-2.5 py-1 text-xs font-medium text-white">
                    {{ active + 1 }} / {{ photos.length }}
                </span>
            </template>
        </div>

        <div v-if="photos.length > 1" class="mt-2 flex gap-2 overflow-x-auto pb-1">
            <button
                v-for="(photo, index) in photos"
                :key="photo.id"
                class="h-16 w-24 shrink-0 overflow-hidden rounded-lg border-2"
                :class="index === active ? 'border-brand-600' : 'border-transparent'"
                @click="active = index"
            >
                <img :src="photo.url" :alt="'Thumbnail ' + (index + 1)" loading="lazy" class="h-full w-full object-cover" />
            </button>
        </div>
    </div>
</template>
