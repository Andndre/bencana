@extends('admin._layout')

@section('title', 'Tambah Bencana')
@section('page-title', 'Tambah Bencana Baru')
@section('page-subtitle', 'Tambahkan jenis bencana baru ke sistem')

@section('header-actions')
    <a href="{{ route('admin.disasters.index') }}"
        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
        ← Kembali
    </a>
@endsection

@section('content')

<div class="max-w-2xl">

    <form method="POST" action="{{ route('admin.disasters.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">

        @csrf

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Bencana <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                placeholder="Contoh: Banjir"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20 @error('name') border-red-500 @enderror">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Slug <span class="text-red-500">*</span></label>
            <input type="text" name="slug" value="{{ old('slug') }}" required
                placeholder="Contoh: banjir"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20 @error('slug') border-red-500 @enderror">
            <p class="mt-1 text-xs text-gray-400">Slug digunakan untuk URL. Huruf kecil, tanpa spasi, gunakan tanda hubung (-).</p>
            @error('slug')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi</label>
            <textarea name="description" rows="4"
                placeholder="Deskripsi singkat tentang bencana ini..."
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20 resize-none @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-lg bg-[#c25c06] px-6 py-2.5 text-sm font-bold text-white hover:bg-[#a04a05] transition-colors">
                Simpan Bencana
            </button>
            <a href="{{ route('admin.disasters.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                Batal
            </a>
        </div>

    </form>
</div>

@endsection
