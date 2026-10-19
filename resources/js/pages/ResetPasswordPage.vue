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

useSeo({ title: 'Reset Password' });

const token = route.query.token || '';
const email = route.query.email || '';

const form = ref({ password: '', password_confirmation: '' });
const loading = ref(false);
const error = ref('');
const showPassword = ref(false);

async function submit() {
    loading.value = true;
    error.value = '';
    try {
        const { message } = await auth.resetPassword({
            token,
            email,
            password: form.value.password,
            password_confirmation: form.value.password_confirmation,
        });
        toast.success(message);
        router.push({ name: 'login' });
    } catch (e) {
        const errors = e.response?.data?.errors;
        error.value = errors ? Object.values(errors)[0][0] : e.response?.data?.message || 'Gagal mereset password.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="card p-6 sm:p-7">
            <h1 class="text-xl font-bold tracking-tight text-slate-900">Buat password baru</h1>
            <p class="mt-1 text-sm text-slate-500">Masukkan password baru untuk akun <strong>{{ email || 'KosKu' }}</strong>.</p>

            <div v-if="!token || !email" class="mt-5">
                <p class="flex items-start gap-2 rounded-xl bg-rose-50 px-3 py-2.5 text-sm text-rose-700">
                    <AppIcon name="alert" :size="16" class="mt-0.5" />
                    <span>Tautan reset tidak lengkap. Minta tautan baru dari halaman lupa password.</span>
                </p>
                <RouterLink :to="{ name: 'password.request' }" class="link mt-4 inline-block text-sm">Minta tautan baru</RouterLink>
            </div>

            <form v-else class="mt-5 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="label" for="new-password">Password baru</label>
                    <div class="relative">
                        <input
                            id="new-password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            class="input pr-11"
                            placeholder="Min. 8 karakter"
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
                <div>
                    <label class="label" for="confirm-password">Ulangi password baru</label>
                    <input id="confirm-password" v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" required autocomplete="new-password" class="input" placeholder="Ulangi password" />
                </div>

                <p v-if="error" class="flex items-start gap-2 rounded-xl bg-rose-50 px-3 py-2.5 text-sm text-rose-700">
                    <AppIcon name="alert" :size="16" class="mt-0.5" />
                    <span>{{ error }}</span>
                </p>

                <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                    {{ loading ? 'Menyimpan…' : 'Simpan password baru' }}
                </button>
            </form>
        </div>
    </div>
</template>
