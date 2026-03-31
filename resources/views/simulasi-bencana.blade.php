<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simulasi Bencana - BENCANA ALAM</title>

    @vite(['resources/css/app.css'])
</head>

<body class="flex h-dvh w-screen justify-center overflow-hidden bg-black font-sans">
    <div class="max-w-110 relative h-full w-full overflow-hidden shadow-2xl">

        <div id="page"
            class="absolute inset-0 flex flex-col items-center justify-start transition-opacity duration-500 ease-in-out">
            <!-- Background with opacity -->
            <img src="{{ asset('images/marker bg.webp') }}" alt=""
                class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-50">
            <!-- Semi-transparent overlay -->
            <div class="absolute inset-0 bg-black/30"></div>

            <!-- Header -->
            <div class="relative z-10 flex w-full items-center justify-center bg-[#ffac00] px-4 py-3 shadow-md">
                {{-- <a href="{{ route('home') }}" class="absolute left-4 flex items-center">
                    <img src="{{ asset('images/info.webp') }}" alt="Back" class="w-8 rotate-180">
                </a> --}}
                <h1 class="text-center text-xl font-extrabold tracking-wide text-[#800000]">SIMULASI BENCANA</h1>
            </div>

            <!-- Logo -->
            <img src="{{ asset('images/logo.webp') }}" alt="BENCANA Logo" class="w-54 mt-42 relative z-10">

            <div class="absolute bottom-[22%] left-1/2 flex w-full max-w-80 -translate-x-1/2 flex-col gap-2 px-4">

                <a href="#" class="group relative block">
                    <img src="{{ asset('images/button.webp') }}" alt="Download Marker"
                        class="block w-full brightness-100 transition-transform duration-200 group-hover:scale-105 group-hover:brightness-110 group-active:scale-95">
                    <span
                        class="absolute inset-0 flex items-center justify-center text-center text-xl font-extrabold tracking-wide text-[#800000]">DOWNLOAD
                        MARKER</span>
                </a>

                <a href="#" class="group relative block">
                    <img src="{{ asset('images/button.webp') }}" alt="Buka AR"
                        class="block w-full brightness-100 transition-transform duration-200 group-hover:scale-105 group-hover:brightness-110 group-active:scale-95">
                    <span
                        class="absolute inset-0 flex items-center justify-center text-center text-xl font-extrabold tracking-wide text-[#800000]">BUKA
                        AR</span>
                </a>

            </div>
        </div>

    </div>

</body>

</html>
