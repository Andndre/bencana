{{-- Overlay pembaca Modul PDF. Letakkan sebagai anak langsung elemen inset-0 --}}
@if (\App\Http\Controllers\ModulController::available())
    <div id="modul-overlay" class="absolute inset-0 z-50 hidden flex-col bg-black/85"
        data-src="{{ route('modul.show') }}">
        <div class="relative z-10 flex w-full shrink-0 items-center justify-center bg-[#ffac00] px-4 py-3 shadow-md">
            <button type="button" id="modul-close" aria-label="Tutup"
                class="absolute left-4 cursor-pointer text-2xl font-extrabold leading-none text-[#800000]">&times;</button>
            <h2 class="text-center text-lg font-extrabold tracking-wide text-[#800000]">MODUL BENCANA</h2>
        </div>

        <iframe id="modul-frame" title="Modul Bencana" class="w-full flex-1 bg-white"></iframe>

        <div class="shrink-0 bg-black/60 px-4 py-2 text-center">
            <a href="{{ route('modul.show') }}" target="_blank" rel="noopener"
                class="text-xs font-semibold text-[#ffac00] underline">PDF tidak tampil? Buka di tab baru</a>
        </div>
    </div>

    <script>
        (function() {
            const overlay = document.getElementById('modul-overlay');
            const frame = document.getElementById('modul-frame');
            const openBtn = document.getElementById('btn-baca-modul');
            const closeBtn = document.getElementById('modul-close');
            if (!overlay || !openBtn) return;

            openBtn.addEventListener('click', function() {
                // src di-set saat pertama dibuka supaya PDF tidak ikut terunduh saat load halaman
                if (!frame.src) frame.src = overlay.dataset.src;
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            });

            closeBtn.addEventListener('click', function() {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            });
        })();
    </script>
@endif
