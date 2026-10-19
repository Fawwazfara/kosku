<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';
import { useSeo } from '../../composables/useSeo';
import AdminNav from '../../components/AdminNav.vue';
import AppIcon from '../../components/AppIcon.vue';

useSeo({ title: 'Panel Admin' });

const summary = ref(null);
const loading = ref(true);

const cards = [
    { key: 'pending_owners', label: 'Pemilik Menunggu Verifikasi', icon: 'shield', tone: 'bg-amber-50 text-amber-600', to: { name: 'admin.owners' } },
    { key: 'pending_kos', label: 'Kos Menunggu Verifikasi', icon: 'home', tone: 'bg-amber-50 text-amber-600', to: { name: 'admin.kos' } },
    { key: 'active_kos', label: 'Kos Aktif', icon: 'check', tone: 'bg-emerald-50 text-emerald-600', to: { name: 'admin.kos' } },
    { key: 'hidden_reviews', label: 'Ulasan Disembunyikan', icon: 'star', tone: 'bg-rose-50 text-rose-600', to: { name: 'admin.reviews' } },
    { key: 'users', label: 'Total Pengguna', icon: 'user', tone: 'bg-brand-50 text-brand-700', to: { name: 'admin.users' } },
    { key: 'full_kos', label: 'Kos Penuh', icon: 'dashboard', tone: 'bg-slate-100 text-slate-600', to: { name: 'admin.kos' } },
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
        <h1 class="text-xl font-bold tracking-tight text-slate-900">Panel Admin</h1>
        <p class="text-sm text-slate-500">Verifikasi, moderasi, dan kelola data master KosKu.</p>

        <div class="mt-4">
            <AdminNav />
        </div>

        <div v-if="loading" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div v-for="n in 6" :key="n" class="h-28 animate-pulse rounded-2xl bg-slate-200"></div>
        </div>

        <div v-else-if="summary" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <RouterLink
                v-for="card in cards"
                :key="card.key"
                :to="card.to"
                class="card group p-4 transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-card"
            >
                <span class="grid h-10 w-10 place-items-center rounded-xl" :class="card.tone">
                    <AppIcon :name="card.icon" :size="20" />
                </span>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ summary[card.key] }}</p>
                <p class="text-xs text-slate-500">{{ card.label }}</p>
            </RouterLink>
        </div>
    </div>
</template>
