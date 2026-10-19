<script setup>
import { useToastStore } from '../stores/toast';
import AppIcon from './AppIcon.vue';

const toast = useToastStore();

const meta = {
    success: { icon: 'check', ring: 'text-emerald-600', bar: 'bg-emerald-500' },
    error: { icon: 'alert', ring: 'text-rose-600', bar: 'bg-rose-500' },
    info: { icon: 'info', ring: 'text-slate-700', bar: 'bg-slate-500' },
};
</script>

<template>
    <div class="pointer-events-none fixed inset-x-0 bottom-4 z-[1000] flex flex-col items-center gap-2 px-4 sm:items-end sm:px-6">
        <transition-group name="toast">
            <div
                v-for="item in toast.items"
                :key="item.id"
                class="pointer-events-auto relative flex w-full max-w-sm items-start gap-3 overflow-hidden rounded-2xl border border-slate-200 bg-white p-3.5 shadow-card"
                @click="toast.remove(item.id)"
            >
                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-50" :class="meta[item.type].ring">
                    <AppIcon :name="meta[item.type].icon" :size="17" />
                </span>
                <p class="flex-1 pt-1 text-sm font-medium text-slate-700">{{ item.message }}</p>
                <span class="absolute bottom-0 left-0 h-1 w-full" :class="meta[item.type].bar"></span>
            </div>
        </transition-group>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(10px) scale(0.98);
}
</style>
