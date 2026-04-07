<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <title>{{ config('app.name') }} - Cara Lebih Baik Kelola Payroll & Absensi Karyawan</title>
    <meta name="description" content="Software absensi dan payroll terbaik untuk kelola karyawan, gaji, cuti, dan HR dalam satu sistem terintegrasi.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body class="antialiased bg-white dark:bg-zinc-950">
    @yield('content')
</body>

</html>