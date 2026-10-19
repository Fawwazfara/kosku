<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import { useSeo } from '../../composables/useSeo';
import { rupiah, STATUS_LABELS, STATUS_CLASSES, TYPE_LABELS } from '../../utils/format';
import AdminNav from '../../components/AdminNav.vue';
import Pagination from '../../components/Pagination.vue';
import EmptyState from '../../components/EmptyState.vue';
import AppIcon from '../../components/AppIcon.vue';

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
        <h1 class="text-xl font-bold tracking-tight text-slate-900">Verifikasi & Moderasi Kos</h1>
        <div class="mt-4">
            <AdminNav />
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <button
                v-for="tab in tabs"
                :key="tab.value"
                class="chip"
                :class="status === tab.value ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-brand-300'"
                @click="status = tab.value; load()"
            >
                {{ tab.label }}
            </button>
        </div>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="n in 3" :key="n" class="h-28 animate-pulse rounded-2xl bg-slate-200"></div>
        </div>

        <div v-else-if="kosList.length" class="mt-4 space-y-3">
            <div v-for="kos in kosList" :key="kos.id" class="card p-4">
                <div class="flex flex-wrap items-start gap-4">
                    <div class="h-20 w-28 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                        <img v-if="kos.primary_photo" :src="kos.primary_photo.url" :alt="kos.name" class="h-full w-full object-cover" loading="lazy" />
                        <div v-else class="grid h-full w-full place-items-center text-slate-300"><AppIcon name="image" :size="24" /></div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-semibold text-slate-800">{{ kos.name }}</h2>
                            <span class="badge" :class="STATUS_CLASSES[kos.status]">{{ STATUS_LABELS[kos.status] }}</span>
                            <span class="badge bg-slate-100 text-slate-600">Kos {{ TYPE_LABELS[kos.type] }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            Pemilik: {{ kos.owner?.name || '-' }} · <span class="font-medium text-slate-700">{{ rupiah(kos.price_min) }}/bulan</span> · {{ kos.district }}
                        </p>
                        <p class="mt-0.5 flex items-center gap-1 text-xs text-slate-400">
                            {{ kos.available_rooms }}/{{ kos.total_rooms }} kamar kosong
                            <span class="mx-1">·</span>
                            <AppIcon name="star" :size="12" class="text-amber-500" />
                            {{ kos.rating_avg ? Number(kos.rating_avg).toFixed(1) : '-' }} ({{ kos.reviews_count }})
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <RouterLink :to="{ name: 'kos.detail', params: { slug: kos.slug } }" target="_blank" class="btn btn-secondary">
                            <AppIcon name="external" :size="15" /> Lihat
                        </RouterLink>
                        <button v-if="kos.status !== 'active'" class="btn bg-emerald-600 text-white shadow-soft hover:bg-emerald-700" :disabled="busyId === kos.id" @click="act(kos, 'approve')">
                            <AppIcon name="check" :size="15" /> Setujui
                        </button>
                        <button v-if="kos.status === 'active'" class="btn btn-secondary" :disabled="busyId === kos.id" @click="act(kos, 'hide')">
                            <AppIcon name="eye-off" :size="15" /> Sembunyikan
                        </button>
                        <button
                            v-if="['pending', 'active'].includes(kos.status)"
                            class="btn border border-rose-200 bg-white text-rose-600 hover:bg-rose-50"
                            :disabled="busyId === kos.id"
                            @click="act(kos, 'reject')"
                        >
                            <AppIcon name="x" :size="15" /> Tolak
                        </button>
                    </div>
                </div>
            </div>

            <Pagination :meta="meta" @change="load" />
        </div>

        <EmptyState v-else icon="home" title="Tidak ada kos" description="Belum ada kos dengan status ini." />
    </div>
</template>
