<script setup>
import { onMounted, ref } from 'vue';
import api from '../services/api';
import { useSeo } from '../composables/useSeo';
import KosCard from '../components/KosCard.vue';
import Pagination from '../components/Pagination.vue';
import EmptyState from '../components/EmptyState.vue';

useSeo({ title: 'Favorit Saya' });

const kosList = ref([]);
const meta = ref(null);
const loading = ref(true);

async function load(page = 1) {
    loading.value = true;
    try {
        const { data } = await api.get('/favorites', { params: { page } });
        kosList.value = data.data;
        meta.value = data.meta;
    } finally {
        loading.value = false;
    }
}

onMounted(() => load());
</script>

<template>
    <div class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="text-xl font-bold tracking-tight text-slate-900">Kos Favorit Saya</h1>
        <p class="mt-1 text-sm text-slate-500">{{ meta ? meta.total : 0 }} kos yang kamu simpan untuk dibandingkan nanti.</p>

        <div v-if="loading" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div v-for="n in 3" :key="n" class="aspect-[4/3] animate-pulse rounded-2xl bg-slate-200"></div>
        </div>

        <template v-else-if="kosList.length">
            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                <KosCard v-for="kos in kosList" :key="kos.id" :kos="kos" />
            </div>
            <div class="mt-6">
                <Pagination :meta="meta" @change="load" />
            </div>
        </template>

        <EmptyState
            v-else
            icon="heart"
            title="Belum ada kos favorit"
            description="Tekan tombol simpan di halaman detail kos untuk menyimpannya di sini."
        >
            <RouterLink :to="{ name: 'home' }" class="btn btn-primary mt-4">Cari kos</RouterLink>
        </EmptyState>
    </div>
</template>
