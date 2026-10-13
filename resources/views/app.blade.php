<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="KosKu - Temukan kos untuk mahasiswa perantau. Cari berdasarkan tipe, harga, fasilitas, dan lokasi. Lihat sisa kamar real-time dan hubungi pemilik via WhatsApp.">
    <title>KosKu — Cari Kos Mahasiswa Perantau</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ctext y='26' font-size='26'%3E%F0%9F%8F%A0%3C/text%3E%3C/svg%3E">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div id="app"></div>
</body>
</html>
