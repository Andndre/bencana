<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - BENCANA ALAM')</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @vite(['resources/css/app.css'])
</head>

<body class="flex h-dvh w-screen justify-center overflow-hidden bg-black font-sans">
    <div class="max-w-110 relative h-full w-full overflow-hidden shadow-2xl">

        <div id="page" class="absolute inset-0 flex flex-col items-center justify-start">
            <img src="{{ asset('images/marker bg.webp') }}" alt=""
                class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-50">
            <div class="absolute inset-0 bg-black/30"></div>

            <!-- Header -->
            <div class="relative z-10 flex w-full items-center justify-between bg-[#ffac00] px-4 py-3 shadow-md">
                <a href="{{ route('home') }}" class="text-sm font-bold text-[#800000] hover:underline">← Kembali</a>
                <h1 class="absolute left-1/2 -translate-x-1/2 text-center text-lg font-extrabold tracking-wide text-[#800000]">ADMIN</h1>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-bold text-[#800000] hover:underline">Logout</button>
                </form>
            </div>

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="relative z-10 mx-4 mt-3 rounded bg-green-600 px-4 py-2 text-sm font-bold text-white">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="relative z-10 mx-4 mt-3 rounded bg-red-600 px-4 py-2 text-sm font-bold text-white">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Content -->
            <div class="relative z-10 mt-4 w-full max-w-80 flex-1 overflow-y-auto px-4 pb-6">
                @yield('content')
            </div>
        </div>

    </div>
</body>

</html>
