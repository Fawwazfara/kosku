<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useToastStore } from '../stores/toast';
import { useSeo } from '../composables/useSeo';

const auth = useAuthStore();
const toast = useToastStore();
const router = useRouter();

useSeo({ title: 'Daftar' });

const form = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    role: 'student',
});

const loading = ref(false);
const errors = ref({});

async function submit() {
    loading.value = true;
    errors.value = {};
    try {
        const { message } = await auth.register(form.value);
        toast.success(message);
        router.push(auth.isOwner ? { name: 'owner.dashboard' } : { name: 'home' });
    } catch (e) {
        errors.value = e.response?.data?.errors || {};
        if (!Object.keys(errors.value).length) {
            toast.error(e.response?.data?.message || 'Gagal mendaftar.');
        }
    } finally {
        loading.value = false;
    }
}

function fieldError(field) {
    return errors.value[field]?.[0];
}
</script>

<template>
    <div class="mx-auto max-w-md px-4 py-10">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-xl font-bold text-slate-900">Daftar Akun KosKu</h1>
            <p class="mt-1 text-sm text-slate-500">Gratis. Pilih peranmu di bawah.</p>

            <div class="mt-4 grid grid-cols-2 gap-2">
                <button
                    type="button"
                    class="rounded-xl border-2 p-3 text-left"
                    :class="form.role === 'student' ? 'border-brand-600 bg-brand-50' : 'border-slate-200'"
                    @click="form.role = 'student'"
                >
                    <span class="text-lg">🎓</span>
                    <p class="mt-1 text-sm font-semibold text-slate-800">Mahasiswa</p>
                    <p class="text-xs text-slate-500">Cari & ulas kos</p>
                </button>
                <button
                    type="button"
                    class="rounded-xl border-2 p-3 text-left"
                    :class="form.role === 'owner' ? 'border-brand-600 bg-brand-50' : 'border-slate-200'"
                    @click="form.role = 'owner'"
                >
                    <span class="text-lg">🔑</span>
                    <p class="mt-1 text-sm font-semibold text-slate-800">Pemilik Kos</p>
                    <p class="text-xs text-slate-500">Kelola listing kos</p>
                </button>
            </div>

            <form class="mt-4 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nama lengkap</label>
                    <input v-model="form.name" type="text" required class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500" />
                    <p v-if="fieldError('name')" class="mt-1 text-xs text-rose-600">{{ fieldError('name') }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input v-model="form.email" type="email" required class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500" />
                    <p v-if="fieldError('email')" class="mt-1 text-xs text-rose-600">{{ fieldError('email') }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Nomor WhatsApp <span class="font-normal text-slate-400">(opsional untuk mahasiswa)</span>
                    </label>
                    <input
                        v-model="form.phone"
                        type="tel"
                        placeholder="08xxxxxxxxxx"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500"
                    />
                    <p v-if="fieldError('phone')" class="mt-1 text-xs text-rose-600">{{ fieldError('phone') }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                        <input v-model="form.password" type="password" required minlength="8" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Ulangi password</label>
                        <input v-model="form.password_confirmation" type="password" required class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500" />
                    </div>
                </div>
                <p v-if="fieldError('password')" class="text-xs text-rose-600">{{ fieldError('password') }}</p>

                <p v-if="form.role === 'owner'" class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    Akun pemilik baru berstatus <strong>menunggu verifikasi</strong> admin sebelum kos tampil di pencarian.
                </p>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-brand-600 py-3 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                    :disabled="loading"
                >
                    {{ loading ? 'Memproses…' : 'Daftar' }}
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-slate-600">
                Sudah punya akun?
                <RouterLink :to="{ name: 'login' }" class="font-semibold text-brand-700 hover:underline">Masuk</RouterLink>
            </p>
        </div>
    </div>
</template>
