<script setup>
import AppIcon from './AppIcon.vue';

const year = new Date().getFullYear();
</script>

<template>
    <footer class="mt-16 border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-10">
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-2.5 font-extrabold tracking-tight text-slate-900">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-white">
                            <AppIcon name="home" :size="19" />
                        </span>
                        <span class="text-lg">KosKu</span>
                    </div>
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-slate-500">
                        Mempertemukan mahasiswa perantau dengan pemilik kos. Lihat sisa kamar real-time dan hubungi pemilik langsung lewat WhatsApp.
                    </p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Jelajahi</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-500">
                        <li><RouterLink :to="{ name: 'home' }" class="hover:text-brand-700">Cari Kos</RouterLink></li>
                        <li><RouterLink :to="{ name: 'register' }" class="hover:text-brand-700">Daftar sebagai pemilik</RouterLink></li>
                    </ul>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Info</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-500">
                        <li class="flex items-center gap-1.5"><AppIcon name="map" :size="15" /> Peta: OpenStreetMap</li>
                        <li class="flex items-center gap-1.5"><AppIcon name="shield" :size="15" /> Data kos terverifikasi admin</li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 border-t border-slate-100 pt-5 text-xs text-slate-400">
                © {{ year }} KosKu. Dibuat untuk mahasiswa perantau.
            </div>
        </div>
    </footer>
</template>
