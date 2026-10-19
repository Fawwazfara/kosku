<script setup>
import { computed, ref, watch } from 'vue';
import api from '../services/api';
import { resizeImage } from '../utils/image';
import { MAX_PHOTOS_PER_KOS, MAX_PHOTO_SIZE_MB } from '../utils/constants';
import { useToastStore } from '../stores/toast';
import AppIcon from './AppIcon.vue';

const props = defineProps({
    kosId: { type: [Number, String], required: true },
    photos: { type: Array, default: () => [] },
});

const emit = defineEmits(['updated']);

const toast = useToastStore();
const fileInput = ref(null);
const uploading = ref(false);
const progress = ref(0);
const busyId = ref(null);
const fileDrag = ref(false);
const dragIndex = ref(null);
const overIndex = ref(null);

const localPhotos = ref([]);
watch(
    () => props.photos,
    (value) => {
        localPhotos.value = [...value];
    },
    { immediate: true },
);

const remaining = computed(() => Math.max(0, MAX_PHOTOS_PER_KOS - localPhotos.value.length));
const usage = computed(() => Math.round((localPhotos.value.length / MAX_PHOTOS_PER_KOS) * 100));
const isFull = computed(() => remaining.value === 0);

function pick() {
    if (!isFull.value && !uploading.value) fileInput.value?.click();
}

function onInputChange(event) {
    handleFiles(event.target.files);
    event.target.value = '';
}

function onDropFiles(event) {
    fileDrag.value = false;
    handleFiles(event.dataTransfer?.files);
}

async function handleFiles(fileList) {
    let files = Array.from(fileList || []).filter((file) => file.type.startsWith('image/'));
    if (!files.length) return;

    if (files.length > remaining.value) {
        toast.error(`Maksimal ${MAX_PHOTOS_PER_KOS} foto per kos. Sisa slot: ${remaining.value}.`);
        files = files.slice(0, remaining.value);
        if (!files.length) return;
    }

    uploading.value = true;
    progress.value = 0;
    try {
        const form = new FormData();
        for (const file of files) {
            const resized = await resizeImage(file);
            if (resized.size > MAX_PHOTO_SIZE_MB * 1024 * 1024) {
                toast.error(`"${resized.name}" masih melebihi ${MAX_PHOTO_SIZE_MB} MB setelah dikompres.`);
                continue;
            }
            form.append('photos[]', resized);
        }

        if (![...form.keys()].length) return;

        const { data } = await api.post(`/owner/kos/${props.kosId}/photos`, form, {
            onUploadProgress: (event) => {
                if (event.total) progress.value = Math.round((event.loaded / event.total) * 100);
            },
        });
        toast.success(data.message);
        emit('updated');
    } catch (error) {
        const message = error.response?.data?.errors
            ? Object.values(error.response.data.errors)[0][0]
            : error.response?.data?.message || 'Gagal mengunggah foto.';
        toast.error(message);
    } finally {
        uploading.value = false;
        progress.value = 0;
    }
}

async function setPrimary(photo) {
    if (photo.is_primary) return;
    busyId.value = photo.id;
    try {
        await api.put(`/owner/photos/${photo.id}`, { is_primary: true });
        localPhotos.value = localPhotos.value.map((p) => ({ ...p, is_primary: p.id === photo.id }));
        toast.success('Foto utama diperbarui.');
        emit('updated');
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gagal memperbarui foto.');
    } finally {
        busyId.value = null;
    }
}

async function remove(photo) {
    if (!confirm('Hapus foto ini? Tindakan ini tidak bisa dibatalkan.')) return;
    busyId.value = photo.id;
    try {
        await api.delete(`/owner/photos/${photo.id}`);
        localPhotos.value = localPhotos.value.filter((p) => p.id !== photo.id);
        toast.success('Foto dihapus.');
        emit('updated');
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gagal menghapus foto.');
    } finally {
        busyId.value = null;
    }
}

async function persistOrder() {
    try {
        await api.post(`/owner/kos/${props.kosId}/photos/order`, { ids: localPhotos.value.map((p) => p.id) });
        emit('updated');
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gagal mengubah urutan.');
        emit('updated');
    }
}

function onDragStart(index) {
    dragIndex.value = index;
}

function onDragOver(index) {
    overIndex.value = index;
}

function onDrop(index) {
    overIndex.value = null;
    if (dragIndex.value === null || dragIndex.value === index) return;
    const arr = [...localPhotos.value];
    const [moved] = arr.splice(dragIndex.value, 1);
    arr.splice(index, 0, moved);
    localPhotos.value = arr;
    dragIndex.value = null;
    persistOrder();
}

function shift(photo, direction) {
    const index = localPhotos.value.findIndex((p) => p.id === photo.id);
    const target = index + direction;
    if (target < 0 || target >= localPhotos.value.length) return;
    const arr = [...localPhotos.value];
    [arr[index], arr[target]] = [arr[target], arr[index]];
    localPhotos.value = arr;
    persistOrder();
}
</script>

