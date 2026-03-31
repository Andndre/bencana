@extends('admin.layout')

@section('title', 'Kelola Bencana')

@section('content')
    <div class="flex flex-col gap-3">
        <a href="{{ route('admin.locations') }}"
            class="rounded border-2 border-[#800000] bg-[#ffac00] px-4 py-2 text-center text-sm font-extrabold text-[#800000] transition-transform hover:scale-105 active:scale-95">
            KELOLA PETA BENCANA
        </a>

        <h2 class="mt-2 text-center text-lg font-extrabold text-[#ffac00]">DAFTAR BENCANA</h2>

        @forelse ($disasters as $disaster)
            <div class="rounded border-2 border-[#800000] bg-white/80 p-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-[#800000]">{{ $disaster->name }}</h3>
                        <p class="text-xs text-[#2f0000]">{{ $disaster->mitigation_steps_count }} langkah mitigasi &bull; {{ $disaster->locations_count }} lokasi</p>
                    </div>
                    <a href="{{ route('admin.disasters.edit', $disaster) }}"
                        class="rounded bg-[#ffac00] px-3 py-1 text-xs font-bold text-[#800000] hover:bg-amber-400">
                        Edit
                    </a>
                </div>
                @if ($disaster->description)
                    <p class="mt-1 text-xs text-[#2f0000]">{{ Str::limit($disaster->description, 80) }}</p>
                @endif
            </div>
        @empty
            <p class="text-center text-sm text-white">Belum ada data bencana.</p>
        @endforelse
    </div>
@endsection
