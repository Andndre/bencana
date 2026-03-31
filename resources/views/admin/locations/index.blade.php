@extends('admin._layout')

@section('title', 'Kelola Lokasi Peta')
@section('page-title', 'Kelola Lokasi Peta')
@section('page-subtitle', 'Tambah, edit, dan hapus marker lokasi bencana di peta')

@section('header-actions')
    <a href="{{ route('admin.disasters.index') }}"
        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
        ← Kembali
    </a>
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 h-[calc(100vh-180px)] min-h-[500px]">

    {{-- Left: Map + Add Form --}}
    <div class="lg:col-span-2 flex flex-col gap-4">

        {{-- Map --}}
        <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden relative">
            <div id="admin-map" class="w-full h-full"></div>
            <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur rounded-lg px-3 py-1.5 text-xs text-gray-600 shadow">
                Klik peta untuk memilih koordinat
            </div>
        </div>

        {{-- Add Form --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex-shrink-0">
            <h3 class="font-bold text-gray-800 mb-4">Tambah Lokasi Baru</h3>

            <form method="POST" action="{{ route('admin.locations.store') }}" class="space-y-3">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis Bencana <span class="text-red-500">*</span></label>
                    <select name="disaster_id" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20 @error('disaster_id') border-red-500 @enderror">
                        @foreach (\App\Models\Disaster::all() as $disaster)
                            <option value="{{ $disaster->id }}">{{ $disaster->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Lokasi <span class="text-red-500">*</span></label>
                    <input type="text" name="location_name" id="location-name-input" required
                        placeholder="Contoh: Gitgit"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20 @error('location_name') border-red-500 @enderror">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Latitude <span class="text-red-500">*</span></label>
                        <input type="number" step="0.0000001" name="latitude" id="lat-input" required
                            placeholder="-8.2400"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20 @error('latitude') border-red-500 @enderror">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Longitude <span class="text-red-500">*</span></label>
                        <input type="number" step="0.0000001" name="longitude" id="lng-input" required
                            placeholder="115.1200"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20 @error('longitude') border-red-500 @enderror">
                    </div>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-[#c25c06] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#a04a05] transition-colors">
                    + Tambah Lokasi
                </button>
            </form>
        </div>
    </div>

    {{-- Right: Locations Table --}}
    <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-gray-200 flex-shrink-0">
            <h3 class="font-bold text-gray-800">Daftar Lokasi</h3>
            <p class="text-xs text-gray-500 mt-0.5">Total: {{ \App\Models\DisasterLocation::count() }} lokasi</p>
        </div>

        <div class="flex-1 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 sticky top-0">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase">Lokasi</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase">Bencana</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase">Koordinat</th>
                        <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="locations-body">
                    @forelse ($disasters as $disaster)
                        @foreach ($disaster->locations as $location)
                            <tr id="view-row-{{ $location->id }}"
                                data-lat="{{ $location->latitude }}"
                                data-lng="{{ $location->longitude }}"
                                data-name="{{ $location->location_name }}"
                                data-disaster="{{ $disaster->name }}"
                                data-disaster-color="{{ $disaster->slug }}">
                                <td class="px-5 py-3.5">
                                    <span class="font-semibold text-gray-900 view-name">{{ $location->location_name }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center rounded-full bg-[#c25c06]/10 px-2.5 py-0.5 text-xs font-bold text-[#c25c06] view-disaster">
                                        {{ $disaster->name }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <code class="text-xs text-gray-500 view-coords">{{ number_format($location->latitude, 4) }}, {{ number_format($location->longitude, 4) }}</code>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button"
                                            onclick="focusMarker({{ $location->latitude }}, {{ $location->longitude }})"
                                            class="rounded-lg border border-blue-200 bg-white px-2 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50 transition-colors"
                                            title="Tampilkan di peta">
                                            📍
                                        </button>
                                        <button type="button" onclick="showEdit({{ $location->id }})"
                                            class="rounded-lg border border-yellow-200 bg-white px-2 py-1 text-xs font-semibold text-yellow-600 hover:bg-yellow-50 transition-colors">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('admin.locations.destroy', $location) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                onclick="return confirm('Hapus lokasi &quot;{{ $location->location_name }}&quot;?')"
                                                class="rounded-lg border border-red-200 bg-white px-2 py-1 text-xs font-semibold text-red-500 hover:bg-red-50 transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Edit row (hidden by default) --}}
                            <tr id="edit-row-{{ $location->id }}" class="hidden bg-amber-50/50">
                                <td colspan="4" class="px-5 py-4">
                                    <form method="POST" action="{{ route('admin.locations.update', $location) }}" id="edit-form-{{ $location->id }}" onsubmit="submitEdit(event, {{ $location->id }})">
                                        @csrf
                                        @method('PUT')
                                        <div class="flex flex-wrap items-end gap-3">
                                            <div class="flex-1 min-w-[140px]">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Lokasi</label>
                                                <input type="text" name="location_name" required
                                                    value="{{ $location->location_name }}"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20">
                                            </div>
                                            <div class="w-36">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">Bencana</label>
                                                <select name="disaster_id" required
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20">
                                                    @foreach (\App\Models\Disaster::all() as $d)
                                                        <option value="{{ $d->id }}" {{ $d->id == $disaster->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="w-32">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">Latitude</label>
                                                <input type="number" step="0.0000001" name="latitude" required
                                                    value="{{ $location->latitude }}"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20">
                                            </div>
                                            <div class="w-32">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">Longitude</label>
                                                <input type="number" step="0.0000001" name="longitude" required
                                                    value="{{ $location->longitude }}"
                                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20">
                                            </div>
                                            <div class="flex gap-2 flex-shrink-0">
                                                <button type="submit"
                                                    class="rounded-lg bg-[#c25c06] px-4 py-2 text-xs font-bold text-white hover:bg-[#a04a05] transition-colors">
                                                    Simpan
                                                </button>
                                                <button type="button" onclick="cancelEdit({{ $location->id }})"
                                                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 transition-colors">
                                                    Batal
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-gray-400 text-sm">Belum ada lokasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const colors = {
        'banjir': '#1e40af',
        'tanah-longsor': '#78350f',
        'gempa-bumi': '#991b1b',
        'tsunami': '#0c4a6e',
        'angin-puting-beliung': '#065f46',
    };

    const allLocations = @json($disasters->pluck('locations')->flatten());

    var map = L.map('admin-map', {
        center: [-8.2, 115.0],
        zoom: 9,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    window.mapMarkers = [];

    function addMarkers() {
        window.mapMarkers.forEach(m => map.removeLayer(m));
        window.mapMarkers = [];

        allLocations.forEach(function(loc) {
            const color = colors[loc.disaster.slug] || '#800000';
            const icon = L.divIcon({
                html: `<div style="background-color:${color};width:14px;height:14px;border-radius:50%;border:3px solid white;box-shadow:0 0 6px rgba(0,0,0,0.4);"></div>`,
                iconSize: [14, 14],
                className: ''
            });
            const marker = L.marker([loc.latitude, loc.longitude], { icon: icon })
                .addTo(map)
                .bindPopup(`<strong>${loc.disaster.name}</strong><br>${loc.location_name}`);
            window.mapMarkers.push(marker);
        });
    }

    function focusMarker(lat, lng) {
        map.setView([lat, lng], 14);
    }

    function showEdit(id) {
        document.getElementById('view-row-' + id).classList.add('hidden');
        document.getElementById('edit-row-' + id).classList.remove('hidden');
    }

    function cancelEdit(id) {
        document.getElementById('edit-row-' + id).classList.add('hidden');
        document.getElementById('view-row-' + id).classList.remove('hidden');
    }

    function submitEdit(e, id) {
        // Allow normal form submission
    }

    window.focusMarker = focusMarker;

    addMarkers();

    map.on('click', function(e) {
        document.getElementById('lat-input').value = e.latlng.lat.toFixed(7);
        document.getElementById('lng-input').value = e.latlng.lng.toFixed(7);
    });
</script>
@endsection
