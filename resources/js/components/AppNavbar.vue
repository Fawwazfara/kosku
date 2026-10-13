<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const open = ref(false);
const menu = ref(false);

const homeLink = computed(() => ({ name: 'home' }));

async function logout() {
    await auth.logout();
    menu.value = false;
    open.value = false;
    router.push({ name: 'home' });
}
</script>

<template>
    <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
            <RouterLink :to="homeLink" class="flex items-center gap-2 text-xl font-bold text-brand-700">
                <span class="text-2xl">🏠</span> KosKu
            </RouterLink>

            <nav class="hidden items-center gap-1 md:flex">
                <RouterLink :to="homeLink" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                    Cari Kos
                </RouterLink>
                <RouterLink
                    v-if="auth.isLoggedIn"
                    :to="{ name: 'favorites' }"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                >
                    Favorit
                </RouterLink>
                <RouterLink
                    v-if="auth.isOwner || auth.isAdmin"
                    :to="{ name: 'owner.dashboard' }"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                >
                    Dashboard
                </RouterLink>
                <RouterLink
                    v-if="auth.isAdmin"
                    :to="{ name: 'admin.dashboard' }"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                >
                    Admin
                </RouterLink>
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                <template v-if="auth.isLoggedIn">
                    <div class="relative">
                        <button
                            class="flex items-center gap-2 rounded-full border border-slate-200 py-1.5 pl-1.5 pr-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            @click="menu = !menu"
                        >
                            <span class="grid h-7 w-7 place-items-center rounded-full bg-brand-600 text-xs font-bold text-white">
                                {{ auth.user.name.charAt(0).toUpperCase() }}
                            </span>
                            <span class="max-w-[9rem] truncate">{{ auth.user.name }}</span>
                        </button>
                        <div
                            v-if="menu"
                            class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg"
                            @mouseleave="menu = false"
                        >
                            <div class="border-b border-slate-100 px-4 py-2">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ auth.user.name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ auth.user.email }}</p>
                                <span class="mt-1 inline-block rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium capitalize text-slate-600">
                                    {{ auth.user.role }}
                                    <template v-if="auth.ownerPending"> · menunggu verifikasi</template>
                                </span>
                            </div>
                            <button class="block w-full px-4 py-2 text-left text-sm text-rose-600 hover:bg-rose-50" @click="logout">
                                Keluar
                            </button>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <RouterLink :to="{ name: 'login' }" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                        Masuk
                    </RouterLink>
                    <RouterLink
                        :to="{ name: 'register' }"
                        class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700"
                    >
                        Daftar
                    </RouterLink>
                </template>
            </div>

            <button class="grid h-10 w-10 place-items-center rounded-lg text-slate-600 hover:bg-slate-100 md:hidden" @click="open = !open">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <div v-if="open" class="border-t border-slate-200 bg-white px-4 py-3 md:hidden">
            <div class="flex flex-col gap-1">
                <RouterLink :to="homeLink" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100" @click="open = false">
                    Cari Kos
                </RouterLink>
                <template v-if="auth.isLoggedIn">
                    <RouterLink :to="{ name: 'favorites' }" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100" @click="open = false">
                        Favorit
                    </RouterLink>
                    <RouterLink v-if="auth.isOwner || auth.isAdmin" :to="{ name: 'owner.dashboard' }" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100" @click="open = false">
                        Dashboard Pemilik
                    </RouterLink>
                    <RouterLink v-if="auth.isAdmin" :to="{ name: 'admin.dashboard' }" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100" @click="open = false">
                        Panel Admin
                    </RouterLink>
                    <button class="rounded-lg px-3 py-2 text-left text-sm font-medium text-rose-600 hover:bg-rose-50" @click="logout">Keluar</button>
                </template>
                <template v-else>
                    <RouterLink :to="{ name: 'login' }" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100" @click="open = false">
                        Masuk
                    </RouterLink>
                    <RouterLink :to="{ name: 'register' }" class="rounded-lg bg-brand-600 px-3 py-2 text-center text-sm font-semibold text-white" @click="open = false">
                        Daftar
                    </RouterLink>
                </template>
            </div>
        </div>
    </header>
</template>
