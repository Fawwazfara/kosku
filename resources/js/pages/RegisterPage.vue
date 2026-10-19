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

const roleOptions = [
    { value: 'student', label: 'Mahasiswa', hint: 'Cari & ulas kos', icon: 'sparkles' },
    { value: 'owner', label: 'Pemilik Kos', hint: 'Kelola listing kos', icon: 'home' },
];

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
    <div class="mx-auto max-w-md px-4 py-12">
        <div class="card p-6 sm:p-7">
            <h1 class="text-xl font-bold tracking-tight text-slate-900">Buat akun KosKu</h1>
            <p class="mt-1 text-sm text-slate-500">Gratis. Pilih peran, lalu isi data dirimu.</p>

            <div class="mt-5 grid grid-cols-2 gap-2">
                <button
                    v-for="option in roleOptions"
                    :key="option.value"
                    type="button"
                    class="rounded-2xl border p-3 text-left transition"
                    :class="form.role === option.value ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-slate-200 hover:border-brand-300'"
                    @click="form.role = option.value"
                >
                    <span class="grid h-8 w-8 place-items-center rounded-lg" :class="form.role === option.value ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600'">
                        <AppIcon :name="option.icon" :size="17" />
                    </span>
                    <p class="mt-2 text-sm font-semibold text-slate-800">{{ option.label }}</p>
                    <p class="text-xs text-slate-500">{{ option.hint }}</p>
                </button>
            </div>

            <form class="mt-4 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="label" for="name">Nama lengkap</label>
                    <input id="name" v-model="form.name" type="text" required class="input" placeholder="Nama kamu" />
                    <p v-if="fieldError('name')" class="mt-1 text-xs text-rose-600">{{ fieldError('name') }}</p>
                </div>
                <div>
                    <label class="label" for="reg-email">Email</label>
                    <input id="reg-email" v-model="form.email" type="email" required class="input" placeholder="nama@email.com" />
                    <p v-if="fieldError('email')" class="mt-1 text-xs text-rose-600">{{ fieldError('email') }}</p>
                </div>
                <div>
                    <label class="label" for="phone">
                        Nomor WhatsApp <span class="font-normal text-slate-400">(opsional untuk mahasiswa)</span>
                    </label>
                    <input id="phone" v-model="form.phone" type="tel" placeholder="08xxxxxxxxxx" class="input" />
                    <p v-if="fieldError('phone')" class="mt-1 text-xs text-rose-600">{{ fieldError('phone') }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="reg-password">Password</label>
                        <input id="reg-password" v-model="form.password" type="password" required minlength="8" class="input" placeholder="Min. 8 karakter" />
                    </div>
                    <div>
                        <label class="label" for="reg-password2">Ulangi</label>
                        <input id="reg-password2" v-model="form.password_confirmation" type="password" required class="input" placeholder="Ulangi password" />
                    </div>
                </div>
                <p v-if="fieldError('password')" class="text-xs text-rose-600">{{ fieldError('password') }}</p>

                <p v-if="form.role === 'owner'" class="flex items-start gap-2 rounded-xl bg-amber-50 px-3 py-2.5 text-xs text-amber-800">
                    <AppIcon name="info" :size="15" class="mt-0.5" />
                    <span>Akun pemilik baru berstatus <strong>menunggu verifikasi</strong> admin sebelum kos tampil di pencarian.</span>
                </p>

                <button type="submit" class="btn btn-primary w-full" :disabled="loading">
                    {{ loading ? 'Memproses…' : 'Daftar' }}
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-600">
                Sudah punya akun?
                <RouterLink :to="{ name: 'login' }" class="link">Masuk</RouterLink>
            </p>
        </div>
    </div>
</template>
