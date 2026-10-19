<script setup>
import { onMounted, ref } from 'vue';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import { useSeo } from '../../composables/useSeo';
import { formatDate } from '../../utils/format';
import AdminNav from '../../components/AdminNav.vue';
import Pagination from '../../components/Pagination.vue';
import EmptyState from '../../components/EmptyState.vue';
import AppIcon from '../../components/AppIcon.vue';

useSeo({ title: 'Moderasi Ulasan' });

const toast = useToastStore();
const reviews = ref([]);
const meta = ref(null);
const loading = ref(true);
const filter = ref('all');
const busyId = ref(null);

const options = [
    { value: 'all', label: 'Semua' },
    { value: 'visible', label: 'Tampil' },
    { value: 'hidden', label: 'Disembunyikan' },
];

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
        <h1 class="text-xl font-bold tracking-tight text-slate-900">Moderasi Ulasan</h1>
        <div class="mt-4">
            <AdminNav />
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <button
                v-for="option in options"
                :key="option.value"
                class="chip"
                :class="filter === option.value ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-brand-300'"
                @click="filter = option.value; load()"
            >
                {{ option.label }}
            </button>
        </div>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="n in 4" :key="n" class="h-20 animate-pulse rounded-2xl bg-slate-200"></div>
        </div>

        <div v-else-if="reviews.length" class="mt-4 space-y-3">
            <div v-for="review in reviews" :key="review.id" class="card p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">
                                {{ review.user?.name?.charAt(0).toUpperCase() }}
                            </span>
                            <span class="font-semibold text-slate-800">{{ review.user?.name }}</span>
                            <span class="flex items-center gap-0.5 text-amber-500">
                                <AppIcon v-for="n in review.rating" :key="n" name="star" :size="14" />
                            </span>
                            <span v-if="review.is_hidden" class="badge bg-rose-100 text-rose-700">Disembunyikan</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            pada <strong class="text-slate-700">{{ review.kos?.name }}</strong> · {{ formatDate(review.created_at) }}
                        </p>
                        <p class="mt-1 text-sm text-slate-600">{{ review.comment || '(tanpa komentar)' }}</p>
                    </div>

                    <div class="flex gap-2">
                        <button v-if="!review.is_hidden" class="btn btn-secondary" :disabled="busyId === review.id" @click="act(review, 'hide')">
                            <AppIcon name="eye-off" :size="15" /> Sembunyikan
                        </button>
                        <button v-else class="btn border border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50" :disabled="busyId === review.id" @click="act(review, 'unhide')">
                            <AppIcon name="eye" :size="15" /> Tampilkan
                        </button>
                        <button class="btn border border-rose-200 bg-white text-rose-600 hover:bg-rose-50" :disabled="busyId === review.id" @click="act(review, 'delete')">
                            <AppIcon name="trash" :size="15" /> Hapus
                        </button>
                    </div>
                </div>
            </div>

            <Pagination :meta="meta" @change="load" />
        </div>

        <EmptyState v-else icon="star" title="Tidak ada ulasan" description="Belum ada ulasan yang cocok dengan filter ini." />
    </div>
</template>
