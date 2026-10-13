<?php

use Illuminate\Support\Str;

if (! function_exists('normalize_whatsapp')) {
    /**
     * Normalisasi nomor HP Indonesia ke format 62xxxxxxxxxx.
     */
    function normalize_whatsapp(?string $raw): ?string
    {
        if (blank($raw)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if ($digits === '') {
            return null;
        }

        if (Str::startsWith($digits, '62')) {
            return $digits;
        }

        if (Str::startsWith($digits, '0')) {
            return '62'.ltrim($digits, '0');
        }

        return '62'.$digits;
    }
}

if (! function_exists('whatsapp_link')) {
    function whatsapp_link(string $whatsapp, string $kosName, ?int $priceMin = null): string
    {
        $price = $priceMin ? ' dengan harga mulai Rp'.number_format($priceMin, 0, ',', '.').'/bulan' : '';

        $pesan = "Halo, saya menemukan kos \"{$kosName}\"{$price} di KosKu dan ingin bertanya tentang ketersediaan kamar.";

        return 'https://wa.me/'.$whatsapp.'?text='.rawurlencode($pesan);
    }
}

if (! function_exists('maps_link')) {
    function maps_link(float $latitude, float $longitude): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='.$latitude.','.$longitude;
    }
}
