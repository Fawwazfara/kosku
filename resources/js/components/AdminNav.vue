<script setup>
import { useRoute } from 'vue-router';
import AppIcon from './AppIcon.vue';

const route = useRoute();

const links = [
    { name: 'admin.dashboard', label: 'Ringkasan', icon: 'dashboard' },
    { name: 'admin.owners', label: 'Pemilik', icon: 'shield' },
    { name: 'admin.kos', label: 'Kos', icon: 'home' },
    { name: 'admin.reviews', label: 'Ulasan', icon: 'star' },
    { name: 'admin.facilities', label: 'Fasilitas', icon: 'sparkles' },
    { name: 'admin.users', label: 'Pengguna', icon: 'user' },
];
</script>

<template>
    <nav class="flex gap-1 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-1 shadow-soft">
        <RouterLink
            v-for="link in links"
            :key="link.name"
            :to="{ name: link.name }"
            class="flex items-center gap-1.5 whitespace-nowrap rounded-xl px-3 py-2 text-sm font-medium transition"
            :class="route.name === link.name ? 'bg-brand-600 text-white shadow-soft' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
        >
            <AppIcon :name="link.icon" :size="16" />
            {{ link.label }}
        </RouterLink>
    </nav>
</template>
