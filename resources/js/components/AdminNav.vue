<script setup>
const links = [
    { name: 'admin.dashboard', label: 'Ringkasan' },
    { name: 'admin.owners', label: 'Pemilik' },
    { name: 'admin.kos', label: 'Kos' },
    { name: 'admin.reviews', label: 'Ulasan' },
    { name: 'admin.facilities', label: 'Fasilitas' },
    { name: 'admin.users', label: 'Pengguna' },
];
</script>

<template>
    <nav class="flex gap-1 overflow-x-auto border-b border-slate-200 pb-px">
        <RouterLink
            v-for="link in links"
            :key="link.name"
            :to="{ name: link.name }"
            class="whitespace-nowrap rounded-t-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
            active-class="!text-brand-700 !border-b-2 !border-brand-600"
        >
            {{ link.label }}
        </RouterLink>
    </nav>
</template>
