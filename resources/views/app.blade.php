<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="KosKu - Temukan kos untuk mahasiswa perantau. Cari berdasarkan tipe, harga, fasilitas, dan lokasi. Lihat sisa kamar real-time dan hubungi pemilik via WhatsApp.">
    <title>KosKu — Cari Kos Mahasiswa Perantau</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='9' fill='%230d9488'/%3E%3Cpath d='M8 15.5 16 9l8 6.5V23a1 1 0 0 1-1 1h-4v-5h-6v5H9a1 1 0 0 1-1-1z' fill='none' stroke='white' stroke-width='2' stroke-linejoin='round'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div id="app"></div>
</body>
</html>
