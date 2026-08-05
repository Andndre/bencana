{{--
    Tombol Modul PDF. Pasangkan dengan partials/modul-overlay.blade.php,
    yang harus diletakkan sebagai anak langsung elemen berukuran penuh
    (mis. #page / .phone-frame) supaya overlay-nya menutup seluruh layar.

    @param bool $compact  true = pil kecil (untuk bar bawah), false = tombol button.webp
--}}
@if (\App\Http\Controllers\ModulController::available())
    @php($compact = $compact ?? false)

    @if ($compact)
        <div class="mb-2 flex items-center justify-center">
            <button type="button" id="btn-baca-modul"
                class="cursor-pointer rounded-full border-2 border-[#800000] bg-white px-4 py-1.5 text-xs font-extrabold text-[#800000] transition-transform active:scale-95">
                BACA MODUL
            </button>
        </div>
    @else
        <button type="button" id="btn-baca-modul" class="group relative block w-full cursor-pointer">
            <img src="{{ asset('images/button.webp') }}" alt="Baca Modul"
                class="block w-full brightness-100 transition-transform duration-200 group-hover:scale-105 group-hover:brightness-110 group-active:scale-95">
            <span
                class="absolute inset-0 flex items-center justify-center text-center text-lg font-extrabold tracking-wide text-[#800000]">BACA
                MODUL</span>
        </button>
    @endif
@endif
