<script setup>
import { useSeo } from '../composables/useSeo';

useSeo({ title: 'Halaman tidak ditemukan' });
</script>

<template>
    <div class="mx-auto flex max-w-lg flex-col items-center px-4 py-24 text-center">
        <span class="text-6xl">🧭</span>
        <h1 class="mt-4 text-2xl font-bold text-slate-900">404 — Halaman tidak ditemukan</h1>
        <p class="mt-2 text-sm text-slate-500">Tautan yang kamu buka tidak tersedia atau sudah dipindahkan.</p>
        <RouterLink :to="{ name: 'home' }" class="mt-6 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
            Kembali ke beranda
        </RouterLink>
    </div>
</template>
