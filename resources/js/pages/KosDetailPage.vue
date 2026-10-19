<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';
import { useToastStore } from '../stores/toast';
import { useSeo } from '../composables/useSeo';
import { mapsLink, priceRange, TYPE_LABELS, TYPE_CLASSES, formatDate } from '../utils/format';
import KosGallery from '../components/KosGallery.vue';
import KosMap from '../components/KosMap.vue';
import StarRating from '../components/StarRating.vue';
import EmptyState from '../components/EmptyState.vue';
import AppIcon from '../components/AppIcon.vue';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const toast = useToastStore();

const kos = ref(null);
const loading = ref(true);
const notFound = ref(false);

const reviewForm = ref({ rating: 0, comment: '' });
const editingReview = ref(false);
const savingReview = ref(false);

const isFull = computed(() => kos.value && kos.value.available_rooms <= 0);
const typeClass = computed(() => (kos.value ? TYPE_CLASSES[kos.value.type] : ''));
const markers = computed(() => (kos.value ? [kos.value] : []));
const gMaps = computed(() => (kos.value ? mapsLink(kos.value.latitude, kos.value.longitude) : '#'));

const ratingBreakdown = computed(() => {
    if (!kos.value) return null;
    return kos.value.rating_avg ? kos.value.rating_avg.toFixed(1) : null;
});

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get(`/kos/${route.params.slug}`);
        kos.value = data.data;
        useSeo({ title: kos.value.name, description: kos.value.description?.slice(0, 150) });
    } catch (error) {
        if (error.response?.status === 404) notFound.value = true;
    } finally {
        loading.value = false;
    }
}

async function contactWa() {
    if (!kos.value) return;
    window.open(kos.value.whatsapp_link, '_blank', 'noopener');
    api.post(`/kos/${kos.value.slug}/wa-click`).catch(() => {});
}

function requireLogin(action) {
    toast.info(`Masuk dulu untuk ${action}.`);
    router.push({ name: 'login', query: { redirect: route.fullPath } });
}

async function toggleFavorite() {
    if (!auth.isLoggedIn) return requireLogin('menyimpan favorit');
    try {
        if (kos.value.is_favorite) await api.delete(`/kos/${kos.value.slug}/favorite`);
        else await api.post(`/kos/${kos.value.slug}/favorite`);
        kos.value.is_favorite = !kos.value.is_favorite;
        toast.success(kos.value.is_favorite ? 'Disimpan ke favorit.' : 'Dihapus dari favorit.');
    } catch {
        toast.error('Gagal memperbarui favorit.');
    }
}

function startEditReview() {
    editingReview.value = true;
    reviewForm.value = { rating: kos.value.review.rating, comment: kos.value.review.comment || '' };
}

async function submitReview() {
    if (!auth.isLoggedIn) return requireLogin('memberi ulasan');
    if (reviewForm.value.rating < 1) {
        toast.error('Pilih rating bintang dulu.');
        return;
    }

    savingReview.value = true;
    try {
        if (editingReview.value) {
            await api.put(`/reviews/${kos.value.review.id}`, reviewForm.value);
            toast.success('Ulasan diperbarui.');
        } else {
            await api.post(`/kos/${kos.value.slug}/reviews`, reviewForm.value);
            toast.success('Terima kasih atas ulasannya!');
        }
        reviewForm.value = { rating: 0, comment: '' };
        editingReview.value = false;
        await load();
    } catch (error) {
        const errors = error.response?.data?.errors;
        toast.error(errors ? Object.values(errors)[0][0] : error.response?.data?.message || 'Gagal menyimpan ulasan.');
    } finally {
        savingReview.value = false;
    }
}

async function deleteReview() {
    if (!confirm('Hapus ulasanmu?')) return;
    try {
        await api.delete(`/reviews/${kos.value.review.id}`);
        toast.success('Ulasan dihapus.');
        await load();
    } catch {
        toast.error('Gagal menghapus ulasan.');
    }
}

onMounted(load);
</script>

