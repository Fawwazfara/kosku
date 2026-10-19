<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import api from '../services/api';
import { useSeo } from '../composables/useSeo';
import KosCard from '../components/KosCard.vue';
import KosMap from '../components/KosMap.vue';
import Pagination from '../components/Pagination.vue';
import EmptyState from '../components/EmptyState.vue';
import AppIcon from '../components/AppIcon.vue';

useSeo();

const filters = reactive({
    q: '',
    type: '',
    price_min: '',
    price_max: '',
    facilities: [],
    available: true,
    sort: 'newest',
    page: 1,
});

const kosList = ref([]);
const meta = ref(null);
const facilities = ref([]);
const loading = ref(false);
const showMap = ref(false);
const showFilter = ref(false);

const typeOptions = [
    { value: '', label: 'Semua' },
    { value: 'putri', label: 'Putri' },
    { value: 'putra', label: 'Putra' },
    { value: 'campur', label: 'Campur' },
];

let debounce;

async function load() {
    loading.value = true;
    try {
        const params = {
            q: filters.q || undefined,
            type: filters.type || undefined,
            price_min: filters.price_min || undefined,
            price_max: filters.price_max || undefined,
            facilities: filters.facilities.length ? filters.facilities : undefined,
            available: filters.available ? 1 : undefined,
            sort: filters.sort,
            page: filters.page,
        };
        const { data } = await api.get('/kos', { params });
        kosList.value = data.data;
        meta.value = data.meta;
    } finally {
        loading.value = false;
    }
}

function toggleFacility(id) {
    const index = filters.facilities.indexOf(id);
    if (index === -1) filters.facilities.push(id);
    else filters.facilities.splice(index, 1);
    filters.page = 1;
    load();
}

function reset() {
    filters.q = '';
    filters.type = '';
    filters.price_min = '';
    filters.price_max = '';
    filters.facilities = [];
    filters.available = true;
    filters.sort = 'newest';
    filters.page = 1;
    load();
}

function changePage(page) {
    filters.page = page;
    load();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

watch(() => [filters.q], () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        filters.page = 1;
        load();
    }, 400);
});

watch(() => [filters.type, filters.available, filters.sort], () => {
    filters.page = 1;
    load();
});

const resultLabel = computed(() => {
    if (!meta.value) return '';
    return `${meta.value.total} kos ditemukan`;
});

const activeFilterCount = computed(() => {
    let count = 0;
    if (filters.type) count++;
    if (filters.price_min || filters.price_max) count++;
    count += filters.facilities.length;
    return count;
});

onMounted(async () => {
    const { data } = await api.get('/facilities');
    facilities.value = data.data;
    load();
});
</script>

