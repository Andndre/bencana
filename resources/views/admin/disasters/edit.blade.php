@extends('admin.layout')

@section('title', 'Edit ' . $disaster->name)

@section('content')
    <form method="POST" action="{{ route('admin.disasters.update', $disaster) }}">
        @csrf
        @method('PUT')

        <div class="flex flex-col gap-4">
            <a href="{{ route('admin.disasters.index') }}"
                class="text-sm font-bold text-[#ffac00] hover:underline">← Kembali ke Daftar</a>

            <h2 class="text-center text-lg font-extrabold text-[#ffac00]">{{ $disaster->name }}</h2>

            <!-- Description -->
            <div>
                <label class="mb-1 block text-sm font-bold text-[#ffac00]">Deskripsi</label>
                <textarea name="description" rows="4"
                    class="w-full rounded border-2 border-[#800000] bg-white/90 p-2 text-sm text-[#2f0000] focus:outline-none focus:ring-2 focus:ring-[#ffac00]">{{ old('description', $disaster->description) }}</textarea>
            </div>

            <!-- Mitigation Steps by Phase -->
            @foreach (['pra' => 'Pra-Bencana', 'saat' => 'Saat Terjadi', 'pasca' => 'Pasca-Bencana'] as $phase => $label)
                <div>
                    <h3 class="mb-2 rounded bg-[#ffac00] px-3 py-1 text-sm font-extrabold text-[#800000]">{{ $label }}</h3>

                    @foreach ($disaster->mitigationSteps->where('phase', $phase) as $step)
                        <div class="mb-2 flex items-start gap-2">
                            <input type="hidden" name="steps[{{ $step->id }}][content]" value="">
                            <input type="text"
                                name="steps[{{ $step->id }}][content]"
                                value="{{ old("steps.{$step->id}.content", $step->content) }}"
                                class="flex-1 rounded border border-[#800000] bg-white/90 p-2 text-sm text-[#2f0000] focus:outline-none focus:ring-1 focus:ring-[#ffac00]">
                            <form method="POST" action="{{ route('admin.disasters.steps.destroy', $step) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Hapus langkah ini?')"
                                    class="rounded bg-red-500 px-2 py-1 text-xs font-bold text-white hover:bg-red-600">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    @endforeach

                    <!-- Add new steps for this phase -->
                    <p class="mb-1 text-xs font-bold text-[#ffac00]">+ Tambah langkah baru:</p>
                    @for ($i = 0; $i < 3; $i++)
                        <input type="text"
                            name="new_steps[{{ $phase }}][]"
                            placeholder="Langkah baru..."
                            class="mb-1 w-full rounded border border-dashed border-[#ffac00] bg-white/60 p-2 text-sm text-[#2f0000] placeholder-[#800000]/40 focus:outline-none focus:ring-1 focus:ring-[#ffac00]">
                    @endfor
                </div>
            @endforeach

            <button type="submit"
                class="rounded border-2 border-[#800000] bg-[#ffac00] px-6 py-2 text-center font-extrabold text-[#800000] transition-transform hover:scale-105 active:scale-95">
                SIMPAN PERUBAHAN
            </button>
        </div>
    </form>

@endsection
