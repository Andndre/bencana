{{-- Pemilih metode pembuatan marker. $mode = 'auto' | 'custom' --}}
<div>
    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Metode Pembuatan Marker</label>
    <div class="grid gap-3 sm:grid-cols-2">
        <label
            class="mode-option flex cursor-pointer gap-3 rounded-lg border-2 p-3 transition-colors {{ $mode === 'auto' ? 'border-[#c25c06] bg-[#c25c06]/5' : 'border-gray-200 hover:border-gray-300' }}">
            <input type="radio" name="mode" value="auto" class="mt-1" {{ $mode === 'auto' ? 'checked' : '' }}>
            <span>
                <span class="block text-sm font-semibold text-gray-800">Auto-Generate</span>
                <span class="block text-xs text-gray-500">Unggah logo/simbol PNG, bingkai marker + file .patt dibuat otomatis.</span>
            </span>
        </label>

        <label
            class="mode-option flex cursor-pointer gap-3 rounded-lg border-2 p-3 transition-colors {{ $mode === 'custom' ? 'border-[#c25c06] bg-[#c25c06]/5' : 'border-gray-200 hover:border-gray-300' }}">
            <input type="radio" name="mode" value="custom" class="mt-1" {{ $mode === 'custom' ? 'checked' : '' }}>
            <span>
                <span class="block text-sm font-semibold text-gray-800">Upload Kustom</span>
                <span class="block text-xs text-gray-500">Unggah gambar marker sendiri seperti sebelumnya.</span>
            </span>
        </label>
    </div>
    @error('mode')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
