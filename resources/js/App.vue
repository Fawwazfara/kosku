<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from './stores/auth';
import AppNavbar from './components/AppNavbar.vue';
import AppFooter from './components/AppFooter.vue';
import ToastStack from './components/ToastStack.vue';

const auth = useAuthStore();

onMounted(() => {
    if (!auth.booted) auth.fetchUser();
});
</script>

<template>
    <div class="flex min-h-screen flex-col">
        <AppNavbar />
        <main class="flex-1">
            <RouterView />
        </main>
        <AppFooter />
        <ToastStack />
    </div>
</template>
