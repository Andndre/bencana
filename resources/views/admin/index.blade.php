@extends('admin._layout')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Selamat datang di panel administrasi')

@section('header-actions')
    <a href="{{ route('admin.disasters.index') }}"
        class="inline-flex items-center gap-2 rounded-lg bg-[#c25c06] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a04a05] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Kelola Bencana
    </a>
@endsection

@section('content')

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    {{-- Card: Total Bencana --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-5">
        <div class="w-14 h-14 rounded-xl bg-[#c25c06]/10 flex items-center justify-center flex-shrink-0">
            <svg class="w-7 h-7 text-[#c25c06]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
        </div>
        <div>
            <div class="text-3xl font-extrabold text-gray-900">{{ $disasterCount }}</div>
            <div class="text-sm text-gray-500 mt-0.5">Total Jenis Bencana</div>
        </div>
    </div>

    {{-- Card: Total Lokasi --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-5">
        <div class="w-14 h-14 rounded-xl bg-[#800000]/10 flex items-center justify-center flex-shrink-0">
            <svg class="w-7 h-7 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <div class="text-3xl font-extrabold text-gray-900">{{ $locationCount }}</div>
            <div class="text-sm text-gray-500 mt-0.5">Total Lokasi Peta</div>
        </div>
    </div>

    {{-- Card: Quick Actions --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-sm font-semibold text-gray-700 mb-3">Aksi Cepat</div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.disasters.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-[#ffac00] px-3 py-1.5 text-xs font-bold text-[#800000] hover:bg-amber-400 transition-colors">
                + Bencana Baru
            </a>
            <a href="{{ route('admin.locations') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-200 transition-colors">
                Kelola Lokasi
            </a>
            <a href="{{ route('peta-bencana') }}" target="_blank"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-200 transition-colors">
                Lihat Peta
            </a>
        </div>
    </div>
</div>

{{-- Recent disasters quick view --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="font-bold text-gray-800">Jenis Bencana</h2>
        <a href="{{ route('admin.disasters.index') }}"
            class="text-sm text-[#c25c06] font-semibold hover:underline">Lihat Semua →</a>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse(\App\Models\Disaster::withCount('locations', 'mitigationSteps')->limit(5)->get() as $disaster)
            <div class="px-6 py-3.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                <div>
                    <div class="font-semibold text-gray-900">{{ $disaster->name }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">
                        {{ $disaster->mitigation_steps_count }} langkah mitigasi &bull; {{ $disaster->locations_count }} lokasi
                    </div>
                </div>
                <a href="{{ route('admin.disasters.edit', $disaster) }}"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                    Edit
                </a>
            </div>
        @empty
            <div class="px-6 py-8 text-center text-gray-400 text-sm">Belum ada data bencana.</div>
        @endforelse
    </div>
</div>

@endsection
