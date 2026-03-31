@extends('admin._layout')

@section('title', 'Edit ' . $disaster->name)
@section('page-title', 'Edit: ' . $disaster->name)
@section('page-subtitle', 'Edit deskripsi dan langkah mitigasi')

@section('header-actions')
    <a href="{{ route('admin.disasters.index') }}"
        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
        ← Kembali ke Daftar
    </a>
@endsection

@section('content')

<form id="main-form" method="POST" action="{{ route('admin.disasters.update', $disaster) }}">
@csrf
@method('PUT')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Description + Info --}}
    <div class="lg:col-span-1 space-y-6">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-bold text-gray-800 mb-4">Informasi Dasar</h3>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama</label>
                <input type="text" value="{{ $disaster->name }}" disabled
                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 cursor-not-allowed">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Slug</label>
                <code class="block w-full rounded-lg border border-gray-200 bg-gray-200 px-4 py-2.5 text-sm text-gray-700">{{ $disaster->slug }}</code>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi</label>
                <textarea name="description" rows="4"
                    placeholder="Masukkan deskripsi..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20 resize-none">{{ old('description', $disaster->description) }}</textarea>
            </div>
        </div>

        {{-- Stats --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-bold text-gray-800 mb-4">Statistik</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Langkah Mitigasi</span>
                    <span class="font-bold text-[#c25c06]">{{ $disaster->mitigationSteps->count() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Lokasi di Peta</span>
                    <span class="font-bold text-[#800000]">{{ $disaster->locations->count() }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Right: Mitigation Steps --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-bold text-gray-800">Langkah Mitigasi</h3>
            </div>

            <div class="p-6 space-y-0">

                @foreach (['pra' => 'Pra-Bencana', 'saat' => 'Saat Terjadi', 'pasca' => 'Pasca-Bencana'] as $phase => $label)
                    <div class="@if(!$loop->first) pt-6 mt-6 border-t border-gray-100 @endif">

                        <div class="flex items-center gap-3 mb-3">
                            <span class="inline-block w-2 h-2 rounded-full @if($phase === 'pra') bg-green-500 @elseif($phase === 'saat') bg-yellow-500 @else bg-blue-500 @endif"></span>
                            <h4 class="font-bold text-gray-800">{{ $label }}</h4>
                            <span class="text-xs text-gray-400">({{ $disaster->mitigationSteps->where('phase', $phase)->count() }} langkah)</span>
                        </div>

                        {{-- Existing steps --}}
                        <div class="steps-list space-y-2 mb-3">
                            @forelse ($disaster->mitigationSteps->where('phase', $phase) as $step)
                                <div class="flex items-start gap-2">
                                    <input type="text"
                                        name="steps[{{ $step->id }}][content]"
                                        value="{{ old("steps.{$step->id}.content", $step->content) }}"
                                        class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20">
                                    <a href="{{ route('admin.disasters.steps.destroy', $step) }}"
                                        onclick="event.preventDefault(); if(confirm('Hapus langkah ini?')) { fetch(this.href, {method:'DELETE', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'turbo-stream'}}).then(() => location.reload()); }"
                                        class="rounded-lg border border-red-200 bg-white px-2.5 py-2 text-xs font-semibold text-red-500 hover:bg-red-50 hover:border-red-400 transition-colors flex-shrink-0">
                                        ✕
                                    </a>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 italic py-1">Belum ada langkah.</p>
                            @endforelse
                        </div>

                        {{-- Add new --}}
                        <div class="flex items-center gap-2">
                            <input type="text"
                                id="new-step-{{ $phase }}"
                                placeholder="Ketik langkah baru, lalu klik +..."
                                class="flex-1 rounded-lg border border-dashed border-gray-300 px-3 py-2 text-sm text-gray-700 placeholder-gray-400 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20">
                            <button type="button" onclick="addStep('{{ $phase }}')"
                                class="rounded-lg border border-[#c25c06] bg-white px-3 py-2 text-sm font-bold text-[#c25c06] hover:bg-[#c25c06] hover:text-white transition-colors flex-shrink-0">
                                +
                            </button>
                        </div>
                    </div>
                @endforeach

            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex items-center gap-3">
                <button type="submit"
                    class="rounded-lg bg-[#c25c06] px-6 py-2.5 text-sm font-bold text-white hover:bg-[#a04a05] transition-colors">
                    Simpan Semua
                </button>
            </div>
        </div>
    </div>

</div>
</form>

@endsection

@section('scripts')
<script>
    function addStep(phase) {
        var input = document.getElementById('new-step-' + phase);
        var value = input.value.trim();
        if (!value) return;

        var form = document.getElementById('main-form');
        var id = 'new_' + Date.now();

        // Hidden field for backend
        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'new_steps[' + phase + '][]';
        hidden.value = value;
        form.appendChild(hidden);

        // Visual row inserted into steps-list
        var stepsList = input.closest('div').previousElementSibling;
        var row = document.createElement('div');
        row.className = 'flex items-start gap-2';
        row.innerHTML =
            '<input type="text" value="' + value.replace(/"/g, '&quot;') + '" readonly' +
            ' class="flex-1 rounded-lg border border-green-300 bg-green-50 px-3 py-2 text-sm text-gray-700 cursor-default">' +
            '<button type="button" onclick="this.closest(\'div\').remove()" class="rounded-lg border border-red-200 bg-white px-2.5 py-2 text-xs font-semibold text-red-500 hover:bg-red-50 flex-shrink-0">✕</button>';
        stepsList.appendChild(row);

        input.value = '';
        input.focus();
    }
</script>
@endsection
