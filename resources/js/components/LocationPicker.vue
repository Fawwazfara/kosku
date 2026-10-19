<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import AppIcon from './AppIcon.vue';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const props = defineProps({
    latitude: { type: [Number, String], default: null },
    longitude: { type: [Number, String], default: null },
    height: { type: String, default: '320px' },
});

const emit = defineEmits(['update:latitude', 'update:longitude', 'change', 'geocode']);

const el = ref(null);
let map = null;
let marker = null;

const locating = ref(false);
const geoError = ref('');

const query = ref('');
const results = ref([]);
const searching = ref(false);
const showResults = ref(false);
const searchError = ref('');
let searchTimer = null;
let searchController = null;

const DEFAULT = [-7.752, 110.4];

const GEO_MESSAGES = {
    1: 'Izinkan akses lokasi untuk menggunakan lokasi saat ini.',
    2: 'Lokasi tidak tersedia. Periksa GPS/perangkat kamu.',
    3: 'Waktu permintaan lokasi habis. Coba lagi.',
};

function emitPosition(latlng) {
    emit('update:latitude', Number(latlng.lat.toFixed(7)));
    emit('update:longitude', Number(latlng.lng.toFixed(7)));
    emit('change', { latitude: latlng.lat, longitude: latlng.lng });
}

function emitGeocode(place) {
    if (!place) return;
    emit('geocode', {
        address: place.address,
        district: place.district,
        latitude: place.latitude,
        longitude: place.longitude,
    });
}

async function reverseGeocode(lat, lng) {
    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=id`;
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!res.ok) return null;

    const data = await res.json();
    const a = data.address || {};

    const parts = [];
    if (a.road) parts.push(a.house_number ? `${a.house_number} ${a.road}` : a.road);
    if (a.village || a.town || a.city) parts.push(a.village || a.town || a.city);
    if (a.county || a.state) parts.push(a.county || a.state);

    return {
        address: parts.length ? parts.join(', ') : (data.display_name || '').split(',').slice(0, 3).join(',').trim(),
        district: a.district || a.suburb || a.neighbourhood || a.village || a.town || a.city || a.county || '',
        latitude: Number(data.lat ?? lat),
        longitude: Number(data.lon ?? lng),
    };
}

function photonLabel(p) {
    const street = [p.housenumber, p.street].filter(Boolean).join(' ');
    const parts = [p.name, street, p.district, p.city, p.state, p.country].filter(Boolean);
    return [...new Set(parts)].join(', ');
}

function photonToPlace(feature) {
    const [lng, lat] = feature.geometry.coordinates;
    const p = feature.properties || {};
    const street = [p.housenumber, p.street].filter(Boolean).join(' ');
    const addressParts = [p.name, street, p.district, p.city].filter(Boolean);

    return {
        label: photonLabel(p),
        address: [...new Set(addressParts)].join(', '),
        district: p.district || p.city || p.county || '',
        latitude: Number(lat),
        longitude: Number(lng),
    };
}

async function search() {
    const term = query.value.trim();
    searchError.value = '';

    if (term.length < 3) {
        results.value = [];
        showResults.value = false;
        return;
    }

    searchController?.abort();
    searchController = new AbortController();
    searching.value = true;

    try {
        const url = `https://photon.komoot.io/api/?limit=6&lang=id&q=${encodeURIComponent(term)}`;
        const res = await fetch(url, { signal: searchController.signal, headers: { Accept: 'application/json' } });
        if (!res.ok) throw new Error('gagal');

        const data = await res.json();
        results.value = (data.features || []).map(photonToPlace).filter((place) => place.label);
        showResults.value = true;
    } catch (e) {
        if (e.name !== 'AbortError') {
            searchError.value = 'Gagal mencari lokasi. Coba lagi.';
            results.value = [];
            showResults.value = false;
        }
    } finally {
        searching.value = false;
    }
}

function onSearchInput() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(search, 400);
}

function selectResult(place) {
    showResults.value = false;
    results.value = [];
    query.value = place.label;
    emitPosition({ lat: place.latitude, lng: place.longitude });
    emitGeocode(place);
}

function closeResults() {
    setTimeout(() => {
        showResults.value = false;
    }, 150);
}

