<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

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
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 },
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
    map?.remove();
    map = null;
});
</script>

<template>
    <div>
        <button
            type="button"
            :disabled="locating"
            class="mb-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:opacity-60 sm:w-auto"
            @click="useMyLocation"
        >
            <span v-if="locating" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
            <span v-else>📍 Pakai Lokasi Saat Ini</span>
            <span v-if="locating">Mencari lokasi…</span>
        </button>
        <p v-if="geoError" class="mb-2 text-xs text-rose-600">{{ geoError }}</p>
        <div ref="el" class="w-full overflow-hidden rounded-2xl border border-slate-200" :style="{ height }"></div>
        <p class="mt-1 text-xs text-slate-500">Klik atau geser pin untuk menentukan lokasi kos.</p>
    </div>
</template>
