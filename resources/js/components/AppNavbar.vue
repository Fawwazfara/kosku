<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import AppIcon from './AppIcon.vue';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const open = ref(false);
const menu = ref(false);

const navItems = computed(() => {
    const items = [{ label: 'Cari Kos', to: { name: 'home' } }];
    if (auth.isLoggedIn) items.push({ label: 'Favorit', to: { name: 'favorites' } });
    if (auth.isOwner || auth.isAdmin) items.push({ label: 'Dashboard', to: { name: 'owner.dashboard' } });
    if (auth.isAdmin) items.push({ label: 'Admin', to: { name: 'admin.dashboard' } });
    return items;
});

watch(() => route.fullPath, () => {
    open.value = false;
    menu.value = false;
});

async function logout() {
    await auth.logout();
    menu.value = false;
    open.value = false;
    router.push({ name: 'home' });
}
</script>

<template>
    <header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/85 backdrop-blur-md">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4">
            <RouterLink :to="{ name: 'home' }" class="flex items-center gap-2.5 font-extrabold tracking-tight text-slate-900">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-white shadow-soft">
                    <AppIcon name="home" :size="19" />
                </span>
                <span class="text-lg">KosKu</span>
            </RouterLink>

            <nav class="hidden items-center gap-1 md:flex">
                <RouterLink
                    v-for="item in navItems"
                    :key="item.label"
                    :to="item.to"
                    class="rounded-lg px-3 py-2 text-sm font-medium transition"
                    :class="route.name === item.to.name ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                >
                    {{ item.label }}
                </RouterLink>
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                <template v-if="auth.isLoggedIn">
                    <div class="relative">
                        <button
                            class="flex items-center gap-2 rounded-full border border-slate-200 py-1 pl-1 pr-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                            @click="menu = !menu"
                        >
                            <span class="grid h-7 w-7 place-items-center rounded-full bg-brand-600 text-xs font-bold text-white">
                                {{ auth.initials }}
                            </span>
                            <span class="max-w-[9rem] truncate">{{ auth.user.name }}</span>
                            <AppIcon name="chevron-down" :size="15" class="text-slate-400" />
                        </button>
                        <div
                            v-if="menu"
                            class="absolute right-0 mt-2 w-60 overflow-hidden rounded-2xl border border-slate-200 bg-white py-1.5 shadow-card"
                            @mouseleave="menu = false"
                        >
                            <div class="border-b border-slate-100 px-4 py-3">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ auth.user.name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ auth.user.email }}</p>
                                <span class="badge mt-1.5 bg-slate-100 capitalize text-slate-600">
                                    {{ auth.user.role || 'belum memilih peran' }}
                                    <template v-if="auth.ownerPending"> · menunggu verifikasi</template>
                                </span>
                            </div>
                            <RouterLink v-if="auth.isOwner || auth.isAdmin" :to="{ name: 'owner.dashboard' }" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                                <AppIcon name="dashboard" :size="16" /> Dashboard
                            </RouterLink>
                            <RouterLink v-if="auth.isAdmin" :to="{ name: 'admin.dashboard' }" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                                <AppIcon name="shield" :size="16" /> Panel Admin
                            </RouterLink>
                            <RouterLink :to="{ name: 'favorites' }" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                                <AppIcon name="heart" :size="16" /> Favoritku
                            </RouterLink>
                            <button class="mt-1 flex w-full items-center gap-2 border-t border-slate-100 px-4 py-2 pt-2.5 text-left text-sm font-medium text-rose-600 hover:bg-rose-50" @click="logout">
                                <AppIcon name="logout" :size="16" /> Keluar
                            </button>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <RouterLink :to="{ name: 'login' }" class="btn btn-ghost">Masuk</RouterLink>
                    <RouterLink :to="{ name: 'register' }" class="btn btn-primary">Daftar</RouterLink>
                </template>
            </div>

            <button class="grid h-10 w-10 place-items-center rounded-lg text-slate-600 hover:bg-slate-100 md:hidden" aria-label="Menu" @click="open = !open">
                <AppIcon :name="open ? 'x' : 'menu'" :size="22" />
            </button>
        </div>

        <div v-if="open" class="border-t border-slate-200 bg-white px-4 py-3 md:hidden">
            <div class="flex flex-col gap-1">
                <RouterLink v-for="item in navItems" :key="item.label" :to="item.to" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                    {{ item.label }}
                </RouterLink>

                <template v-if="auth.isLoggedIn">
                    <div class="mt-1 flex items-center gap-2 border-t border-slate-100 px-3 py-3">
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-600 text-xs font-bold text-white">{{ auth.initials }}</span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ auth.user.name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ auth.user.email }}</p>
                        </div>
                    </div>
                    <button class="flex items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-rose-600 hover:bg-rose-50" @click="logout">
                        <AppIcon name="logout" :size="16" /> Keluar
                    </button>
                </template>
                <template v-else>
                    <RouterLink :to="{ name: 'login' }" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Masuk</RouterLink>
                    <RouterLink :to="{ name: 'register' }" class="btn btn-primary mt-1">Daftar</RouterLink>
                </template>
            </div>
        </div>
    </header>
</template>
