<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import { useSeo } from '../../composables/useSeo';
import { rupiah, formatDate, STATUS_LABELS, STATUS_CLASSES, TYPE_LABELS } from '../../utils/format';
import EmptyState from '../../components/EmptyState.vue';

useSeo({ title: 'Dashboard Pemilik' });

const auth = useAuthStore();
const toast = useToastStore();

const summary = ref(null);
const kosList = ref([]);
const latestReviews = ref([]);
const loading = ref(true);
const busyId = ref(null);

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
                <h1 class="text-xl font-bold text-slate-900">Dashboard Pemilik</h1>
                <p class="text-sm text-slate-500">Kelola listing kos dan pantau ketersediaan kamar.</p>
            </div>
            <RouterLink
                :to="{ name: 'owner.kos.create' }"
                class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700"
            >
                + Tambah Kos
            </RouterLink>
        </div>

        <div v-if="auth.ownerPending" class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <strong>Akunmu menunggu verifikasi admin.</strong> Kos tetap bisa dibuat, tetapi baru tampil di pencarian publik setelah diverifikasi.
        </div>

        <!-- Ringkasan -->
        <div v-if="summary" class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-3">
                <p class="text-xs text-slate-500">Total Kos</p>
                <p class="mt-1 text-xl font-bold text-slate-900">{{ summary.total_kos }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3">
                <p class="text-xs text-slate-500">Kos Aktif</p>
                <p class="mt-1 text-xl font-bold text-emerald-600">{{ summary.active_kos }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3">
                <p class="text-xs text-slate-500">Menunggu Verifikasi</p>
                <p class="mt-1 text-xl font-bold text-amber-600">{{ summary.pending_kos }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3">
                <p class="text-xs text-slate-500">Kamar Kosong</p>
                <p class="mt-1 text-xl font-bold text-brand-700">
                    {{ summary.available_rooms }}<span class="text-sm font-normal text-slate-400">/{{ summary.total_rooms }}</span>
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3">
                <p class="text-xs text-slate-500">Klik WhatsApp</p>
                <p class="mt-1 text-xl font-bold text-slate-900">{{ summary.wa_clicks }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-3">
                <p class="text-xs text-slate-500">Rating Rata-rata</p>
                <p class="mt-1 text-xl font-bold text-amber-500">
                    {{ summary.rating_avg ? Number(summary.rating_avg).toFixed(1) : '-' }}
                    <span class="text-xs font-normal text-slate-400">({{ summary.reviews_count }})</span>
                </p>
            </div>
        </div>

        <!-- Daftar kos -->
        <h2 class="mt-8 text-base font-semibold text-slate-800">Listing Kos Saya</h2>

        <div v-if="loading" class="mt-3 space-y-3">
            <div v-for="n in 2" :key="n" class="h-24 animate-pulse rounded-2xl bg-slate-200"></div>
        </div>

        <div v-else-if="kosList.length" class="mt-3 space-y-3">
            <div v-for="kos in kosList" :key="kos.id" class="rounded-2xl border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="truncate font-semibold text-slate-800">{{ kos.name }}</h3>
                            <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold" :class="STATUS_CLASSES[kos.status]">
                                {{ STATUS_LABELS[kos.status] }}
                            </span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                Kos {{ TYPE_LABELS[kos.type] }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ rupiah(kos.price_min) }}/bulan · {{ kos.district }} · ⭐
                            {{ kos.rating_avg ? Number(kos.rating_avg).toFixed(1) : '-' }} ({{ kos.reviews_count }}) · {{ kos.wa_clicks }} klik WA
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2 rounded-xl border border-slate-200 px-2 py-1">
                            <button
                                class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 font-bold text-slate-700 hover:bg-slate-200 disabled:opacity-40"
                                :disabled="kos.available_rooms <= 0 || busyId === kos.id"
                                @click="adjustStock(kos, -1)"
                            >
                                −
                            </button>
                            <span class="min-w-[3.5rem] text-center text-xs">
                                <strong class="text-sm" :class="kos.available_rooms === 0 ? 'text-rose-600' : 'text-slate-800'">
                                    {{ kos.available_rooms === 0 ? 'Penuh' : kos.available_rooms + ' kosong' }}
                                </strong>
                            </span>
                            <button
                                class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 font-bold text-slate-700 hover:bg-slate-200 disabled:opacity-40"
                                :disabled="kos.available_rooms >= kos.total_rooms || busyId === kos.id"
                                @click="adjustStock(kos, 1)"
                            >
                                +
                            </button>
                        </div>

                        <RouterLink
                            :to="{ name: 'owner.kos.edit', params: { id: kos.id } }"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Kelola
                        </RouterLink>
                        <button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50" @click="remove(kos)">
                            Hapus
                        </button>
                    </div>
                </div>
                <p class="mt-2 text-xs text-slate-400">Total {{ kos.total_rooms }} kamar · sisa dikelola cepat lewat tombol + / −</p>
            </div>
        </div>

        <EmptyState
            v-else
            icon="🏠"
            title="Belum ada kos"
            description="Tambahkan listing kos pertamamu dan mulai terima penghuni."
        >
            <RouterLink :to="{ name: 'owner.kos.create' }" class="mt-4 inline-block rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">
                + Tambah Kos
            </RouterLink>
        </EmptyState>

        <!-- Ulasan terbaru -->
        <template v-if="latestReviews.length">
            <h2 class="mt-8 text-base font-semibold text-slate-800">Ulasan Terbaru</h2>
            <div class="mt-3 space-y-2">
                <div v-for="review in latestReviews" :key="review.id" class="rounded-2xl border border-slate-200 bg-white p-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-800">{{ review.user_name }}</span>
                        <span class="text-amber-500">{{ '★'.repeat(review.rating) }}</span>
                    </div>
                    <p class="text-xs text-slate-500">{{ review.kos_name }} · {{ formatDate(review.created_at) }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ review.comment }}</p>
                </div>
            </div>
        </template>
    </div>
</template>
