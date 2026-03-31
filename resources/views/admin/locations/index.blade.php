@extends('admin.layout')

@section('title', 'Kelola Lokasi Peta')

@section('content')
    <div class="flex flex-col gap-4">
        <a href="{{ route('admin.disasters.index') }}"
            class="text-sm font-bold text-[#ffac00] hover:underline">← Kembali ke Daftar</a>

        <h2 class="text-center text-lg font-extrabold text-[#ffac00]">KELOLA PETA BENCANA</h2>

        <!-- Map -->
        <div id="map" class="h-64 w-full rounded border-2 border-[#800000]"></div>

        <!-- Add Location Form -->
        <form method="POST" action="{{ route('admin.locations.store') }}" class="flex flex-col gap-2">
            @csrf

            <h3 class="text-sm font-extrabold text-[#ffac00]">TAMBAH LOKASI BARU</h3>

            <div>
                <label class="mb-1 block text-xs font-bold text-[#ffac00]">Jenis Bencana</label>
                <select name="disaster_id"
                    class="w-full rounded border-2 border-[#800000] bg-white/90 p-2 text-sm text-[#2f0000] focus:outline-none focus:ring-2 focus:ring-[#ffac00]">
                    @foreach ($disasters as $disaster)
                        <option value="{{ $disaster->id }}">{{ $disaster->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold text-[#ffac00]">Nama Lokasi</label>
                <input type="text" name="location_name" required
                    placeholder="Contoh: Gitgit"
                    class="w-full rounded border-2 border-[#800000] bg-white/90 p-2 text-sm text-[#2f0000] focus:outline-none focus:ring-2 focus:ring-[#ffac00]">
            </div>

            <div class="flex gap-2">
                <div class="flex-1">
                    <label class="mb-1 block text-xs font-bold text-[#ffac00]">Latitude</label>
                    <input type="number" step="0.0000001" name="latitude" id="lat-input" required
                        placeholder="-8.24"
                        class="w-full rounded border-2 border-[#800000] bg-white/90 p-2 text-sm text-[#2f0000] focus:outline-none focus:ring-2 focus:ring-[#ffac00]">
                </div>
                <div class="flex-1">
                    <label class="mb-1 block text-xs font-bold text-[#ffac00]">Longitude</label>
                    <input type="number" step="0.0000001" name="longitude" id="lng-input" required
                        placeholder="115.12"
                        class="w-full rounded border-2 border-[#800000] bg-white/90 p-2 text-sm text-[#2f0000] focus:outline-none focus:ring-2 focus:ring-[#ffac00]">
                </div>
            </div>

            <button type="submit"
                class="rounded border-2 border-[#800000] bg-[#ffac00] px-4 py-2 text-sm font-extrabold text-[#800000] transition-transform hover:scale-105 active:scale-95">
                + TAMBAH LOKASI
            </button>
        </form>

        <!-- Existing Locations -->
        @foreach ($disasters as $disaster)
            @if ($disaster->locations->isNotEmpty())
                <div>
                    <h3 class="mb-2 rounded bg-[#800000] px-3 py-1 text-xs font-extrabold text-[#ffac00]">{{ $disaster->name }}</h3>
                    @foreach ($disaster->locations as $location)
                        <div class="mb-1 flex items-center justify-between rounded bg-white/70 px-3 py-2">
                            <span class="text-sm font-bold text-[#2f0000]">
                                {{ $location->location_name }}
                                <span class="text-xs font-normal text-[#800000]">
                                    ({{ $location->latitude }}, {{ $location->longitude }})
                                </span>
                            </span>
                            <form method="POST" action="{{ route('admin.locations.destroy', $location) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Hapus lokasi ini?')"
                                    class="rounded bg-red-500 px-2 py-1 text-xs font-bold text-white hover:bg-red-600">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('map', {
                center: [-8.2, 115.0],
                zoom: 9,
                zoomControl: true
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Disaster type colors
            const colors = {
                'banjir': '#1e40af',
                'tanah-longsor': '#78350f',
                'gempa-bumi': '#991b1b',
                'tsunami': '#0c4a6e',
                'angin-puting-beliung': '#065f46',
            };

            // Existing markers from server
            const locations = @json($disasters->pluck('locations')->flatten());

            locations.forEach(function(loc) {
                const color = colors[loc.disaster.slug] || '#800000';
                const icon = L.divIcon({
                    html: `<div style="background-color:${color};width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 0 4px rgba(0,0,0,0.5);"></div>`,
                    iconSize: [14, 14],
                    className: ''
                });
                L.marker([loc.latitude, loc.longitude], { icon: icon })
                    .addTo(map)
                    .bindPopup(`<strong>${loc.disaster.name}</strong><br>${loc.location_name}`);
            });

            // Click to set coordinates
            map.on('click', function(e) {
                document.getElementById('lat-input').value = e.latlng.lat.toFixed(7);
                document.getElementById('lng-input').value = e.latlng.lng.toFixed(7);
            });
        });
    </script>
@endsection
