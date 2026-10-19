<script setup>
import { useSeo } from '../composables/useSeo';
import AppIcon from '../components/AppIcon.vue';

useSeo({ title: 'Halaman tidak ditemukan' });
</script>

<template>
    <div class="mx-auto flex max-w-lg flex-col items-center px-4 py-24 text-center">
        <span class="grid h-20 w-20 place-items-center rounded-3xl bg-brand-50 text-brand-600">
            <AppIcon name="map" :size="36" />
        </span>
        <p class="mt-6 text-5xl font-extrabold tracking-tight text-slate-900">404</p>
        <h1 class="mt-2 text-xl font-bold text-slate-900">Halaman tidak ditemukan</h1>
        <p class="mt-2 text-sm text-slate-500">Tautan yang kamu buka tidak tersedia atau sudah dipindahkan.</p>
        <RouterLink :to="{ name: 'home' }" class="btn btn-primary mt-6">
            <AppIcon name="home" :size="16" /> Kembali ke beranda
        </RouterLink>
    </div>
</template>
