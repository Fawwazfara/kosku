<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useToastStore } from '../stores/toast';
import { useSeo } from '../composables/useSeo';
import AppIcon from '../components/AppIcon.vue';

const auth = useAuthStore();
const toast = useToastStore();
const router = useRouter();

useSeo({ title: 'Pilih Peran' });

const selected = ref(null);
const loading = ref(false);

const roles = [
    {
        value: 'student',
        title: 'Saya Mahasiswa',
        description: 'Cari kos, simpan favorit, dan beri ulasan untuk membantu mahasiswa lain.',
        icon: 'sparkles',
        points: ['Cari & filter kos', 'Simpan favorit', 'Tulis ulasan'],
    },
    {
        value: 'owner',
        title: 'Saya Pemilik Kos',
        description: 'Kelola listing, perbarui sisa kamar, dan terima pertanyaan lewat WhatsApp.',
        icon: 'home',
        points: ['Kelola banyak kos', 'Atur sisa kamar', 'Unggah sampai 10 foto'],
    },
];

async function confirm() {
    if (!selected.value) {
        toast.error('Pilih salah satu peran dulu.');
        return;
    }

    loading.value = true;
    try {
        const { message } = await auth.chooseRole(selected.value);
        toast.success(message);
        router.push(selected.value === 'owner' ? { name: 'owner.dashboard' } : { name: 'home' });
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gagal menyimpan peran.');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="mx-auto max-w-3xl px-4 py-12">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-brand-600">Satu langkah lagi</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Kamu ingin memakai KosKu sebagai apa?</h1>
            <p class="mx-auto mt-2 max-w-lg text-sm text-slate-500">
                Hai {{ auth.user?.name }}, pilih peran untuk menyesuaikan pengalamanmu. Peran tidak bisa diubah setelah ini.
            </p>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            <button
                v-for="role in roles"
                :key="role.value"
                type="button"
                class="card group relative p-5 text-left transition"
                :class="selected === role.value ? 'ring-2 ring-brand-500' : 'hover:-translate-y-0.5 hover:shadow-card'"
                @click="selected = role.value"
            >
                <span
                    class="grid h-11 w-11 place-items-center rounded-xl transition"
                    :class="selected === role.value ? 'bg-brand-600 text-white' : 'bg-brand-50 text-brand-700'"
                >
                    <AppIcon :name="role.icon" :size="22" />
                </span>
                <h2 class="mt-3 text-base font-semibold text-slate-900">{{ role.title }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ role.description }}</p>
                <ul class="mt-3 space-y-1.5">
                    <li v-for="point in role.points" :key="point" class="flex items-center gap-2 text-xs text-slate-600">
                        <AppIcon name="check" :size="14" class="text-brand-600" />
                        {{ point }}
                    </li>
                </ul>
                <span
                    class="absolute right-4 top-4 grid h-5 w-5 place-items-center rounded-full border transition"
                    :class="selected === role.value ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-300 text-transparent'"
                >
                    <AppIcon name="check" :size="13" />
                </span>
            </button>
        </div>

        <div class="mt-8 flex flex-col items-center gap-3">
            <button class="btn btn-primary w-full sm:w-auto sm:px-10" :disabled="loading || !selected" @click="confirm">
                {{ loading ? 'Menyimpan…' : 'Lanjutkan' }}
                <AppIcon name="arrow-right" :size="18" />
            </button>
            <p class="text-xs text-slate-400">Akun pemilik baru perlu diverifikasi admin sebelum kos tampil publik.</p>
        </div>
    </div>
</template>
