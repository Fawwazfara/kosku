<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import { useSeo } from '../../composables/useSeo';
import AdminNav from '../../components/AdminNav.vue';
import AppIcon from '../../components/AppIcon.vue';
import EmptyState from '../../components/EmptyState.vue';

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
        <h1 class="text-xl font-bold tracking-tight text-slate-900">Master Fasilitas</h1>
        <div class="mt-4">
            <AdminNav />
        </div>

        <form class="card mt-6 flex flex-wrap items-end gap-3 p-4" @submit.prevent="submit">
            <div class="min-w-48 flex-1">
                <label class="label">Nama fasilitas</label>
                <input v-model="form.name" type="text" required class="input" placeholder="cth. WiFi" />
                <p v-if="errors.name" class="mt-1 text-xs text-rose-600">{{ errors.name[0] }}</p>
            </div>
            <div class="w-40">
                <label class="label">Ikon (opsional)</label>
                <input v-model="form.icon" type="text" placeholder="wifi" class="input" />
            </div>
            <button type="submit" :disabled="saving" class="btn btn-primary">
                <AppIcon :name="editingId ? 'check' : 'plus'" :size="15" />
                {{ editingId ? 'Simpan' : 'Tambah' }}
            </button>
            <button v-if="editingId" type="button" class="btn btn-secondary" @click="cancelEdit">Batal</button>
        </form>

        <div v-if="loading" class="mt-4 space-y-2">
            <div v-for="n in 5" :key="n" class="h-12 animate-pulse rounded-xl bg-slate-200"></div>
        </div>

        <div v-else-if="facilities.length" class="card mt-4 divide-y divide-slate-100 overflow-hidden p-0">
            <div v-for="facility in facilities" :key="facility.id" class="flex items-center justify-between gap-3 px-4 py-3">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600">
                        <AppIcon name="sparkles" :size="16" />
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-slate-800">{{ facility.name }}</p>
                        <p class="text-xs text-slate-400">{{ facility.icon || 'tanpa ikon' }} · dipakai {{ facility.kos_count || 0 }} kos</p>
                    </div>
                </div>
                <div class="flex shrink-0 gap-1">
                    <button class="grid h-9 w-9 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-brand-700" title="Edit" @click="startEdit(facility)">
                        <AppIcon name="pencil" :size="16" />
                    </button>
                    <button class="grid h-9 w-9 place-items-center rounded-lg text-slate-500 transition hover:bg-rose-50 hover:text-rose-600" title="Hapus" @click="remove(facility)">
                        <AppIcon name="trash" :size="16" />
                    </button>
                </div>
            </div>
        </div>

        <EmptyState v-else icon="sparkles" title="Belum ada fasilitas" description="Tambahkan fasilitas master pertama untuk dipakai kos." />
    </div>
</template>