<template>
    <div class="mx-auto max-w-5xl px-4 py-6">
        <div v-if="loading" class="animate-pulse space-y-4">
            <div class="aspect-[16/9] rounded-2xl bg-slate-200"></div>
            <div class="h-6 w-2/3 rounded bg-slate-200"></div>
            <div class="h-4 w-1/3 rounded bg-slate-200"></div>
        </div>

        <EmptyState
            v-else-if="notFound"
            icon="alert"
            title="Kos tidak ditemukan"
            description="Kos mungkin sudah tidak aktif atau disembunyikan pemilik."
        >
            <RouterLink :to="{ name: 'home' }" class="btn btn-primary mt-4">Cari kos lain</RouterLink>
        </EmptyState>

        <template v-else-if="kos">
            <nav class="mb-3 flex items-center gap-1.5 text-xs text-slate-500">
                <RouterLink :to="{ name: 'home' }" class="hover:text-brand-700">Beranda</RouterLink>
                <AppIcon name="chevron-right" :size="13" />
                <span class="truncate text-slate-700">{{ kos.name }}</span>
            </nav>

            <KosGallery :photos="kos.photos" :name="kos.name" />

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge" :class="typeClass">Kos {{ TYPE_LABELS[kos.type] }}</span>
                            <span class="badge" :class="isFull ? 'bg-rose-600 text-white' : 'bg-emerald-50 text-emerald-700'">
                                {{ isFull ? 'Penuh' : 'Tersedia ' + kos.available_rooms + ' kamar' }}
                            </span>
                        </div>
                        <h1 class="mt-2.5 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{{ kos.name }}</h1>
                        <div class="mt-1.5 flex items-center gap-1.5 text-sm text-slate-500">
                            <AppIcon name="pin" :size="16" class="text-slate-400" />
                            <span>{{ kos.address }}, {{ kos.district }}</span>
                        </div>
                        <div class="mt-2.5 flex items-center gap-2">
                            <StarRating :model-value="Math.round(kos.rating_avg || 0)" />
                            <span class="text-sm text-slate-600">
                                <template v-if="ratingBreakdown">{{ ratingBreakdown }} / 5</template>
                                <template v-else>Belum ada rating</template>
                                · {{ kos.reviews_count }} ulasan
                            </span>
                        </div>
                    </div>

                    <section class="card p-4">
                        <h2 class="section-title mb-2">Deskripsi</h2>
                        <p class="whitespace-pre-line text-sm leading-relaxed text-slate-600">{{ kos.description }}</p>
                    </section>

                    <section v-if="kos.facilities.length" class="card p-4">
                        <h2 class="section-title mb-3">Fasilitas</h2>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="facility in kos.facilities" :key="facility.id" class="chip border-brand-100 bg-brand-50 text-brand-700">
                                <AppIcon name="check" :size="13" /> {{ facility.name }}
                            </span>
                        </div>
                    </section>

                    <section v-if="kos.rules" class="card border-amber-200 bg-amber-50/60 p-4">
                        <h2 class="section-title mb-2 flex items-center gap-2 text-amber-900">
                            <AppIcon name="info" :size="17" /> Aturan Kos
                        </h2>
                        <p class="whitespace-pre-line text-sm leading-relaxed text-amber-900/90">{{ kos.rules }}</p>
                    </section>

                    <section class="card p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h2 class="section-title">Lokasi</h2>
                            <a :href="gMaps" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:underline">
                                Google Maps <AppIcon name="external" :size="14" />
                            </a>
                        </div>
                        <div class="overflow-hidden rounded-xl">
                            <KosMap :markers="markers" :center="[kos.latitude, kos.longitude]" :zoom="16" height="280px" />
                        </div>
                    </section>

                    <section class="card p-4">
                        <h2 class="section-title mb-3">Ulasan Mahasiswa ({{ kos.reviews_count }})</h2>

                        <div class="mb-4 rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                            <template v-if="!auth.isLoggedIn">
                                <p class="text-sm text-slate-600">
                                    <button class="link" @click="requireLogin('memberi ulasan')">Masuk</button>
                                    untuk memberi rating dan ulasan.
                                </p>
                            </template>

                            <template v-else-if="kos.review && !editingReview">
                                <div class="flex items-center justify-between">
                                    <StarRating :model-value="kos.review.rating" size="sm" />
                                    <div class="flex gap-3 text-xs">
                                        <button class="font-semibold text-brand-700 hover:underline" @click="startEditReview">Edit</button>
                                        <button class="font-semibold text-rose-600 hover:underline" @click="deleteReview">Hapus</button>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-slate-600">{{ kos.review.comment || '(tanpa komentar)' }}</p>
                                <p class="mt-1 text-xs text-slate-400">Ini ulasanmu.</p>
                            </template>

                            <template v-else-if="kos.can.review || editingReview">
                                <p class="mb-2 text-sm font-medium text-slate-700">{{ editingReview ? 'Edit ulasanmu' : 'Beri ulasanmu' }}</p>
                                <StarRating v-model="reviewForm.rating" :readonly="false" size="lg" />
                                <textarea
                                    v-model="reviewForm.comment"
                                    rows="3"
                                    maxlength="2000"
                                    placeholder="Bagaimana pengalamanmu tinggal di kos ini?"
                                    class="input mt-3"
                                ></textarea>
                                <div class="mt-3 flex gap-2">
                                    <button class="btn btn-primary" :disabled="savingReview" @click="submitReview">
                                        {{ savingReview ? 'Menyimpan…' : editingReview ? 'Simpan perubahan' : 'Kirim ulasan' }}
                                    </button>
                                    <button v-if="editingReview" class="btn btn-secondary" @click="editingReview = false">Batal</button>
                                </div>
                            </template>

                            <template v-else>
                                <p class="text-sm text-slate-500">Kamu tidak dapat mengulas kos ini.</p>
                            </template>
                        </div>

                        <div v-if="kos.reviews.length" class="space-y-3">
                            <div v-for="review in kos.reviews" :key="review.id" class="rounded-2xl border border-slate-200 p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-100 text-xs font-bold text-brand-700">
                                            {{ review.user?.name?.charAt(0).toUpperCase() }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">{{ review.user?.name }}</p>
                                            <StarRating :model-value="review.rating" size="sm" />
                                        </div>
                                    </div>
                                    <span class="text-xs text-slate-400">{{ formatDate(review.created_at) }}</span>
                                </div>
                                <p v-if="review.comment" class="mt-2 text-sm leading-relaxed text-slate-600">{{ review.comment }}</p>
                            </div>
                        </div>
                        <p v-else class="text-sm text-slate-500">Belum ada ulasan. Jadilah yang pertama!</p>
                    </section>
                </div>

                <aside class="lg:sticky lg:top-20 lg:self-start">
                    <div class="card p-4">
                        <p class="text-xs text-slate-500">Harga sewa</p>
                        <p class="text-2xl font-bold text-slate-900">
                            {{ priceRange(kos.price_min, kos.price_max) }}
                            <span class="text-sm font-normal text-slate-500">/bulan</span>
                        </p>

                        <dl class="mt-4 space-y-2.5 border-t border-slate-100 pt-4 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Total kamar</dt>
                                <dd class="font-semibold text-slate-800">{{ kos.total_rooms }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Sisa kamar</dt>
                                <dd class="font-semibold" :class="isFull ? 'text-rose-600' : 'text-emerald-600'">{{ isFull ? 'Penuh' : kos.available_rooms }}</dd>
                            </div>
                        </dl>

                        <button class="btn mt-4 w-full bg-[#25D366] text-white shadow-soft hover:brightness-95" @click="contactWa">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413" />
                            </svg>
                            Hubungi via WhatsApp
                        </button>

                        <a :href="gMaps" target="_blank" rel="noopener" class="btn btn-secondary mt-2 w-full">
                            <AppIcon name="map" :size="16" /> Buka di Google Maps
                        </a>

                        <button
                            class="btn mt-2 w-full"
                            :class="kos.is_favorite ? 'border border-rose-200 bg-rose-50 text-rose-600' : 'border border-slate-200 text-slate-700 hover:bg-slate-50'"
                            @click="toggleFavorite"
                        >
                            <AppIcon :name="kos.is_favorite ? 'heart-fill' : 'heart'" :size="16" />
                            {{ kos.is_favorite ? 'Tersimpan di favorit' : 'Simpan ke favorit' }}
                        </button>

                        <p v-if="isFull" class="mt-3 rounded-xl bg-rose-50 px-3 py-2 text-xs text-rose-700">
                            Kamar sedang penuh. Kamu tetap bisa menghubungi pemilik untuk ketersediaan berikutnya.
                        </p>
                    </div>
                </aside>
            </div>
        </template>
    </div>
</template>
