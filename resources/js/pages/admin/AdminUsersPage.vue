<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import { useSeo } from '../../composables/useSeo';
import AdminNav from '../../components/AdminNav.vue';
import Pagination from '../../components/Pagination.vue';
import EmptyState from '../../components/EmptyState.vue';
import AppIcon from '../../components/AppIcon.vue';

useSeo({ title: 'Kelola Pengguna' });

const auth = useAuthStore();
const toast = useToastStore();
const users = ref([]);
const meta = ref(null);
const loading = ref(true);
const filters = ref({ q: '', role: '' });
const busyId = ref(null);

async function load(page = 1) {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/users', {
            params: { q: filters.value.q || undefined, role: filters.value.role || undefined, page },
        });
        users.value = data.data;
        meta.value = data;
    } finally {
        loading.value = false;
    }
}

async function toggle(user) {
    busyId.value = user.id;
    try {
        const { data } = await api.post(`/admin/users/${user.id}/toggle-active`);
        toast.success(data.message);
        user.is_active = data.is_active;
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gagal mengubah status.');
    } finally {
        busyId.value = null;
    }
}

onMounted(() => load());
</script>

<template>
    <div class="mx-auto max-w-5xl px-4 py-8">
        <h1 class="text-xl font-bold tracking-tight text-slate-900">Kelola Pengguna</h1>
        <div class="mt-4">
            <AdminNav />
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <div class="relative min-w-52 flex-1">
                <AppIcon name="search" :size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                <input v-model="filters.q" type="search" placeholder="Cari nama/email…" class="input pl-9" @change="load()" />
            </div>
            <select v-model="filters.role" class="input w-full sm:w-44" @change="load()">
                <option value="">Semua peran</option>
                <option value="student">Mahasiswa</option>
                <option value="owner">Pemilik</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div v-if="loading" class="mt-4 space-y-2">
            <div v-for="n in 5" :key="n" class="h-14 animate-pulse rounded-xl bg-slate-200"></div>
        </div>

        <div v-else-if="users.length" class="card mt-4 divide-y divide-slate-100 overflow-hidden p-0">
            <div v-for="user in users" :key="user.id" class="flex items-center justify-between gap-3 px-4 py-3">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">
                        {{ user.name?.charAt(0).toUpperCase() }}
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="truncate text-sm font-medium text-slate-800">{{ user.name }}</p>
                            <span class="badge bg-slate-100 capitalize text-slate-600">{{ user.role }}</span>
                            <span v-if="!user.is_active" class="badge bg-rose-100 text-rose-700">Nonaktif</span>
                            <span
                                v-if="user.role === 'owner'"
                                class="badge"
                                :class="user.is_verified ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                            >
                                {{ user.is_verified ? 'terverifikasi' : 'belum' }}
                            </span>
                        </div>
                        <p class="truncate text-xs text-slate-500">{{ user.email }} · {{ user.phone || 'tanpa nomor' }} · {{ user.kos_count }} kos</p>
                    </div>
                </div>
                <button
                    class="btn shrink-0"
                    :class="user.is_active ? 'border border-rose-200 bg-white text-rose-600 hover:bg-rose-50' : 'border border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50'"
                    :disabled="busyId === user.id || user.id === auth.user?.id"
                    @click="toggle(user)"
                >
                    <AppIcon :name="user.is_active ? 'eye-off' : 'check'" :size="15" />
                    {{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
            </div>
        </div>

        <EmptyState v-else icon="user" title="Tidak ada pengguna" description="Tidak ada pengguna yang cocok dengan filter ini." />

        <div v-if="meta" class="mt-4">
            <Pagination :meta="meta" @change="load" />
        </div>
    </div>
</template>
