@extends('admin._layout')

@section('title', 'Modul PDF')
@section('page-title', 'Modul PDF')
@section('page-subtitle', 'Materi bacaan + gambar marker AR siap cetak dalam satu file PDF')

@section('content')

<div class="grid gap-6 lg:grid-cols-3">

    {{-- Status + Upload --}}
    <div class="lg:col-span-1 space-y-6">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="font-bold text-gray-800 mb-4">Status</h3>

            @if ($exists)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 border border-green-200 px-3 py-1 text-xs font-semibold text-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    PDF sudah diunggah
                </span>

                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">Nama file</dt>
                        <dd class="font-medium text-gray-800 truncate">{{ \App\Http\Controllers\ModulController::FILENAME }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">Ukuran</dt>
                        <dd class="font-medium text-gray-800">{{ number_format($size / 1048576, 2) }} MB</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">Diunggah</dt>
                        <dd class="font-medium text-gray-800">{{ \Carbon\Carbon::createFromTimestamp($lastModified)->format('d M Y H:i') }}</dd>
                    </div>
                </dl>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('modul.download') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#c25c06] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a04a05] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh
                    </a>
                    <a href="{{ route('modul.show') }}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                        Buka di tab baru
                    </a>
                    <form method="POST" action="{{ route('admin.modul.destroy') }}"
                        onsubmit="return confirm('Hapus modul PDF? Tombol modul akan hilang dari halaman publik.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 hover:border-red-400 transition-colors">
                            Hapus
                        </button>
                    </form>
                </div>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-600">
                    Belum ada modul
                </span>
                <p class="mt-3 text-sm text-gray-500">
                    Tombol "Download Modul" dan "Baca Modul" belum muncul di halaman publik sampai file diunggah.
                </p>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="font-bold text-gray-800 mb-4">{{ $exists ? 'Ganti File PDF' : 'Unggah File PDF' }}</h3>

            <form method="POST" action="{{ route('admin.modul.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <input type="file" name="file_pdf" accept="application/pdf" required
                        class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#800000] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#600000] file:cursor-pointer">
                    @error('file_pdf')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-gray-500">Format: PDF. Maksimal 20MB. Mengunggah file baru akan mengganti yang lama.</p>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-[#c25c06] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#a04a05] transition-colors">
                    {{ $exists ? 'Ganti Modul' : 'Unggah Modul' }}
                </button>
            </form>
        </div>
    </div>

    {{-- Preview --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden h-full flex flex-col">
            <div class="border-b border-gray-200 px-5 py-3">
                <h3 class="font-bold text-gray-800">Preview</h3>
            </div>
            @if ($exists)
                <iframe src="{{ route('modul.show') }}" title="Preview Modul PDF"
                    class="w-full flex-1 min-h-125 bg-gray-100"></iframe>
            @else
                <div class="flex flex-1 min-h-75 items-center justify-center text-sm text-gray-400">
                    Belum ada PDF untuk ditampilkan.
                </div>
            @endif
        </div>
    </div>

</div>

@endsection
