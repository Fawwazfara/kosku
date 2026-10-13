<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import { useSeo } from '../../composables/useSeo';
import { rupiah, STATUS_LABELS, STATUS_CLASSES, TYPE_LABELS } from '../../utils/format';
import AdminNav from '../../components/AdminNav.vue';
import Pagination from '../../components/Pagination.vue';
import EmptyState from '../../components/EmptyState.vue';

useSeo({ title: 'Verifikasi Kos' });

const toast = useToastStore();
const kosList = ref([]);
const meta = ref(null);
const loading = ref(true);
const status = ref('pending');
const busyId = ref(null);

const tabs = [
    { value: 'pending', label: 'Menunggu' },
    { value: 'active', label: 'Aktif' },
    { value: 'hidden', label: 'Disembunyikan' },
    { value: 'rejected', label: 'Ditolak' },
];

async function load(page = 1) {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/kos', { params: { status: status.value, page } });
        kosList.value = data.data;
        meta.value = data.meta;
    } finally {
        loading.value = false;
    }
}

async function act(kos, action) {
    busyId.value = kos.id;
    try {
        const { data } = await api.post(`/admin/kos/${kos.id}/${action}`);
        toast.success(data.message);
        await load(meta.value?.current_page || 1);
    } catch (error) {
        toast.error(error.response?.data?.message || 'Aksi gagal.');
    } finally {
        busyId.value = null;
    }
}

onMounted(() => load());
</script>

<template>
    <div class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="text-xl font-bold text-slate-900">Verifikasi & Moderasi Kos</h1>
        <div class="mt-4">
            <AdminNav />
        </div>

        <div class="mt-4 flex gap-2">
            <button
                v-for="tab in tabs"
                :key="tab.value"
                class="rounded-full border px-3 py-1.5 text-xs font-medium"
                :class="status === tab.value ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 text-slate-600'"
                @click="status = tab.value; load()"
            >
                {{ tab.label }}
            </button>
        </div>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="n in 3" :key="n" class="h-28 animate-pulse rounded-2xl bg-slate-200"></div>
        </div>

        <div v-else-if="kosList.length" class="mt-4 space-y-3">
            <div v-for="kos in kosList" :key="kos.id" class="rounded-2xl border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-start gap-4">
                    <img
                        v-if="kos.primary_photo"
                        :src="kos.primary_photo.url"
                        :alt="kos.name"
                        class="h-20 w-28 rounded-xl object-cover"
                        loading="lazy"
                    />
                    <div v-else class="grid h-20 w-28 place-items-center rounded-xl bg-slate-100 text-2xl">🏠</div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-semibold text-slate-800">{{ kos.name }}</h2>
                            <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold" :class="STATUS_CLASSES[kos.status]">
                                {{ STATUS_LABELS[kos.status] }}
                            </span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                Kos {{ TYPE_LABELS[kos.type] }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            Pemilik: {{ kos.owner?.name || '-' }} · {{ rupiah(kos.price_min) }}/bulan · {{ kos.district }}
                        </p>
                        <p class="text-xs text-slate-400">
                            {{ kos.available_rooms }}/{{ kos.total_rooms }} kamar kosong · ⭐ {{ kos.rating_avg ? Number(kos.rating_avg).toFixed(1) : '-' }} ({{ kos.reviews_count }})
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <RouterLink
                            :to="{ name: 'kos.detail', params: { slug: kos.slug } }"
                            target="_blank"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"
                        >
                            Lihat
                        </RouterLink>
                        <button
                            v-if="kos.status !== 'active'"
                            class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="busyId === kos.id"
                            @click="act(kos, 'approve')"
                        >
                            Setujui
                        </button>
                        <button
                            v-if="kos.status === 'active'"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50"
                            :disabled="busyId === kos.id"
                            @click="act(kos, 'hide')"
                        >
                            Sembunyikan
                        </button>
                        <button
                            v-if="['pending', 'active'].includes(kos.status)"
                            class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50 disabled:opacity-50"
                            :disabled="busyId === kos.id"
                            @click="act(kos, 'reject')"
                        >
                            Tolak
                        </button>
                    </div>
                </div>
            </div>

            <Pagination :meta="meta" @change="load" />
        </div>

        <EmptyState v-else icon="🏠" title="Tidak ada kos" description="Belum ada kos dengan status ini." />
    </div>
</template>