<template>
    <div>
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-slate-700">
                    {{ localPhotos.length }} / {{ MAX_PHOTOS_PER_KOS }} foto
                </p>
                <div class="mt-1.5 h-1.5 w-40 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-brand-500 transition-all" :style="{ width: usage + '%' }" :class="isFull ? 'bg-amber-500' : ''"></div>
                </div>
            </div>
            <button type="button" class="btn btn-primary" :disabled="uploading || isFull" @click="pick">
                <AppIcon name="plus" :size="17" />
                {{ uploading ? 'Mengunggah…' : 'Tambah Foto' }}
            </button>
            <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" @change="onInputChange" />
        </div>

        <p class="mt-2 text-xs text-slate-500">
            Format JPG, PNG, atau WebP · maks {{ MAX_PHOTO_SIZE_MB }} MB/foto. Gambar otomatis dikompres di perangkatmu. Foto pertama jadi sampul.
        </p>

        <div v-if="uploading" class="mt-3">
            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-brand-500 transition-all" :style="{ width: progress + '%' }"></div>
            </div>
            <p class="mt-1 text-xs text-slate-500">Mengunggah… {{ progress }}%</p>
        </div>

        <div
            v-if="localPhotos.length"
            class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4"
        >
            <div
                v-for="(photo, index) in localPhotos"
                :key="photo.id"
                class="group relative aspect-square overflow-hidden rounded-2xl border bg-slate-100 transition"
                :class="[overIndex === index ? 'border-brand-500 ring-2 ring-brand-200' : 'border-slate-200', dragIndex === index ? 'opacity-50' : '']"
                draggable="true"
                @dragstart="onDragStart(index)"
                @dragover.prevent="onDragOver(index)"
                @dragleave="overIndex = null"
                @drop.prevent="onDrop(index)"
                @dragend="dragIndex = null; overIndex = null"
            >
                <img :src="photo.url" :alt="'Foto ' + (index + 1)" class="h-full w-full object-cover" loading="lazy" :class="busyId === photo.id ? 'opacity-40' : ''" />

                <span v-if="photo.is_primary" class="badge absolute left-2 top-2 bg-brand-600 text-white shadow">
                    <AppIcon name="star" :size="12" /> Sampul
                </span>

                <span class="absolute right-2 top-2 grid h-7 w-7 cursor-grab place-items-center rounded-lg bg-black/45 text-white opacity-0 backdrop-blur transition group-hover:opacity-100" title="Tarik untuk mengurutkan">
                    <AppIcon name="grip" :size="15" />
                </span>

                <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-1 bg-gradient-to-t from-black/75 to-transparent p-1.5 opacity-0 transition group-hover:opacity-100 group-focus-within:opacity-100">
                    <button
                        v-if="!photo.is_primary"
                        type="button"
                        class="rounded-lg px-2 py-1 text-[11px] font-semibold text-white/95 hover:bg-white/20"
                        :disabled="busyId === photo.id"
                        @click="setPrimary(photo)"
                    >
                        Jadikan sampul
                    </button>
                    <span v-else class="px-2 text-[11px] font-medium text-white/80">Sampul aktif</span>

                    <div class="flex items-center gap-0.5">
                        <button type="button" class="grid h-7 w-7 place-items-center rounded-lg text-white hover:bg-white/20 disabled:opacity-30" :disabled="index === 0" aria-label="Geser kiri" @click="shift(photo, -1)">
                            <AppIcon name="chevron-left" :size="15" />
                        </button>
                        <button type="button" class="grid h-7 w-7 place-items-center rounded-lg text-white hover:bg-white/20 disabled:opacity-30" :disabled="index === localPhotos.length - 1" aria-label="Geser kanan" @click="shift(photo, 1)">
                            <AppIcon name="chevron-right" :size="15" />
                        </button>
                        <button type="button" class="grid h-7 w-7 place-items-center rounded-lg text-rose-200 hover:bg-white/20" aria-label="Hapus foto" @click="remove(photo)">
                            <AppIcon name="trash" :size="15" />
                        </button>
                    </div>
                </div>

                <span class="absolute bottom-2 left-2 rounded-md bg-black/45 px-1.5 py-0.5 text-[10px] font-medium text-white/90 backdrop-blur group-hover:opacity-0">
                    {{ index + 1 }}
                </span>
            </div>
        </div>

        <button
            v-else
            type="button"
            class="mt-4 flex w-full flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed px-6 py-10 text-center transition"
            :class="fileDrag ? 'border-brand-500 bg-brand-50' : 'border-slate-300 hover:border-brand-400 hover:bg-slate-50'"
            @click="pick"
            @dragover.prevent="fileDrag = true"
            @dragleave="fileDrag = false"
            @drop.prevent="onDropFiles"
        >
            <span class="grid h-12 w-12 place-items-center rounded-full bg-brand-50 text-brand-600">
                <AppIcon name="upload" :size="24" />
            </span>
            <span class="text-sm font-semibold text-slate-700">Tarik & lepas foto di sini</span>
            <span class="text-xs text-slate-500">atau klik untuk memilih · JPG, PNG, WebP</span>
        </button>
    </div>
</template>
