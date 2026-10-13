<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import { useSeo } from '../../composables/useSeo';
import AdminNav from '../../components/AdminNav.vue';

useSeo({ title: 'Master Fasilitas' });

const toast = useToastStore();
const facilities = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const form = ref({ name: '', icon: '' });
const errors = ref({});

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/facilities');
        facilities.value = data.data;
    } finally {
        loading.value = false;
    }
}

function startEdit(facility) {
    editingId.value = facility.id;
    form.value = { name: facility.name, icon: facility.icon || '' };
}

function cancelEdit() {
    editingId.value = null;
    form.value = { name: '', icon: '' };
    errors.value = {};
}

async function submit() {
    saving.value = true;
    errors.value = {};
    try {
        if (editingId.value) {
            await api.put(`/admin/facilities/${editingId.value}`, form.value);
            toast.success('Fasilitas diperbarui.');
        } else {
            await api.post('/admin/facilities', form.value);
            toast.success('Fasilitas ditambahkan.');
        }
        cancelEdit();
        await load();
    } catch (error) {
        errors.value = error.response?.data?.errors || {};
        if (!Object.keys(errors.value).length) toast.error('Gagal menyimpan fasilitas.');
    } finally {
        saving.value = false;
    }
}

async function remove(facility) {
    if (!confirm(`Hapus fasilitas "${facility.name}"? Fasilitas akan dilepas dari semua kos.`)) return;
    try {
        await api.delete(`/admin/facilities/${facility.id}`);
        toast.success('Fasilitas dihapus.');
        await load();
    } catch {
        toast.error('Gagal menghapus fasilitas.');
    }
}

onMounted(load);
</script>

<template>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <h1 class="text-xl font-bold text-slate-900">Master Fasilitas</h1>
        <div class="mt-4">
            <AdminNav />
        </div>

        <form class="mt-6 flex flex-wrap items-end gap-2 rounded-2xl border border-slate-200 bg-white p-4" @submit.prevent="submit">
            <div class="flex-1">
                <label class="mb-1 block text-sm font-medium text-slate-700">Nama fasilitas</label>
                <input v-model="form.name" type="text" required class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500" />
                <p v-if="errors.name" class="mt-1 text-xs text-rose-600">{{ errors.name[0] }}</p>
            </div>
            <div class="w-40">
                <label class="mb-1 block text-sm font-medium text-slate-700">Ikon (opsional)</label>
                <input v-model="form.icon" type="text" placeholder="wifi" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500" />
            </div>
            <button type="submit" :disabled="saving" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50">
                {{ editingId ? 'Simpan' : 'Tambah' }}
            </button>
            <button v-if="editingId" type="button" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600" @click="cancelEdit">
                Batal
            </button>
        </form>

        <div v-if="loading" class="mt-4 space-y-2">
            <div v-for="n in 5" :key="n" class="h-12 animate-pulse rounded-xl bg-slate-200"></div>
        </div>

        <div v-else class="mt-4 divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div v-for="facility in facilities" :key="facility.id" class="flex items-center justify-between gap-3 px-4 py-3">
                <div>
                    <p class="text-sm font-medium text-slate-800">{{ facility.name }}</p>
                    <p class="text-xs text-slate-400">{{ facility.icon || 'tanpa ikon' }} · dipakai {{ facility.kos_count || 0 }} kos</p>
                </div>
                <div class="flex gap-2">
                    <button class="text-sm font-medium text-brand-700 hover:underline" @click="startEdit(facility)">Edit</button>
                    <button class="text-sm font-medium text-rose-600 hover:underline" @click="remove(facility)">Hapus</button>
                </div>
            </div>
        </div>
    </div>
</template>
