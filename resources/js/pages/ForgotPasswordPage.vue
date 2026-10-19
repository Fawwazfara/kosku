<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useToastStore } from '../stores/toast';
import { useSeo } from '../composables/useSeo';
import AppIcon from '../components/AppIcon.vue';

const auth = useAuthStore();
const toast = useToastStore();

useSeo({ title: 'Lupa Password' });

const email = ref('');
const loading = ref(false);
const error = ref('');
const sent = ref(false);

async function submit() {
    loading.value = true;
    error.value = '';
    try {
        const { message } = await auth.forgotPassword(email.value);
        sent.value = true;
        toast.success(message);
    } catch (e) {
        const errors = e.response?.data?.errors;
        error.value = errors ? Object.values(errors)[0][0] : e.response?.data?.message || 'Gagal mengirim tautan reset.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="card p-6 sm:p-7">
            <h1 class="text-xl font-bold tracking-tight text-slate-900">Lupa password?</h1>
            <p class="mt-1 text-sm text-slate-500">
                Masukkan email akun KosKu-mu. Kami akan mengirim tautan untuk membuat password baru.
            </p>

            <div v-if="sent" class="mt-5 space-y-4">
                <p class="flex items-start gap-2 rounded-xl bg-emerald-50 px-3 py-2.5 text-sm text-emerald-700">
                    <AppIcon name="check" :size="16" class="mt-0.5" />
                    <span>Tautan reset sudah dikirim ke <strong>{{ email }}</strong>. Cek inbox (dan folder spam) lalu ikuti petunjuknya.</span>
                </p>
                <RouterLink :to="{ name: 'login' }" class="link text-sm">Kembali ke halaman masuk</RouterLink>
            </div>

            <form v-else class="mt-5 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="label" for="forgot-email">Email</label>
                    <input id="forgot-email" v-model="email" type="email" required autocomplete="email" class="input" placeholder="nama@email.com" />
                </div>

                <p v-if="error" class="flex items-start gap-2 rounded-xl bg-rose-50 px-3 py-2.5 text-sm text-rose-700">
                    <AppIcon name="alert" :size="16" class="mt-0.5" />
                    <span>{{ error }}</span>
                </p>

                <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                    {{ loading ? 'Mengirim…' : 'Kirim tautan reset' }}
                </button>
            </form>

            <p v-if="!sent" class="mt-5 text-center text-sm text-slate-600">
                Ingat passwordmu?
                <RouterLink :to="{ name: 'login' }" class="link">Masuk</RouterLink>
            </p>
        </div>
    </div>
</template>
