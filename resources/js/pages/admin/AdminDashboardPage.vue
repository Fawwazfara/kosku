<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';
import { useSeo } from '../../composables/useSeo';
import AdminNav from '../../components/AdminNav.vue';

useSeo({ title: 'Panel Admin' });

const summary = ref(null);
const loading = ref(true);

const cards = [
    { key: 'pending_owners', label: 'Pemilik Menunggu Verifikasi', icon: '🔑', to: { name: 'admin.owners' } },
    { key: 'pending_kos', label: 'Kos Menunggu Verifikasi', icon: '🏠', to: { name: 'admin.kos' } },
    { key: 'active_kos', label: 'Kos Aktif', icon: '✅', to: { name: 'admin.kos' } },
    { key: 'hidden_reviews', label: 'Ulasan Disembunyikan', icon: '🚩', to: { name: 'admin.reviews' } },
    { key: 'users', label: 'Total Pengguna', icon: '👥', to: { name: 'admin.users' } },
    { key: 'full_kos', label: 'Kos Penuh', icon: '🚪', to: { name: 'admin.kos' } },
];

onMounted(async () => {
    try {
        const { data } = await api.get('/admin/dashboard');
        summary.value = data.summary;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="text-xl font-bold text-slate-900">Panel Admin</h1>
        <p class="text-sm text-slate-500">Verifikasi, moderasi, dan kelola data master KosKu.</p>

        <div class="mt-4">
            <AdminNav />
        </div>

        <div v-if="loading" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div v-for="n in 6" :key="n" class="h-24 animate-pulse rounded-2xl bg-slate-200"></div>
        </div>

        <div v-else-if="summary" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <RouterLink v-for="card in cards" :key="card.key" :to="card.to" class="rounded-2xl border border-slate-200 bg-white p-4 hover:border-brand-300 hover:shadow-sm">
                <span class="text-xl">{{ card.icon }}</span>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ summary[card.key] }}</p>
                <p class="text-xs text-slate-500">{{ card.label }}</p>
            </RouterLink>
        </div>
    </div>
</template>
