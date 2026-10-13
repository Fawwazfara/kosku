<script setup>
import { computed, ref } from 'vue';
import api from '../services/api';
import { resizeImage } from '../utils/image';
import { useToastStore } from '../stores/toast';

const props = defineProps({
    kosId: { type: [Number, String], required: true },
    photos: { type: Array, default: () => [] },
});

const emit = defineEmits(['updated']);

const toast = useToastStore();
const fileInput = ref(null);
const uploading = ref(false);
const busyId = ref(null);

const remaining = computed(() => Math.max(0, 8 - props.photos.length));

async function onFiles(event) {
    const files = Array.from(event.target.files || []);
    event.target.value = '';
    if (!files.length) return;

    if (files.length > remaining.value) {
        toast.error(`Maksimal 8 foto per kos. Sisa slot: ${remaining.value}.`);
        return;
    }

    uploading.value = true;
    try {
        const form = new FormData();
        for (const file of files) {
            const resized = await resizeImage(file);
            if (resized.size > 2 * 1024 * 1024) {
                toast.error(`"${resized.name}" melebihi 2 MB setelah dikompres.`);
                uploading.value = false;
                return;
            }
            form.append('photos[]', resized);
        }
        const { data } = await api.post(`/owner/kos/${props.kosId}/photos`, form);
        toast.success(data.message);
        emit('updated');
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gagal mengunggah foto.');
    } finally {
        uploading.value = false;
    }
}

async function setPrimary(photo) {
    busyId.value = photo.id;
    try {
        await api.put(`/owner/photos/${photo.id}`, { is_primary: true });
        toast.success('Foto utama diperbarui.');
        emit('updated');
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gagal memperbarui foto.');
    } finally {
        busyId.value = null;
    }
}

async function remove(photo) {
    if (!confirm('Hapus foto ini?')) return;
    busyId.value = photo.id;
    try {
        await api.delete(`/owner/photos/${photo.id}`);
        toast.success('Foto dihapus.');
        emit('updated');
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gagal menghapus foto.');
    } finally {
        busyId.value = null;
    }
}

async function move(photo, direction) {
    const ids = props.photos.map((p) => p.id);
    const index = ids.indexOf(photo.id);
    const target = index + direction;
    if (target < 0 || target >= ids.length) return;

    [ids[index], ids[target]] = [ids[target], ids[index]];

    try {
        await api.post(`/owner/kos/${props.kosId}/photos/order`, { ids });
        emit('updated');
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gagal mengubah urutan.');
    }
}
</script>

<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm text-slate-600">
                {{ photos.length }}/8 foto
                <span v-if="photos.length === 0" class="text-amber-600">· minimal 1 foto disarankan</span>
            </p>
            <button
                type="button"
                class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                :disabled="uploading || remaining === 0"
                @click="fileInput.click()"
            >
                {{ uploading ? 'Mengunggah…' : '+ Tambah Foto' }}
            </button>
            <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" @change="onFiles" />
        </div>

        <p class="mt-1 text-xs text-slate-500">Format JPG/PNG/WebP, maks 2 MB/foto. Gambar otomatis dikompres di perangkatmu.</p>

        <div v-if="photos.length" class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
            <div v-for="(photo, index) in photos" :key="photo.id" class="group relative overflow-hidden rounded-xl border border-slate-200">
                <img :src="photo.url" :alt="'Foto ' + (index + 1)" class="aspect-square w-full object-cover" loading="lazy" />
                <span v-if="photo.is_primary" class="absolute left-1.5 top-1.5 rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-bold text-white">
                    UTAMA
                </span>
                <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-1 bg-black/60 p-1 opacity-0 transition group-hover:opacity-100">
                    <button
                        v-if="!photo.is_primary"
                        type="button"
                        class="rounded px-1.5 py-1 text-[11px] font-medium text-white hover:bg-white/20"
                        :disabled="busyId === photo.id"
                        @click="setPrimary(photo)"
                    >
                        Jadikan utama
                    </button>
                    <span v-else class="px-1.5 text-[11px] text-white/80">Utama</span>
                    <div class="flex gap-0.5">
                        <button type="button" class="rounded px-1.5 py-1 text-xs text-white hover:bg-white/20" :disabled="index === 0" @click="move(photo, -1)">↑</button>
                        <button type="button" class="rounded px-1.5 py-1 text-xs text-white hover:bg-white/20" :disabled="index === photos.length - 1" @click="move(photo, 1)">↓</button>
                        <button type="button" class="rounded px-1.5 py-1 text-xs text-rose-300 hover:bg-white/20" @click="remove(photo)">✕</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="mt-3 rounded-xl border border-dashed border-slate-300 py-8 text-center text-sm text-slate-500">
            Belum ada foto. Tambahkan foto kamar agar kos lebih menarik.
        </div>
    </div>
</template>
