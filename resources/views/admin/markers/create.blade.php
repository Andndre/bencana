@extends('admin._layout')

@section('title', 'Upload Marker AR')
@section('page-title', 'Upload Marker AR')
@section('page-subtitle', 'Upload gambar marker AR untuk simulasi bencana')

@section('header-actions')
    <a href="{{ route('admin.markers.index') }}"
        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
        ← Kembali
    </a>
@endsection

@section('content')

<div class="max-w-2xl">

    <form method="POST" action="{{ route('admin.markers.store') }}" enctype="multipart/form-data"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">

        @csrf

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Marker <span class="text-red-500">*</span></label>
            <input type="text" name="nama" value="{{ old('nama') }}" required autofocus
                placeholder="Contoh: Marker Banjir"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20 @error('nama') border-red-500 @enderror">
            @error('nama')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Bencana</label>
            <select name="disaster_id"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20 @error('disaster_id') border-red-500 @enderror">
                <option value="">-- Pilih Bencana --</option>
                @foreach ($disasters as $disaster)
                    <option value="{{ $disaster->id }}" {{ old('disaster_id') == $disaster->id ? 'selected' : '' }}>
                        {{ $disaster->name }}
                    </option>
                @endforeach
            </select>
            @error('disaster_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Gambar Marker AR <span class="text-red-500">*</span></label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-[#c25c06] transition-colors">
                <input type="file" name="path_gambar_marker" id="path_gambar_marker" accept="image/*"
                    class="hidden @error('path_gambar_marker') border-red-500 @enderror"
                    onchange="previewMarkerImage(this)">
                <label for="path_gambar_marker" class="cursor-pointer flex flex-col items-center gap-3">
                    <div id="preview-container" class="hidden">
                        <img id="preview-image" class="max-h-48 mx-auto rounded-lg shadow">
                    </div>
                    <div id="upload-placeholder">
                        <svg class="w-12 h-12 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Klik untuk pilih gambar atau drag & drop di sini</p>
                    </div>
                </label>
            </div>
            <p class="mt-2 text-xs text-gray-400">
                Gunakan gambar dengan kontras tinggi dan detail visual jelas. Minimal 512×512 pixel.
                Format: JPEG, PNG, JPG, GIF, WebP. Maksimal 5MB.
            </p>
            @error('path_gambar_marker')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Model 3D (.glb / .gltf)</label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-[#c25c06] transition-colors">
                <input type="file" name="path_model" id="path_model" accept=".glb,.gltf,.binary"
                    class="hidden @error('path_model') border-red-500 @enderror"
                    onchange="previewModelFile(this)">
                <label for="path_model" class="cursor-pointer flex flex-col items-center gap-3">
                    <div id="model-preview-container" class="hidden">
                        <svg class="w-10 h-10 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p id="model-filename" class="mt-2 text-sm text-green-600 font-medium"></p>
                    </div>
                    <div id="model-placeholder">
                        <svg class="w-10 h-10 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Klik untuk pilih file model 3D (opsional)</p>
                    </div>
                </label>
            </div>
            <p class="mt-2 text-xs text-gray-400">
                Model 3D berekstensi .glb atau .gltf yang akan ditampilkan di marker.
                Maksimal 20MB. Jika tidak diupload, akan ditampilkan kubus placeholder.
            </p>
            @error('path_model')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-lg bg-[#c25c06] px-6 py-2.5 text-sm font-bold text-white hover:bg-[#a04a05] transition-colors">
                Upload Marker
            </button>
            <a href="{{ route('admin.markers.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                Batal
            </a>
        </div>

    </form>
</div>

@endsection

@section('scripts')
<script>
function previewMarkerImage(input) {
    var file = input.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
            document.getElementById('preview-container').classList.remove('hidden');
            document.getElementById('upload-placeholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
}
function previewModelFile(input) {
    var file = input.files[0];
    if (file) {
        document.getElementById('model-filename').textContent = file.name;
        document.getElementById('model-preview-container').classList.remove('hidden');
        document.getElementById('model-placeholder').classList.add('hidden');
    }
}
</script>
@endsection
