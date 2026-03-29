<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BENCANA ALAM</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet" />

    @vite(['resources/css/app.css'])
</head>

<body class="flex h-screen w-screen justify-center overflow-hidden bg-black font-sans">
    <!-- Mobile Container -->
    <div class="max-w-110 relative h-full w-full overflow-hidden shadow-2xl">

        <div id="splash-screen"
            class="absolute inset-0 flex items-center justify-center transition-opacity duration-700 ease-in-out"
            style="background-color: #c25c06;">
            <img src="{{ asset('images/bencana splash.webp') }}" alt=""
                class="pointer-events-none absolute inset-0 h-full w-full object-cover">
            <img src="{{ asset('images/logo.webp') }}" alt="BENCANA Logo" class="w-63 relative z-10">
        </div>

        <div id="menu-screen"
            class="pointer-events-none absolute inset-0 flex scale-[1.03] flex-col items-center justify-start opacity-0 transition-opacity duration-700 ease-in-out"
            style="background-image: url('{{ asset('images/bencana.webp') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
            <!-- Logo -->
            <img src="{{ asset('images/logo.webp') }}" alt="BENCANA Logo"
                class="w-50 absolute left-1/2 top-[3.5%] -translate-x-1/2">

            <div class="absolute bottom-[12%] left-1/2 flex w-full max-w-80 -translate-x-1/2 flex-col gap-2 px-4">

                <a href="#" class="group relative block">
                    <img src="{{ asset('images/button.webp') }}" alt="Peta Bencana"
                        class="block w-full brightness-100 transition-transform duration-200 group-hover:scale-105 group-hover:brightness-110 group-active:scale-95">
                    <span
                        class="absolute inset-0 flex items-center justify-center text-center text-xl font-extrabold tracking-wide text-[#800000]">PETA
                        BENCANA</span>
                </a>

                <a href="#" class="group relative block">
                    <img src="{{ asset('images/button.webp') }}" alt="Simulasi Bencana"
                        class="block w-full brightness-100 transition-transform duration-200 group-hover:scale-105 group-hover:brightness-110 group-active:scale-95">
                    <span
                        class="absolute inset-0 flex items-center justify-center text-center text-xl font-extrabold tracking-wide text-[#800000]">SIMULASI
                        BENCANA</span>
                </a>

                <a href="#" class="group relative block">
                    <img src="{{ asset('images/button.webp') }}" alt="Penanggulangan Bencana"
                        class="block w-full brightness-100 transition-transform duration-200 group-hover:scale-105 group-hover:brightness-110 group-active:scale-95">
                    <span
                        class="absolute inset-0 flex items-center justify-center text-center text-xl font-extrabold tracking-wide text-[#800000]">PENANGGULANGAN
                        BENCANA</span>
                </a>
            </div>

            <a href="#"
                class="group absolute bottom-[3%] left-[4%] transition-transform duration-200 hover:scale-110 group-active:scale-95">
                <img src="{{ asset('images/info.webp') }}" alt="Info" class="w-12">
            </a>
        </div>

    </div>

    <script>
        setTimeout(function() {
            document.getElementById('splash-screen').classList.add('opacity-0', 'pointer-events-none');
            var menu = document.getElementById('menu-screen');
            menu.classList.remove('opacity-0', 'pointer-events-none', 'scale-[1.03]');
        }, 2000);
    </script>
</body>

</html>