async function useMyLocation() {
    geoError.value = '';
    locating.value = true;

    if (!('geolocation' in navigator)) {
        geoError.value = 'Browser kamu tidak mendukung akses lokasi.';
        locating.value = false;
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            emitPosition({ lat: latitude, lng: longitude });

            const place = await reverseGeocode(latitude, longitude);
            if (place) emitGeocode(place);

            locating.value = false;
        },
        (error) => {
            geoError.value = GEO_MESSAGES[error.code] || 'Gagal mengambil lokasi. Coba lagi.';
            locating.value = false;
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 },
    );
}

function syncMarker() {
    if (!map) return;
    const lat = props.latitude !== null && props.latitude !== '' ? Number(props.latitude) : null;
    const lng = props.longitude !== null && props.longitude !== '' ? Number(props.longitude) : null;

    if (lat === null || lng === null || Number.isNaN(lat) || Number.isNaN(lng)) {
        marker?.setLatLng(DEFAULT);
        return;
    }

    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', () => emitPosition(marker.getLatLng()));
    }
    map.setView([lat, lng], map.getZoom() < 13 ? 15 : map.getZoom());
}

onMounted(() => {
    const lat = props.latitude !== null && props.latitude !== '' ? Number(props.latitude) : DEFAULT[0];
    const lng = props.longitude !== null && props.longitude !== '' ? Number(props.longitude) : DEFAULT[1];

    map = L.map(el.value, { center: [lat, lng], zoom: 15 }).setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    marker.on('dragend', () => emitPosition(marker.getLatLng()));

    map.on('click', (e) => {
        marker.setLatLng(e.latlng);
        emitPosition(e.latlng);
    });

    setTimeout(() => map.invalidateSize(), 100);
});

watch(() => [props.latitude, props.longitude], syncMarker);

onBeforeUnmount(() => {
    clearTimeout(searchTimer);
    searchController?.abort();
    map?.remove();
    map = null;
});
</script>

<template>
    <div>
        <div class="relative mb-2">
            <label class="label" for="location-search">Cari nama kos / alamat</label>
            <div class="relative">
                <input
                    id="location-search"
                    v-model="query"
                    type="text"
                    autocomplete="off"
                    class="input pr-9"
                    placeholder="Contoh: Kost Galuh UIN Bandung"
                    @input="onSearchInput"
                    @focus="showResults = results.length > 0"
                    @blur="closeResults"
                />
                <span v-if="searching" class="absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin rounded-full border-2 border-slate-300 border-t-brand-600"></span>
                <AppIcon v-else name="search" :size="16" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" />
            </div>

            <ul v-if="showResults && results.length" class="absolute z-[1000] mt-1 max-h-64 w-full overflow-auto rounded-xl border border-slate-200 bg-white py-1 shadow-card">
                <li v-for="(place, index) in results" :key="`${place.latitude}-${place.longitude}-${index}`">
                    <button
                        type="button"
                        class="flex w-full items-start gap-2 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                        @mousedown.prevent="selectResult(place)"
                    >
                        <AppIcon name="pin" :size="15" class="mt-0.5 shrink-0 text-brand-600" />
                        <span class="line-clamp-2">{{ place.label }}</span>
                    </button>
                </li>
            </ul>
            <p v-if="searchError" class="mt-1 text-xs text-rose-600">{{ searchError }}</p>
        </div>

        <button
            type="button"
            :disabled="locating"
            class="mb-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:opacity-60 sm:w-auto"
            @click="useMyLocation"
        >
            <span v-if="locating" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
            <template v-else><AppIcon name="pin" :size="16" /> Pakai Lokasi Saat Ini</template>
            <span v-if="locating">Mencari lokasi…</span>
        </button>
        <p v-if="geoError" class="mb-2 text-xs text-rose-600">{{ geoError }}</p>
        <div ref="el" class="w-full overflow-hidden rounded-2xl border border-slate-200" :style="{ height }"></div>
        <p class="mt-1 text-xs text-slate-500">
            Cari nama kos, klik atau geser pin untuk menentukan lokasi. Hasil pencarian &copy; OpenStreetMap (Photon).
        </p>
    </div>
</template>
