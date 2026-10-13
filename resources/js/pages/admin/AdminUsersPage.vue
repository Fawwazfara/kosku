<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toast';
import { useSeo } from '../../composables/useSeo';
import AdminNav from '../../components/AdminNav.vue';
import Pagination from '../../components/Pagination.vue';
import EmptyState from '../../components/EmptyState.vue';

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
        <h1 class="text-xl font-bold text-slate-900">Kelola Pengguna</h1>
        <div class="mt-4">
            <AdminNav />
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <input
                v-model="filters.q"
                type="search"
                placeholder="Cari nama/email…"
                class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-brand-500"
                @change="load()"
            />
            <select v-model="filters.role" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" @change="load()">
                <option value="">Semua peran</option>
                <option value="student">Mahasiswa</option>
                <option value="owner">Pemilik</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div v-if="loading" class="mt-4 space-y-2">
            <div v-for="n in 5" :key="n" class="h-14 animate-pulse rounded-xl bg-slate-200"></div>
        </div>

        <div v-else-if="users.length" class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div v-for="user in users" :key="user.id" class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 last:border-0">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="truncate text-sm font-medium text-slate-800">{{ user.name }}</p>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium capitalize text-slate-600">{{ user.role }}</span>
                        <span v-if="!user.is_active" class="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-700">Nonaktif</span>
                        <span v-if="user.role === 'owner'" class="text-[11px]" :class="user.is_verified ? 'text-emerald-600' : 'text-amber-600'">
                            {{ user.is_verified ? 'terverifikasi' : 'belum' }}
                        </span>
                    </div>
                    <p class="truncate text-xs text-slate-500">{{ user.email }} · {{ user.phone || 'tanpa nomor' }} · {{ user.kos_count }} kos</p>
                </div>
                <button
                    class="shrink-0 rounded-lg border px-3 py-2 text-sm font-medium disabled:opacity-50"
                    :class="user.is_active ? 'border-rose-200 text-rose-600 hover:bg-rose-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50'"
                    :disabled="busyId === user.id || user.id === auth.user?.id"
                    @click="toggle(user)"
                >
                    {{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
            </div>
        </div>

        <EmptyState v-else icon="👥" title="Tidak ada pengguna" description="Tidak ada pengguna yang cocok dengan filter ini." />

        <div v-if="meta" class="mt-4">
            <Pagination :meta="meta" @change="load" />
        </div>
    </div>
</template>
