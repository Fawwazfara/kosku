<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import { useSeo } from '../../composables/useSeo';
import AdminNav from '../../components/AdminNav.vue';
import EmptyState from '../../components/EmptyState.vue';

useSeo({ title: 'Verifikasi Pemilik' });

const toast = useToastStore();
const owners = ref([]);
const loading = ref(true);
const filter = ref('pending');
const busyId = ref(null);

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/owners', { params: { status: filter.value === 'all' ? undefined : filter.value } });
        owners.value = data.owners;
    } finally {
        loading.value = false;
    }
}

async function verify(owner) {
    busyId.value = owner.id;
    try {
        const { data } = await api.post(`/admin/owners/${owner.id}/verify`);
        toast.success(data.message);
        await load();
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gagal memverifikasi.');
    } finally {
        busyId.value = null;
    }
}

async function reject(owner) {
    if (!confirm(`Tolak dan nonaktifkan pemilik "${owner.name}"?`)) return;
    busyId.value = owner.id;
    try {
        const { data } = await api.post(`/admin/owners/${owner.id}/reject`);
        toast.success(data.message);
        await load();
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gagal menolak.');
    } finally {
        busyId.value = null;
    }
}

onMounted(load);
</script>

<template>
    <div class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="text-xl font-bold text-slate-900">Verifikasi Pemilik Kos</h1>
        <div class="mt-4">
            <AdminNav />
        </div>

        <div class="mt-4 flex gap-2">
            <button
                v-for="option in [
                    { value: 'pending', label: 'Menunggu' },
                    { value: 'verified', label: 'Terverifikasi' },
                    { value: 'all', label: 'Semua' },
                ]"
                :key="option.value"
                class="rounded-full border px-3 py-1.5 text-xs font-medium"
                :class="filter === option.value ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 text-slate-600'"
                @click="filter = option.value; load()"
            >
                {{ option.label }}
            </button>
        </div>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="n in 3" :key="n" class="h-24 animate-pulse rounded-2xl bg-slate-200"></div>
        </div>

        <div v-else-if="owners.length" class="mt-4 space-y-3">
            <div v-for="owner in owners" :key="owner.id" class="rounded-2xl border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-slate-800">{{ owner.name }}</h2>
                            <span
                                class="rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="owner.is_verified ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                            >
                                {{ owner.is_verified ? 'Terverifikasi' : 'Menunggu' }}
                            </span>
                            <span v-if="!owner.is_active" class="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-700">
                                Nonaktif
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ owner.email }} · {{ owner.phone || 'tanpa nomor' }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ owner.kos_count }} kos ({{ owner.active_kos_count }} aktif):
                            <span v-if="owner.kos.length">{{ owner.kos.map((k) => k.name).join(', ') }}</span>
                            <span v-else class="text-slate-400">belum ada</span>
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <button
                            v-if="!owner.is_verified"
                            class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="busyId === owner.id"
                            @click="verify(owner)"
                        >
                            Verifikasi
                        </button>
                        <button
                            class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50 disabled:opacity-50"
                            :disabled="busyId === owner.id"
                            @click="reject(owner)"
                        >
                            Tolak
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <EmptyState v-else icon="🔑" title="Tidak ada pemilik" description="Belum ada pemilik kos yang cocok dengan filter ini." />
    </div>
</template>
