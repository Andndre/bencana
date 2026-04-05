@extends('admin._layout')

@section('title', 'Edit Marker AR')
@section('page-title', 'Edit Marker AR')
@section('page-subtitle', 'Ubah data marker AR')

@section('header-actions')
    <a href="{{ route('admin.markers.index') }}"
        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50">
        ← Kembali
    </a>
@endsection

@section('content')

    <div class="max-w-2xl">

        <form method="POST" action="{{ route('admin.markers.update', $marker) }}" enctype="multipart/form-data"
            class="space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            @csrf
            @method('PUT')

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Marker <span
                        class="text-red-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama', $marker->nama) }}" required autofocus
                    placeholder="Contoh: Marker Banjir"
                    class="@error('nama') @enderror w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20">
                @error('nama')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Bencana</label>
                <select name="disaster_id"
                    class="@error('disaster_id') @enderror w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20">
                    <option value="">-- Pilih Bencana --</option>
                    @foreach ($disasters as $disaster)
                        <option value="{{ $disaster->id }}"
                            {{ old('disaster_id', $marker->disaster_id) == $disaster->id ? 'selected' : '' }}>
                            {{ $disaster->name }}
                        </option>
                    @endforeach
                </select>
                @error('disaster_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Gambar Marker AR</label>
                <div id="marker-drop-zone"
                    class="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center transition-colors hover:border-[#c25c06]">
                    <input type="file" name="path_gambar_marker" id="path_gambar_marker" accept="image/*"
                        class="@error('path_gambar_marker') border-red-500 @enderror hidden"
                        onchange="previewMarkerImage(this)">
                    <label for="path_gambar_marker" class="flex cursor-pointer flex-col items-center gap-3">
                        <div id="preview-container" @if (!$marker->path_gambar_marker) class="hidden" @endif>
                            <img id="preview-image"
                                 src="{{ $marker->path_gambar_marker ? Storage::disk('public')->url($marker->path_gambar_marker) : '' }}"
                                 class="mx-auto max-h-48 rounded-lg shadow">
                        </div>
                        <div id="upload-placeholder" @if ($marker->path_gambar_marker) class="hidden" @endif>
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Klik untuk pilih gambar atau drag & drop di sini</p>
                        </div>
                    </label>
                </div>
                <p class="mt-2 text-xs text-gray-400">
                    Kosongkan jika tidak ingin mengubah gambar.
                    Gunakan gambar dengan kontras tinggi dan detail visual jelas. Minimal 512×512 pixel.
                    Format: JPEG, PNG, JPG, GIF, WebP. Maksimal 5MB.
                </p>
                @error('path_gambar_marker')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Model 3D (.glb / .gltf)</label>
                <div id="model-drop-zone"
                    class="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center transition-colors hover:border-[#c25c06]">
                    <input type="file" name="path_model" id="path_model" accept=".glb,.gltf,.binary"
                        class="@error('path_model') border-red-500 @enderror hidden" onchange="previewModelFile(this)">
                    <label for="path_model" class="flex cursor-pointer flex-col items-center gap-3">
                        <div id="model-preview-container" @if (!$marker->path_model) class="hidden" @endif>
                            <svg class="mx-auto h-10 w-10 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p id="model-filename" class="mt-2 text-sm font-medium text-green-600">
                                {{ $marker->path_model ? basename($marker->path_model) : '' }}
                            </p>
                        </div>
                        <div id="model-placeholder" @if ($marker->path_model) class="hidden" @endif>
                            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Klik untuk pilih file model 3D (opsional)</p>
                        </div>
                    </label>
                </div>
                <p class="mt-2 text-xs text-gray-400">
                    Kosongkan jika tidak ingin mengubah model 3D.
                    Maksimal 20MB.
                </p>
                @error('path_model')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="rounded-lg bg-[#c25c06] px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-[#a04a05]">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.markers.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50">
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

        // --- Drag & Drop for Marker Image ---
        var markerDropZone = document.getElementById('marker-drop-zone');
        var markerInput = document.getElementById('path_gambar_marker');

        if (markerDropZone && markerInput) {
            ['dragenter', 'dragover'].forEach(function(eventName) {
                markerDropZone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    markerDropZone.classList.add('border-[#c25c06]', 'bg-[#c25c06]/5');
                }, false);
            });

            ['dragleave', 'drop'].forEach(function(eventName) {
                markerDropZone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    markerDropZone.classList.remove('border-[#c25c06]', 'bg-[#c25c06]/5');
                }, false);
            });

            markerDropZone.addEventListener('drop', function(e) {
                var files = e.dataTransfer.files;
                if (files.length > 0) {
                    Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'files').set.call(markerInput, files);
                    markerInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }

        // --- Drag & Drop for 3D Model ---
        var modelDropZone = document.getElementById('model-drop-zone');
        var modelInput = document.getElementById('path_model');

        if (modelDropZone && modelInput) {
            ['dragenter', 'dragover'].forEach(function(eventName) {
                modelDropZone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    modelDropZone.classList.add('border-[#c25c06]', 'bg-[#c25c06]/5');
                }, false);
            });

            ['dragleave', 'drop'].forEach(function(eventName) {
                modelDropZone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    modelDropZone.classList.remove('border-[#c25c06]', 'bg-[#c25c06]/5');
                }, false);
            });

            modelDropZone.addEventListener('drop', function(e) {
                var files = e.dataTransfer.files;
                if (files.length > 0) {
                    Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'files').set.call(modelInput, files);
                    modelInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }
    </script>
@endsection
