<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import { useSeo } from '../../composables/useSeo';
import AdminNav from '../../components/AdminNav.vue';
import EmptyState from '../../components/EmptyState.vue';
import AppIcon from '../../components/AppIcon.vue';

useSeo({ title: 'Verifikasi Pemilik' });

const toast = useToastStore();
const owners = ref([]);
const loading = ref(true);
const filter = ref('pending');
const busyId = ref(null);

const options = [
    { value: 'pending', label: 'Menunggu' },
    { value: 'verified', label: 'Terverifikasi' },
    { value: 'all', label: 'Semua' },
];

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
        <h1 class="text-xl font-bold tracking-tight text-slate-900">Verifikasi Pemilik Kos</h1>
        <div class="mt-4">
            <AdminNav />
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <button
                v-for="option in options"
                :key="option.value"
                class="chip"
                :class="filter === option.value ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-brand-300'"
                @click="filter = option.value; load()"
            >
                {{ option.label }}
            </button>
        </div>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="n in 3" :key="n" class="h-24 animate-pulse rounded-2xl bg-slate-200"></div>
        </div>

        <div v-else-if="owners.length" class="mt-4 space-y-3">
            <div v-for="owner in owners" :key="owner.id" class="card p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-50 text-sm font-bold text-brand-700">
                            {{ owner.name?.charAt(0).toUpperCase() }}
                        </span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-semibold text-slate-800">{{ owner.name }}</h2>
                                <span class="badge" :class="owner.is_verified ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                                    {{ owner.is_verified ? 'Terverifikasi' : 'Menunggu' }}
                                </span>
                                <span v-if="!owner.is_active" class="badge bg-rose-100 text-rose-700">Nonaktif</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">{{ owner.email }} · {{ owner.phone || 'tanpa nomor' }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ owner.kos_count }} kos ({{ owner.active_kos_count }} aktif):
                                <span v-if="owner.kos.length">{{ owner.kos.map((k) => k.name).join(', ') }}</span>
                                <span v-else class="text-slate-400">belum ada</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button v-if="!owner.is_verified" class="btn bg-emerald-600 text-white shadow-soft hover:bg-emerald-700" :disabled="busyId === owner.id" @click="verify(owner)">
                            <AppIcon name="check" :size="15" /> Verifikasi
                        </button>
                        <button class="btn border border-rose-200 bg-white text-rose-600 hover:bg-rose-50" :disabled="busyId === owner.id" @click="reject(owner)">
                            <AppIcon name="x" :size="15" /> Tolak
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <EmptyState v-else icon="shield" title="Tidak ada pemilik" description="Belum ada pemilik kos yang cocok dengan filter ini." />
    </div>
</template>
