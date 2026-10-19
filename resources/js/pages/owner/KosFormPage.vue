<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import { useToastStore } from '../../stores/toast';
import { useSeo } from '../../composables/useSeo';
import LocationPicker from '../../components/LocationPicker.vue';
import PhotoManager from '../../components/PhotoManager.vue';
import AppIcon from '../../components/AppIcon.vue';

const route = useRoute();
const router = useRouter();
const toast = useToastStore();

const editing = computed(() => !!route.params.id);
useSeo({ title: editing.value ? 'Ubah Kos' : 'Tambah Kos' });

const form = reactive({
    name: '',
    type: 'putri',
    description: '',
    price_min: '',
    price_max: '',
    address: '',
    district: '',
    latitude: null,
    longitude: null,
    whatsapp: '',
    total_rooms: 1,
    available_rooms: 1,
    rules: '',
    facility_ids: [],
});

const facilities = ref([]);
const kos = ref(null);
const loading = ref(true);
const saving = ref(false);
const errors = ref({});

const AREA_OPTIONS = [
    'Depok', 'Sleman', 'Mlati', 'Ngaglik', 'Ngemplak', 'Gamping', 'Godean', 'Berbah', 'Cangkringan', 'Kalasan',
    'Minggir', 'Moyudan', 'Pakem', 'Prambanan', 'Seyegan', 'Tempel', 'Turi', 'Gondokusuman', 'Gondomanan', 'Jetis',
    'Kotagede', 'Danurejan', 'Gedongtengen', 'Kraton', 'Mantrijeron', 'Mergangsan', 'Ngampilan', 'Pakualaman',
    'Tegalrejo', 'Umbulharjo', 'Wirobrajan', 'Seturan', 'Babarsari', 'Condongcatur', 'Kaliurang',
].sort((a, b) => a.localeCompare(b, 'id'));

const districtOptions = computed(() => {
    const value = form.district;
    if (value && !AREA_OPTIONS.includes(value)) return [value, ...AREA_OPTIONS];
    return AREA_OPTIONS;
});

function applyCurrentLocation(place) {
    if (place.address) form.address = place.address;
    if (place.district) {
        const match = AREA_OPTIONS.find((option) => option.toLowerCase() === place.district.toLowerCase());
        form.district = match || place.district;
    }
    errors.value.address = undefined;
    errors.value.district = undefined;
}

function fieldError(field) {
    return errors.value[field]?.[0];
}

async function loadFacilities() {
    const { data } = await api.get('/facilities');
    facilities.value = data.data;
}

async function loadKos() {
    try {
        const { data } = await api.get(`/owner/kos/${route.params.id}`);
        kos.value = data.data;
        Object.assign(form, {
            name: kos.value.name,
            type: kos.value.type,
            description: kos.value.description,
            price_min: kos.value.price_min,
            price_max: kos.value.price_max ?? '',
            address: kos.value.address,
            district: kos.value.district,
            latitude: kos.value.latitude,
            longitude: kos.value.longitude,
            whatsapp: kos.value.whatsapp,
            total_rooms: kos.value.total_rooms,
            available_rooms: kos.value.available_rooms,
            rules: kos.value.rules ?? '',
            facility_ids: kos.value.facilities.map((f) => f.id),
        });
    } catch (error) {
        toast.error('Kos tidak ditemukan atau bukan milikmu.');
        router.push({ name: 'owner.dashboard' });
    }
}

function toggleFacility(id) {
    const index = form.facility_ids.indexOf(id);
    if (index === -1) form.facility_ids.push(id);
    else form.facility_ids.splice(index, 1);
}

async function submit() {
    saving.value = true;
    errors.value = {};

    const payload = { ...form, price_max: form.price_max === '' ? null : form.price_max };

    try {
        if (editing.value) {
            const { data } = await api.put(`/owner/kos/${route.params.id}`, payload);
            toast.success(data.message);
            await loadKos();
        } else {
            const { data } = await api.post('/owner/kos', payload);
            toast.success(data.message);
            router.replace({ name: 'owner.kos.edit', params: { id: data.kos.id } });
            kos.value = data.kos;
        }
    } catch (error) {
        errors.value = error.response?.data?.errors || {};
        if (!Object.keys(errors.value).length) toast.error(error.response?.data?.message || 'Gagal menyimpan kos.');
        else toast.error('Periksa kembali isian formulir.');
    } finally {
        saving.value = false;
    }
}

async function refreshPhotos() {
    const { data } = await api.get(`/owner/kos/${route.params.id}`);
    kos.value = data.data;
}

onMounted(async () => {
    await loadFacilities();
    if (editing.value) await loadKos();
    loading.value = false;
});
</script>

