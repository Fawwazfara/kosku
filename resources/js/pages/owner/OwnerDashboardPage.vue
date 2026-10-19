<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '../../services/api';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import { useSeo } from '../../composables/useSeo';
import { rupiah, formatDate, STATUS_LABELS, STATUS_CLASSES, TYPE_LABELS } from '../../utils/format';
import EmptyState from '../../components/EmptyState.vue';
import AppIcon from '../../components/AppIcon.vue';

useSeo({ title: 'Dashboard Pemilik' });

const auth = useAuthStore();
const toast = useToastStore();

const summary = ref(null);
const kosList = ref([]);
const latestReviews = ref([]);
const loading = ref(true);
const busyId = ref(null);

const statCards = computed(() => {
    if (!summary.value) return [];
    return [
        { label: 'Total Kos', value: summary.value.total_kos, icon: 'home', tone: 'text-slate-900' },
        { label: 'Kos Aktif', value: summary.value.active_kos, icon: 'check', tone: 'text-emerald-600' },
        { label: 'Menunggu Verifikasi', value: summary.value.pending_kos, icon: 'info', tone: 'text-amber-600' },
        { label: 'Kamar Kosong', value: `${summary.value.available_rooms}/${summary.value.total_rooms}`, icon: 'dashboard', tone: 'text-brand-700' },
        { label: 'Klik WhatsApp', value: summary.value.wa_clicks, icon: 'whatsapp', tone: 'text-slate-900' },
        { label: 'Rating Rata-rata', value: summary.value.rating_avg ? Number(summary.value.rating_avg).toFixed(1) : '-', icon: 'star', tone: 'text-amber-500' },
    ];
});

async function load() {
    loading.value = true;
    try {
        const [dashboard, kos] = await Promise.all([api.get('/owner/dashboard'), api.get('/owner/kos')]);
        summary.value = dashboard.data.summary;
        latestReviews.value = dashboard.data.latest_reviews;
        kosList.value = kos.data.data;
    } catch (error) {
        toast.error('Gagal memuat dashboard.');
    } finally {
        loading.value = false;
    }
}

async function adjustStock(kos, delta) {
    const next = Math.min(kos.total_rooms, Math.max(0, kos.available_rooms + delta));
    if (next === kos.available_rooms) return;

    busyId.value = kos.id;
    try {
        const { data } = await api.put(`/owner/kos/${kos.id}/stock`, { available_rooms: next });
        toast.success(data.message);
        await load();
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gagal memperbarui stok.');
    } finally {
        busyId.value = null;
    }
}

async function remove(kos) {
    if (!confirm(`Hapus "${kos.name}"? Foto dan ulasannya juga akan terhapus.`)) return;
    try {
        await api.delete(`/owner/kos/${kos.id}`);
        toast.success('Kos dihapus.');
        await load();
    } catch {
        toast.error('Gagal menghapus kos.');
    }
}

onMounted(load);
</script>

