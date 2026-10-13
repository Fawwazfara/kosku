<script setup>
const year = new Date().getFullYear();
</script>

<template>
    <footer class="mt-12 border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8 text-sm text-slate-500">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-base font-bold text-brand-700">🏠 KosKu</p>
                    <p class="mt-1 max-w-md">
                        Mempertemukan mahasiswa perantau dengan pemilik kos. Lihat sisa kamar real-time, hubungi pemilik langsung via WhatsApp.
                    </p>
                </div>
                <div class="text-xs">
                    <p>Peta: © OpenStreetMap contributors · Leaflet</p>
                    <p class="mt-1">© {{ year }} KosKu. Dibuat untuk mahasiswa.</p>
                </div>
            </div>
        </div>
    </footer>
</template>
