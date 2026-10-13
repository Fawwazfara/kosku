<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useToastStore } from '../stores/toast';
import { useSeo } from '../composables/useSeo';

const auth = useAuthStore();
const toast = useToastStore();
const route = useRoute();
const router = useRouter();

useSeo({ title: 'Masuk' });

const form = ref({ email: '', password: '' });
const loading = ref(false);
const error = ref('');

const demoAccounts = [
    { label: 'Mahasiswa', email: 'mahasiswa@test.com', password: 'password' },
    { label: 'Pemilik Kos', email: 'pemilik@kosku.test', password: 'password' },
    { label: 'Admin', email: 'admin@kosku.test', password: 'password' },
];

async function submit() {
    loading.value = true;
    error.value = '';
    try {
        const { message } = await auth.login(form.value);
        toast.success(message);
        router.push(route.query.redirect || { name: 'home' });
    } catch (e) {
        const errors = e.response?.data?.errors;
        error.value = errors ? Object.values(errors)[0][0] : e.response?.data?.message || 'Gagal masuk.';
    } finally {
        loading.value = false;
    }
}

function fill(account) {
    form.value = { email: account.email, password: account.password };
}
</script>

<template>
    <div class="mx-auto max-w-md px-4 py-10">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-xl font-bold text-slate-900">Masuk ke KosKu</h1>
            <p class="mt-1 text-sm text-slate-500">Simpan kos favorit dan beri ulasan.</p>

            <form class="mt-5 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500"
                    />
                </div>

                <p v-if="error" class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ error }}</p>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-brand-600 py-3 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                    :disabled="loading"
                >
                    {{ loading ? 'Memproses…' : 'Masuk' }}
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-slate-600">
                Belum punya akun?
                <RouterLink :to="{ name: 'register' }" class="font-semibold text-brand-700 hover:underline">Daftar</RouterLink>
            </p>
        </div>

        <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Akun demo (klik untuk isi otomatis)</p>
            <div class="mt-2 flex flex-wrap gap-2">
                <button
                    v-for="account in demoAccounts"
                    :key="account.email"
                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50"
                    @click="fill(account)"
                >
                    {{ account.label }}
                </button>
            </div>
        </div>
    </div>
</template>
