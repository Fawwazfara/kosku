<script setup>
const props = defineProps({
    modelValue: { type: Number, default: 0 },
    readonly: { type: Boolean, default: true },
    size: { type: String, default: 'md' },
});

const emit = defineEmits(['update:modelValue']);

const sizeClass = {
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
    lg: 'h-7 w-7',
}[props.size] || 'h-5 w-5';

function set(value) {
    if (!props.readonly) emit('update:modelValue', value);
}
</script>

<template>
    <div class="flex items-center gap-0.5">
        <button
            v-for="star in 5"
            :key="star"
            type="button"
            :disabled="readonly"
            class="transition"
            :class="readonly ? 'cursor-default' : 'cursor-pointer hover:scale-110'"
            @click="set(star)"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                :class="sizeClass"
                viewBox="0 0 20 20"
                :fill="star <= modelValue ? '#f59e0b' : '#e2e8f0'"
            >
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.54 1.118l-3.366-2.446a1 1 0 00-1.175 0l-3.367 2.446c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.958z" />
            </svg>
        </button>
    </div>
</template>
