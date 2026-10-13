<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import { useSeo } from '../../composables/useSeo';
import { formatDate } from '../../utils/format';
import AdminNav from '../../components/AdminNav.vue';
import Pagination from '../../components/Pagination.vue';
import EmptyState from '../../components/EmptyState.vue';

useSeo({ title: 'Moderasi Ulasan' });

const toast = useToastStore();
const reviews = ref([]);
const meta = ref(null);
const loading = ref(true);
const filter = ref('all');
const busyId = ref(null);

async function load(page = 1) {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/reviews', {
            params: { filter: filter.value === 'all' ? undefined : filter.value, page },
        });
        reviews.value = data.data;
        meta.value = data.meta;
    } finally {
        loading.value = false;
    }
}

async function act(review, action) {
    busyId.value = review.id;
    try {
        if (action === 'delete') {
            if (!confirm('Hapus ulasan ini permanen?')) {
                busyId.value = null;
                return;
            }
            await api.delete(`/admin/reviews/${review.id}`);
            toast.success('Ulasan dihapus.');
        } else {
            await api.post(`/admin/reviews/${review.id}/${action}`);
            toast.success(action === 'hide' ? 'Ulasan disembunyikan.' : 'Ulasan ditampilkan.');
        }
        await load(meta.value?.current_page || 1);
    } catch (error) {
        toast.error(error.response?.data?.message || 'Aksi gagal.');
    } finally {
        busyId.value = null;
    }
}

onMounted(() => load());
</script>

<template>
    <div class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="text-xl font-bold text-slate-900">Moderasi Ulasan</h1>
        <div class="mt-4">
            <AdminNav />
        </div>

        <div class="mt-4 flex gap-2">
            <button
                v-for="option in [
                    { value: 'all', label: 'Semua' },
                    { value: 'visible', label: 'Tampil' },
                    { value: 'hidden', label: 'Disembunyikan' },
                ]"
                :key="option.value"
                class="rounded-full border px-3 py-1.5 text-xs font-medium"
                :class="filter === option.value ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 text-slate-600'"
                @click="filter = option.value; load()"
            >
                {{ option.label }}
            </button>
        </div>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="n in 4" :key="n" class="h-20 animate-pulse rounded-2xl bg-slate-200"></div>
        </div>

        <div v-else-if="reviews.length" class="mt-4 space-y-3">
            <div v-for="review in reviews" :key="review.id" class="rounded-2xl border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-slate-800">{{ review.user?.name }}</span>
                            <span class="text-sm text-amber-500">{{ '★'.repeat(review.rating) }}</span>
                            <span v-if="review.is_hidden" class="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-700">
                                Disembunyikan
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">
                            pada <strong>{{ review.kos?.name }}</strong> · {{ formatDate(review.created_at) }}
                        </p>
                        <p class="mt-1 text-sm text-slate-600">{{ review.comment || '(tanpa komentar)' }}</p>
                    </div>

                    <div class="flex gap-2">
                        <button
                            v-if="!review.is_hidden"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50"
                            :disabled="busyId === review.id"
                            @click="act(review, 'hide')"
                        >
                            Sembunyikan
                        </button>
                        <button
                            v-else
                            class="rounded-lg border border-emerald-200 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-50 disabled:opacity-50"
                            :disabled="busyId === review.id"
                            @click="act(review, 'unhide')"
                        >
                            Tampilkan
                        </button>
                        <button
                            class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50 disabled:opacity-50"
                            :disabled="busyId === review.id"
                            @click="act(review, 'delete')"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>

            <Pagination :meta="meta" @change="load" />
        </div>

        <EmptyState v-else icon="⭐" title="Tidak ada ulasan" description="Belum ada ulasan yang cocok dengan filter ini." />
    </div>
</template>