<template>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">{{ editing ? 'Ubah Kos' : 'Tambah Kos Baru' }}</h1>
                <p class="text-sm text-slate-500">Isi data kos dengan lengkap agar mudah ditemukan mahasiswa.</p>
            </div>
            <RouterLink :to="{ name: 'owner.dashboard' }" class="btn btn-ghost">
                <AppIcon name="chevron-left" :size="16" /> Dashboard
            </RouterLink>
        </div>

        <div v-if="loading" class="mt-6 h-64 animate-pulse rounded-2xl bg-slate-200"></div>

        <form v-else class="mt-6 space-y-6" @submit.prevent="submit">
            <section class="card p-5">
                <h2 class="section-title mb-4 flex items-center gap-2"><AppIcon name="home" :size="18" class="text-brand-600" /> Informasi Dasar</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="label">Nama kos</label>
                        <input v-model="form.name" type="text" required class="input" placeholder="Contoh: Kos Putri Melati" />
                        <p v-if="fieldError('name')" class="mt-1 text-xs text-rose-600">{{ fieldError('name') }}</p>
                    </div>

                    <div>
                        <label class="label">Tipe kos</label>
                        <select v-model="form.type" class="input">
                            <option value="putri">Putri</option>
                            <option value="putra">Putra</option>
                            <option value="campur">Campur</option>
                        </select>
                    </div>

                    <div>
                        <label class="label">Nomor WhatsApp</label>
                        <input v-model="form.whatsapp" type="tel" placeholder="08xxxxxxxxxx" required class="input" />
                        <p v-if="fieldError('whatsapp')" class="mt-1 text-xs text-rose-600">{{ fieldError('whatsapp') }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="label">Deskripsi</label>
                        <textarea v-model="form.description" rows="4" required maxlength="3000" class="input" placeholder="Ceritakan keunggulan kos, suasana, dan sekitarnya…"></textarea>
                        <p v-if="fieldError('description')" class="mt-1 text-xs text-rose-600">{{ fieldError('description') }}</p>
                    </div>

                    <div>
                        <label class="label">Harga terendah / bulan</label>
                        <input v-model.number="form.price_min" type="number" min="100000" step="50000" required class="input" />
                        <p v-if="fieldError('price_min')" class="mt-1 text-xs text-rose-600">{{ fieldError('price_min') }}</p>
                    </div>
                    <div>
                        <label class="label">Harga tertinggi <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input v-model.number="form.price_max" type="number" min="0" step="50000" class="input" />
                        <p v-if="fieldError('price_max')" class="mt-1 text-xs text-rose-600">{{ fieldError('price_max') }}</p>
                    </div>

                    <div>
                        <label class="label">Total kamar</label>
                        <input v-model.number="form.total_rooms" type="number" min="1" max="500" required class="input" />
                        <p v-if="fieldError('total_rooms')" class="mt-1 text-xs text-rose-600">{{ fieldError('total_rooms') }}</p>
                    </div>
                    <div>
                        <label class="label">Sisa kamar kosong</label>
                        <input v-model.number="form.available_rooms" type="number" min="0" :max="form.total_rooms" required class="input" />
                        <p v-if="fieldError('available_rooms')" class="mt-1 text-xs text-rose-600">{{ fieldError('available_rooms') }}</p>
                    </div>
                </div>
            </section>

            <section class="card p-5">
                <h2 class="section-title mb-4 flex items-center gap-2"><AppIcon name="pin" :size="18" class="text-brand-600" /> Lokasi</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="label">Alamat lengkap</label>
                        <input v-model="form.address" type="text" required class="input" placeholder="Jalan, nomor, RT/RW" />
                        <p v-if="fieldError('address')" class="mt-1 text-xs text-rose-600">{{ fieldError('address') }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Kecamatan / area</label>
                        <select v-model="form.district" required class="input">
                            <option value="" disabled>— Pilih kecamatan / area —</option>
                            <option v-for="option in districtOptions" :key="option" :value="option">{{ option }}</option>
                        </select>
                        <p v-if="fieldError('district')" class="mt-1 text-xs text-rose-600">{{ fieldError('district') }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <LocationPicker
                            v-model:latitude="form.latitude"
                            v-model:longitude="form.longitude"
                            @geocode="applyCurrentLocation"
                        />
                        <p v-if="fieldError('latitude') || fieldError('longitude')" class="mt-1 text-xs text-rose-600">
                            Tentukan lokasi kos di peta.
                        </p>
                    </div>
                </div>
            </section>

            <section class="card p-5">
                <h2 class="section-title mb-3 flex items-center gap-2"><AppIcon name="sparkles" :size="18" class="text-brand-600" /> Fasilitas</h2>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    <label
                        v-for="facility in facilities"
                        :key="facility.id"
                        class="flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 text-sm text-slate-700 transition"
                        :class="form.facility_ids.includes(facility.id) ? 'border-brand-500 bg-brand-50' : 'border-slate-200 hover:bg-slate-50'"
                    >
                        <input type="checkbox" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500" :checked="form.facility_ids.includes(facility.id)" @change="toggleFacility(facility.id)" />
                        {{ facility.name }}
                    </label>
                </div>

                <div class="mt-4">
                    <label class="label">Aturan kos</label>
                    <textarea v-model="form.rules" rows="3" maxlength="2000" placeholder="Contoh: jam malam 22.00, dilarang merokok…" class="input"></textarea>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" :disabled="saving" class="btn btn-primary px-6">
                    {{ saving ? 'Menyimpan…' : editing ? 'Simpan Perubahan' : 'Simpan & Lanjut Tambah Foto' }}
                </button>
            </div>
        </form>

        <section v-if="editing && kos" class="card mt-6 p-5">
            <h2 class="section-title mb-3 flex items-center gap-2"><AppIcon name="image" :size="18" class="text-brand-600" /> Foto Kos</h2>
            <PhotoManager :kos-id="kos.id" :photos="kos.photos" @updated="refreshPhotos" />
        </section>
    </div>
</template>