<template>
    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">Dashboard Pemilik</h1>
                <p class="text-sm text-slate-500">Kelola listing kos dan pantau ketersediaan kamar.</p>
            </div>
            <RouterLink :to="{ name: 'owner.kos.create' }" class="btn btn-primary">
                <AppIcon name="plus" :size="17" /> Tambah Kos
            </RouterLink>
        </div>

        <div v-if="auth.ownerPending" class="mt-4 flex items-start gap-2.5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <AppIcon name="alert" :size="18" class="mt-0.5 shrink-0" />
            <p><strong>Akunmu menunggu verifikasi admin.</strong> Kos tetap bisa dibuat, tetapi baru tampil di pencarian publik setelah diverifikasi.</p>
        </div>

        <div v-if="summary" class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div v-for="stat in statCards" :key="stat.label" class="card p-3.5">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-slate-500">{{ stat.label }}</p>
                    <AppIcon :name="stat.icon" :size="16" class="text-slate-300" />
                </div>
                <p class="mt-1.5 text-xl font-bold" :class="stat.tone">{{ stat.value }}</p>
            </div>
        </div>

        <h2 class="section-title mt-8">Listing Kos Saya</h2>

        <div v-if="loading" class="mt-3 space-y-3">
            <div v-for="n in 2" :key="n" class="h-24 animate-pulse rounded-2xl bg-slate-200"></div>
        </div>

        <div v-else-if="kosList.length" class="mt-3 space-y-3">
            <div v-for="kos in kosList" :key="kos.id" class="card p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="hidden h-16 w-20 shrink-0 overflow-hidden rounded-xl bg-slate-100 sm:block">
                            <img v-if="kos.primary_photo" :src="kos.primary_photo.url" :alt="kos.name" class="h-full w-full object-cover" />
                            <div v-else class="grid h-full w-full place-items-center text-slate-300"><AppIcon name="image" :size="22" /></div>
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="truncate font-semibold text-slate-800">{{ kos.name }}</h3>
                                <span class="badge" :class="STATUS_CLASSES[kos.status]">{{ STATUS_LABELS[kos.status] }}</span>
                                <span class="badge bg-slate-100 text-slate-600">Kos {{ TYPE_LABELS[kos.type] }}</span>
                            </div>
                            <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-sm text-slate-500">
                                <span class="font-medium text-slate-700">{{ rupiah(kos.price_min) }}/bulan</span>
                                <span>· {{ kos.district }}</span>
                                <span class="inline-flex items-center gap-1"><AppIcon name="star" :size="13" class="text-amber-500" /> {{ kos.rating_avg ? Number(kos.rating_avg).toFixed(1) : '-' }} ({{ kos.reviews_count }})</span>
                                <span class="inline-flex items-center gap-1"><AppIcon name="whatsapp" :size="13" class="text-emerald-600" /> {{ kos.wa_clicks }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1.5 rounded-xl border border-slate-200 px-2 py-1">
                            <button class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 disabled:opacity-40" :disabled="kos.available_rooms <= 0 || busyId === kos.id" @click="adjustStock(kos, -1)">
                                <AppIcon name="minus" :size="14" />
                            </button>
                            <span class="min-w-[3.5rem] text-center text-xs font-semibold" :class="kos.available_rooms === 0 ? 'text-rose-600' : 'text-slate-800'">
                                {{ kos.available_rooms === 0 ? 'Penuh' : kos.available_rooms + ' kosong' }}
                            </span>
                            <button class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 disabled:opacity-40" :disabled="kos.available_rooms >= kos.total_rooms || busyId === kos.id" @click="adjustStock(kos, 1)">
                                <AppIcon name="plus" :size="14" />
                            </button>
                        </div>

                        <RouterLink :to="{ name: 'owner.kos.edit', params: { id: kos.id } }" class="btn btn-secondary">
                            <AppIcon name="pencil" :size="15" /> Kelola
                        </RouterLink>
                        <button class="btn border border-rose-200 bg-white text-rose-600 hover:bg-rose-50" @click="remove(kos)">
                            <AppIcon name="trash" :size="15" />
                        </button>
                    </div>
                </div>
                <p class="mt-2 text-xs text-slate-400">Total {{ kos.total_rooms }} kamar · kelola cepat lewat tombol + / −</p>
            </div>
        </div>

        <EmptyState v-else icon="home" title="Belum ada kos" description="Tambahkan listing kos pertamamu dan mulai terima penghuni.">
            <RouterLink :to="{ name: 'owner.kos.create' }" class="btn btn-primary mt-4">
                <AppIcon name="plus" :size="17" /> Tambah Kos
            </RouterLink>
        </EmptyState>

        <template v-if="latestReviews.length">
            <h2 class="section-title mt-8">Ulasan Terbaru</h2>
            <div class="mt-3 space-y-2">
                <div v-for="review in latestReviews" :key="review.id" class="card p-3.5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-800">{{ review.user_name }}</span>
                        <span class="flex text-amber-500"><AppIcon v-for="n in review.rating" :key="n" name="star" :size="13" /></span>
                    </div>
                    <p class="text-xs text-slate-500">{{ review.kos_name }} · {{ formatDate(review.created_at) }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ review.comment }}</p>
                </div>
            </div>
        </template>
    </div>
</template>