<template>
    <div>
        <section class="relative overflow-hidden bg-gradient-to-br from-brand-800 via-brand-700 to-brand-600 pb-10 pt-12 text-white">
            <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 left-10 h-72 w-72 rounded-full bg-brand-400/20 blur-3xl"></div>

            <div class="relative mx-auto max-w-6xl px-4">
                <span class="badge bg-white/15 text-white backdrop-blur">
                    <AppIcon name="sparkles" :size="14" /> Data kamar diperbarui pemilik
                </span>
                <h1 class="mt-3 max-w-2xl text-3xl font-bold leading-tight tracking-tight sm:text-4xl">
                    Cari kos tanpa keliling gang.
                </h1>
                <p class="mt-2 max-w-xl text-sm text-white/85">
                    Lihat sisa kamar real-time, bandingkan harga, dan hubungi pemilik langsung lewat WhatsApp.
                </p>

                <div class="mt-6 flex gap-2">
                    <div class="relative flex-1">
                        <AppIcon name="search" :size="18" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="filters.q"
                            type="search"
                            placeholder="Cari nama kos, area, atau kampus…"
                            class="w-full rounded-2xl border-0 py-3.5 pl-11 pr-4 text-sm text-slate-800 shadow-card focus:outline-none focus:ring-2 focus:ring-white/70"
                        />
                    </div>
                    <button
                        class="relative flex items-center gap-2 rounded-2xl bg-white/15 px-4 text-sm font-semibold backdrop-blur transition hover:bg-white/25 lg:hidden"
                        @click="showFilter = !showFilter"
                    >
                        <AppIcon name="filter" :size="18" />
                        <span class="hidden sm:inline">Filter</span>
                        <span v-if="activeFilterCount" class="grid h-5 w-5 place-items-center rounded-full bg-amber-400 text-[11px] text-slate-900">{{ activeFilterCount }}</span>
                    </button>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-6xl gap-6 px-4 py-6 lg:flex lg:items-start">
            <aside class="mb-4 shrink-0 lg:mb-0 lg:block lg:w-64" :class="showFilter ? 'block' : 'hidden'">
                <div class="card space-y-5 p-4 lg:sticky lg:top-20">
                    <div class="flex items-center justify-between">
                        <h2 class="section-title">Filter</h2>
                        <button class="text-xs font-semibold text-brand-700 hover:underline" @click="reset">Reset</button>
                    </div>

                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Tipe Kos</p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="type in typeOptions"
                                :key="type.value"
                                class="chip"
                                :class="filters.type === type.value ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 text-slate-600 hover:border-brand-300'"
                                @click="filters.type = type.value"
                            >
                                {{ type.label }}
                            </button>
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Rentang Harga</p>
                        <div class="flex items-center gap-2">
                            <input v-model="filters.price_min" type="number" min="0" step="50000" placeholder="Min" class="input" @change="filters.page = 1; load()" />
                            <span class="text-slate-400">–</span>
                            <input v-model="filters.price_max" type="number" min="0" step="50000" placeholder="Max" class="input" @change="filters.page = 1; load()" />
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Fasilitas</p>
                        <div class="max-h-48 space-y-1 overflow-y-auto pr-1">
                            <label
                                v-for="facility in facilities"
                                :key="facility.id"
                                class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-slate-600 hover:bg-slate-50"
                            >
                                <input
                                    type="checkbox"
                                    class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                    :checked="filters.facilities.includes(facility.id)"
                                    @change="toggleFacility(facility.id)"
                                />
                                {{ facility.name }}
                            </label>
                        </div>
                    </div>

                    <label class="flex cursor-pointer items-center gap-2 border-t border-slate-100 pt-3 text-sm font-medium text-slate-700">
                        <input v-model="filters.available" type="checkbox" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                        Hanya tampilkan yang ada kamar
                    </label>
                </div>
            </aside>

            <section class="min-w-0 flex-1">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-medium text-slate-600">{{ loading ? 'Memuat…' : resultLabel }}</p>
                    <div class="flex items-center gap-2">
                        <button class="btn btn-secondary lg:hidden" @click="showMap = !showMap">
                            <AppIcon name="map" :size="16" /> {{ showMap ? 'Sembunyikan' : 'Peta' }}
                        </button>
                        <select v-model="filters.sort" class="input w-auto py-2">
                            <option value="newest">Terbaru</option>
                            <option value="cheapest">Termurah</option>
                            <option value="rating">Rating tertinggi</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4 overflow-hidden rounded-2xl" :class="showMap ? 'block' : 'hidden lg:block'">
                    <KosMap :markers="kosList" height="300px" />
                </div>

                <div v-if="loading" class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div v-for="n in 6" :key="n" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white">
                        <div class="aspect-[4/3] animate-pulse bg-slate-200"></div>
                        <div class="space-y-2 p-3.5">
                            <div class="h-3 w-3/4 animate-pulse rounded bg-slate-200"></div>
                            <div class="h-3 w-1/2 animate-pulse rounded bg-slate-200"></div>
                        </div>
                    </div>
                </div>

                <template v-else-if="kosList.length">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <KosCard v-for="kos in kosList" :key="kos.id" :kos="kos" />
                    </div>
                    <div class="mt-6">
                        <Pagination :meta="meta" @change="changePage" />
                    </div>
                </template>

                <EmptyState
                    v-else
                    icon="search"
                    title="Kos tidak ditemukan"
                    description="Coba ubah kata kunci atau kurangi filter yang dipasang."
                >
                    <button class="btn btn-primary mt-4" @click="reset">Reset filter</button>
                </EmptyState>
            </section>
        </div>
    </div>
</template>
