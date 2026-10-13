export function rupiah(value) {
    if (value === null || value === undefined) return '-';
    return 'Rp' + Number(value).toLocaleString('id-ID');
}

export function priceRange(min, max) {
    if (max && max !== min) {
        return `${rupiah(min)} - ${rupiah(max)}`;
    }
    return rupiah(min);
}

export const TYPE_LABELS = {
    putri: 'Putri',
    putra: 'Putra',
    campur: 'Campur',
};

export const TYPE_CLASSES = {
    putri: 'bg-pink-100 text-pink-700',
    putra: 'bg-sky-100 text-sky-700',
    campur: 'bg-violet-100 text-violet-700',
};

export const STATUS_LABELS = {
    pending: 'Menunggu verifikasi',
    active: 'Aktif',
    hidden: 'Disembunyikan',
    rejected: 'Ditolak',
};

export const STATUS_CLASSES = {
    pending: 'bg-amber-100 text-amber-700',
    active: 'bg-emerald-100 text-emerald-700',
    hidden: 'bg-slate-200 text-slate-700',
    rejected: 'bg-rose-100 text-rose-700',
};

export function formatDate(value) {
    if (!value) return '';
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

export function whatsappLink(number, kosName, priceMin) {
    const price = priceMin
        ? ` dengan harga mulai Rp${Number(priceMin).toLocaleString('id-ID')}/bulan`
        : '';
    const message = `Halo, saya menemukan kos "${kosName}"${price} di KosKu dan ingin bertanya tentang ketersediaan kamar.`;
    return `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
}

export function mapsLink(lat, lng) {
    return `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
}
