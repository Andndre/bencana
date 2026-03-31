<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BENCANA ALAM</title>

    @vite(['resources/css/app.css'])
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body class="flex h-dvh w-screen justify-center overflow-hidden bg-black font-sans">

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
                class="absolute left-1/2 top-[10.5%] w-60 -translate-x-1/2">

            <div class="absolute bottom-[22%] left-1/2 flex w-full max-w-80 -translate-x-1/2 flex-col gap-2 px-4">

                <a href="{{ route('peta-bencana') }}" class="group relative block">
                    <img src="{{ asset('images/button.webp') }}" alt="Peta Bencana"
                        class="block w-full brightness-100 transition-transform duration-200 group-hover:scale-105 group-hover:brightness-110 group-active:scale-95">
                    <span
                        class="absolute inset-0 flex items-center justify-center text-center text-xl font-extrabold tracking-wide text-[#800000]">PETA
                        BENCANA</span>
                </a>

                <a href="{{ route('simulasi-bencana') }}" class="group relative block">
                    <img src="{{ asset('images/button.webp') }}" alt="Simulasi Bencana"
                        class="block w-full brightness-100 transition-transform duration-200 group-hover:scale-105 group-hover:brightness-110 group-active:scale-95">
                    <span
                        class="absolute inset-0 flex items-center justify-center text-center text-xl font-extrabold tracking-wide text-[#800000]">SIMULASI
                        BENCANA</span>
                </a>

                <a href="{{ route('penanggulangan-bencana') }}" class="group relative block">
                    <img src="{{ asset('images/button.webp') }}" alt="Penanggulangan Bencana"
                        class="block w-full brightness-100 transition-transform duration-200 group-hover:scale-105 group-hover:brightness-110 group-active:scale-95">
                    <span
                        class="absolute inset-0 flex items-center justify-center text-center text-xl font-extrabold tracking-wide text-[#800000]">PENANGGULANGAN
                        BENCANA</span>
                </a>
            </div>

            <a href="#" id="info-button"
                class="group absolute bottom-[3%] left-[4%] transition-transform duration-200 hover:scale-110 group-active:scale-95">
                <img src="{{ asset('images/info.webp') }}" alt="Info" class="w-12">
            </a>
        </div>

        <!-- Info Overlay -->
        <div id="info-overlay" class="absolute inset-0 z-50 hidden bg-black/85">
            <div class="flex h-full w-full flex-col items-center justify-center px-8">
                <img src="{{ asset('images/pengembang.webp') }}" alt="Pengembang" class="w-60 object-contain">
                {{-- rounded white bg, and with border --}}
                <div class="rounded-4xl border-4 border-[#800000] bg-white/85 p-4 text-center shadow-lg">
                    <h2 class="text-sm font-extrabold text-[#2f0000]">Dikembangkan Oleh:</h2>
                    <p class="text-xl font-extrabold text-[#2f0000]">Ketut Sudiasih</p>
                    {{-- alamat --}}
                    <h3 class="mt-4 text-sm font-extrabold text-[#2f0000]">Alamat:</h3>
                    <p class="text-sm font-extrabold text-[#2f0000]">BR. Dinas Ancak, Desa Bungkulan, Kec. Sawan,
                        Kabupaten Buleleng</p>
                </div>
                <button id="panduan-button"
                    class="mt-3 rounded-full border-4 border-[#800000] bg-[#ffac00] px-8 py-3 font-extrabold text-[#800000] transition-transform hover:scale-105 active:scale-95">
                    PANDUAN
                </button>
            </div>
        </div>

        <!-- Panduan Overlay -->
        <div id="panduan-overlay" class="absolute inset-0 z-50 hidden bg-black/85">
            <!-- Header -->
            <div class="relative z-10 flex w-full items-center justify-center bg-[#ffac00] px-4 py-3 shadow-md">
                <h1 class="text-center text-xl font-extrabold tracking-wide text-[#800000]">PANDUAN</h1>
            </div>

            <div class="flex h-full w-full flex-col items-start justify-center py-6 pr-4">
                <div class="flex flex-col gap-2">
                    <div
                        class="panduan-item flex items-center gap-3 rounded-r-full border-b-4 border-r-4 border-t-4 border-[#800000] bg-[#ffac00] px-3 py-3 text-[#800000] opacity-0">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white">
                            <img src="{{ asset('images/panduan-1.png') }}" alt=""
                                class="h-7 w-7 object-contain">
                        </div>
                        <p class="text-sm font-extrabold">Masuk ke menu Simulasi Bencana</p>
                    </div>
                    <div
                        class="panduan-item flex items-center gap-3 rounded-r-full border-b-4 border-r-4 border-t-4 border-[#800000] bg-[#ffac00] px-3 py-3 text-[#800000] opacity-0">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white">
                            <img src="{{ asset('images/panduan-2.png') }}" alt=""
                                class="h-7 w-7 object-contain">
                        </div>
                        <p class="text-sm font-extrabold">Download beberapa Marker terlebih dahulu.</p>
                    </div>
                    <div
                        class="panduan-item flex items-center gap-3 rounded-r-full border-b-4 border-r-4 border-t-4 border-[#800000] bg-[#ffac00] px-3 py-3 text-[#800000] opacity-0">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white">
                            <img src="{{ asset('images/panduan-3.png') }}" alt=""
                                class="h-7 w-7 object-contain">
                        </div>
                        <p class="text-sm font-extrabold">Setelah marker di download, klik tombol Buka AR untuk masuk ke
                            kamera.</p>
                    </div>
                    <div
                        class="panduan-item flex items-center gap-3 rounded-r-full border-b-4 border-r-4 border-t-4 border-[#800000] bg-[#ffac00] px-3 py-3 text-[#800000] opacity-0">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white">
                            <img src="{{ asset('images/panduan-4.png') }}" alt=""
                                class="h-7 w-7 object-contain">
                        </div>
                        <p class="text-sm font-extrabold">Arahkan kamera ke marker yang sudah di download.</p>
                    </div>
                    <div
                        class="panduan-item flex items-center gap-3 rounded-r-full border-b-4 border-r-4 border-t-4 border-[#800000] bg-[#ffac00] px-3 py-3 text-[#800000] opacity-0">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white">
                            <img src="{{ asset('images/panduan-5.png') }}" alt=""
                                class="h-7 w-7 object-contain">
                        </div>
                        <p class="text-sm font-extrabold">Setelah itu objek simulasi bencana akan muncul sesuai dengan
                            marker yang digunakan. </p>
                    </div>
                </div>
            </div>

        </div>

        <script>
            setTimeout(function() {
                document.getElementById('splash-screen').classList.add('opacity-0', 'pointer-events-none');
                var menu = document.getElementById('menu-screen');
                menu.classList.remove('opacity-0', 'pointer-events-none', 'scale-[1.03]');
            }, 2000);

            $('#info-button').click(function() {
                $('#info-overlay').fadeIn();
            });

            $('#info-close-button').click(function() {
                $('#info-overlay').fadeOut();
            });

            $('#info-overlay').click(function() {
                $('#info-overlay').fadeOut();
            });

            $('#panduan-overlay').click(function() {
                $('#panduan-overlay').fadeOut();
            });

            $('#panduan-button').click(function() {
                $('#panduan-overlay').fadeIn();
                $('#info-overlay').fadeOut();

                $('.panduan-item').each(function(index) {
                    $(this).delay(index * 200).animate({
                        opacity: 1
                    }, 100);
                });
            });
        </script>
</body>

</html>
