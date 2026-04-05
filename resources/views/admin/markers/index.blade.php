@extends('admin._layout')

@section('title', 'Kelola Marker AR')
@section('page-title', 'Kelola Marker AR')
@section('page-subtitle', 'Upload dan kelola marker AR untuk simulasi bencana')

@section('header-actions')
    <a href="{{ route('admin.markers.create') }}"
        class="inline-flex items-center gap-2 rounded-lg bg-[#c25c06] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a04a05] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Upload Marker
    </a>
@endsection

@section('content')

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Marker</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bencana</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">File .patt</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Model 3D (.glb)</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($markers as $marker)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $marker->nama ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-full bg-[#c25c06]/10 px-3 py-1 text-xs font-semibold text-[#c25c06]">
                                {{ $marker->disaster?->name ?? 'Tidak ada' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if ($marker->path_patt)
                                <span class="text-xs text-green-600 font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Ada
                                </span>
                            @else
                                <span class="text-xs text-red-500">Tidak ada</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($marker->path_model)
                                <span class="text-xs text-green-600 font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Ada
                                </span>
                            @else
                                <span class="text-xs text-amber-500">Opsional</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.markers.edit', $marker) }}"
                                    class="rounded-lg border border-blue-200 bg-white px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50 hover:border-blue-400 transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.markers.destroy', $marker) }}"
                                    onsubmit="return confirm('Hapus marker &quot;{{ $marker->nama ?? $marker->marker_id }}&quot;?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 hover:border-red-400 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm">Belum ada marker AR.</span>
                                <a href="{{ route('admin.markers.create') }}" class="text-[#c25c06] font-semibold hover:underline text-sm">Upload yang pertama →</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-[#ffac00]/10 rounded-xl border border-[#ffac00]/20 p-5">
    <h3 class="font-bold text-[#800000] mb-2 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Panduan Marker AR
    </h3>
    <ul class="text-sm text-gray-700 space-y-1">
        <li>1. Upload gambar dengan kontras tinggi dan detail visual yang jelas</li>
        <li>2. Hindari gambar dengan warna datar atau pola berulang</li>
        <li>3. Resolusi gambar minimal 512×512 pixel</li>
        <li>4. Setelah upload, file .patt dan PNG akan dibuat secara otomatis</li>
        <li>5. Unduh marker image (PNG siap-cetak) dalam bentuk ZIP di halaman Simulasi Bencana</li>
    </ul>
</div>

@endsection
