<script setup>
import { useToastStore } from '../stores/toast';

const toast = useToastStore();

const styles = {
    success: 'bg-emerald-600',
    error: 'bg-rose-600',
    info: 'bg-slate-800',
};
</script>

<template>
    <div class="pointer-events-none fixed inset-x-0 top-4 z-[1000] flex flex-col items-center gap-2 px-4">
        <transition-group name="toast">
            <div
                v-for="item in toast.items"
                :key="item.id"
                class="pointer-events-auto w-full max-w-md rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg"
                :class="styles[item.type]"
                @click="toast.remove(item.id)"
            >
                {{ item.message }}
            </div>
        </transition-group>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.25s ease;
}
.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
