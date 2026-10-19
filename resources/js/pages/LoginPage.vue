<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useToastStore } from '../stores/toast';
import { useSeo } from '../composables/useSeo';
import AppIcon from '../components/AppIcon.vue';

const auth = useAuthStore();
const toast = useToastStore();
const route = useRoute();
const router = useRouter();

useSeo({ title: 'Masuk' });

const form = ref({ email: '', password: '' });
const loading = ref(false);
const error = ref('');
const showPassword = ref(false);

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
    <div class="mx-auto grid max-w-5xl items-center gap-10 px-4 py-12 lg:grid-cols-2 lg:py-16">
        <div class="hidden lg:block">
            <span class="badge bg-brand-50 text-brand-700">
                <AppIcon name="sparkles" :size="14" /> Cari kos tanpa keliling gang
            </span>
            <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">
                Satu akun untuk cari, simpan, dan ulas kos.
            </h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-500">
                Masuk untuk menyimpan kos favorit, menulis ulasan jujur, dan menghubungi pemilik langsung lewat WhatsApp.
            </p>
            <ul class="mt-6 space-y-3 text-sm text-slate-600">
                <li v-for="item in ['Sisa kamar real-time', 'Filter harga & fasilitas', 'Ulasan dari mahasiswa asli']" :key="item" class="flex items-center gap-2">
                    <span class="grid h-6 w-6 place-items-center rounded-full bg-brand-50 text-brand-700"><AppIcon name="check" :size="14" /></span>
                    {{ item }}
                </li>
            </ul>
        </div>

        <div class="mx-auto w-full max-w-md">
            <div class="card p-6 sm:p-7">
                <h1 class="text-xl font-bold tracking-tight text-slate-900">Masuk ke KosKu</h1>
                <p class="mt-1 text-sm text-slate-500">Selamat datang kembali.</p>

                <form class="mt-5 space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="label" for="email">Email</label>
                        <input id="email" v-model="form.email" type="email" required autocomplete="email" class="input" placeholder="nama@email.com" />
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="label" for="password">Password</label>
                            <RouterLink :to="{ name: 'password.request' }" class="text-xs font-medium text-brand-700 hover:underline">Lupa password?</RouterLink>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                class="input pr-11"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                class="absolute right-2 top-1/2 grid h-8 w-8 -translate-y-1/2 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                @click="showPassword = !showPassword"
                            >
                                <AppIcon name="eye" :size="18" />
                            </button>
                        </div>
                    </div>

                    <p v-if="error" class="flex items-start gap-2 rounded-xl bg-rose-50 px-3 py-2.5 text-sm text-rose-700">
                        <AppIcon name="alert" :size="16" class="mt-0.5" />
                        <span>{{ error }}</span>
                    </p>

                    <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                        {{ loading ? 'Memproses…' : 'Masuk' }}
                    </button>
                </form>

                <p class="mt-5 text-center text-sm text-slate-600">
                    Belum punya akun?
                    <RouterLink :to="{ name: 'register' }" class="link">Daftar gratis</RouterLink>
                </p>
            </div>

            <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white/60 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Akun demo · klik untuk isi otomatis</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button
                        v-for="account in demoAccounts"
                        :key="account.email"
                        type="button"
                        class="chip border-slate-200 text-slate-600 hover:border-brand-300 hover:text-brand-700"
                        @click="fill(account)"
                    >
                        {{ account.label }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
