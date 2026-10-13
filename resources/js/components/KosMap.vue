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
    markers: { type: Array, default: () => [] },
    center: { type: Array, default: () => [-7.75, 110.4] },
    zoom: { type: Number, default: 13 },
    height: { type: String, default: '320px' },
    fit: { type: Boolean, default: true },
});

const el = ref(null);
let map = null;
let layer = null;

function popupHtml(marker) {
    const photo = marker.primary_photo?.url
        ? `<img src="${marker.primary_photo.url}" style="width:180px;height:110px;object-fit:cover;border-radius:8px" loading="lazy" />`
        : '';
    const kamar = marker.available_rooms > 0 ? `${marker.available_rooms} kamar kosong` : 'Penuh';
    const harga = marker.price_min ? `Rp${Number(marker.price_min).toLocaleString('id-ID')}/bln` : '';
    return `
        <div style="width:180px">
            ${photo}
            <div style="font-weight:600;margin-top:6px;font-size:13px">${marker.name}</div>
            <div style="font-size:12px;color:#475569">${harga} · ${kamar}</div>
            <a href="/kos/${marker.slug}" style="display:inline-block;margin-top:6px;font-size:12px;color:#226956;font-weight:600">Lihat detail →</a>
        </div>`;
}

function render() {
    if (!map || !layer) return;
    layer.clearLayers();

    const points = [];

    props.markers.forEach((marker) => {
        if (marker.latitude == null || marker.longitude == null) return;
        const m = L.marker([marker.latitude, marker.longitude]).addTo(layer);
        m.bindPopup(popupHtml(marker));
        points.push([marker.latitude, marker.longitude]);
    });

    if (props.fit && points.length > 1) {
        map.fitBounds(points, { padding: [40, 40], maxZoom: 15 });
    } else if (points.length === 1) {
        map.setView(points[0], props.zoom);
    }
}

onMounted(() => {
    map = L.map(el.value, {
        center: props.center,
        zoom: props.zoom,
        scrollWheelZoom: false,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    layer = L.layerGroup().addTo(map);
    render();
});

watch(() => props.markers, render, { deep: true });

onBeforeUnmount(() => {
    map?.remove();
    map = null;
});
</script>

<template>
    <div ref="el" class="w-full overflow-hidden rounded-2xl border border-slate-200" :style="{ height }"></div>
</template>
