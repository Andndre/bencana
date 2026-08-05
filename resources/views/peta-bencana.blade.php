<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Peta Bencana - BENCANA ALAM</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @vite(['resources/css/app.css'])

    <style>
        #map {
            width: 100%;
            height: 100%;
        }
    </style>
</head>

<body class="phone-shell">
    <div class="phone-frame">

        <div id="page"
            class="absolute inset-0 flex flex-col items-center justify-start transition-opacity duration-500 ease-in-out">
            <!-- Background with opacity -->
            <img src="{{ asset('images/marker bg.webp') }}" alt=""
                class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-50">
            <!-- Semi-transparent overlay -->
            <div class="absolute inset-0 bg-black/30"></div>

            <!-- Header -->
            <div class="z-1000 relative flex w-full items-center justify-center bg-[#ffac00] px-4 py-3 shadow-md">
                <a href="{{ route('home') }}" class="absolute left-4">
                    <img src="{{ asset('images/left-arrow.webp') }}" alt="" class="w-4">
                </a>
                <h1 class="text-center text-xl font-extrabold tracking-wide text-[#800000]">PETA BENCANA</h1>
            </div>

            <!-- Filter jenis bencana -->
            <div class="relative z-[900] w-full shrink-0 bg-[#ffac00]/95 px-3 py-2">
                <div class="flex gap-2 overflow-x-auto pb-1">
                    <button type="button" data-filter="all" data-active="true"
                        class="filter-chip shrink-0 rounded-full border-2 border-[#800000] bg-[#800000] px-3 py-1 text-xs font-extrabold whitespace-nowrap text-white">
                        Semua
                    </button>
                    @foreach ($disasters as $disaster)
                        <button type="button" data-filter="{{ $disaster->slug }}" data-active="false"
                            class="filter-chip shrink-0 rounded-full border-2 border-[#800000] bg-white px-3 py-1 text-xs font-extrabold whitespace-nowrap text-[#800000]">
                            <span class="mr-1 inline-block h-2 w-2 rounded-full align-middle"
                                style="background-color: {{ $disaster->color }}"></span>{{ $disaster->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Map Container -->
            <div class="relative z-10 w-full flex-1">
                <div id="map" class="h-full w-full"></div>

                <!-- Tombol lokasi saya -->
                <button type="button" id="locate-btn" title="Lokasi saya"
                    class="absolute bottom-4 left-3 z-[800] flex items-center justify-center rounded-full bg-white p-2.5 text-[#800000] shadow-lg active:scale-95 transition-transform">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </button>

                <!-- Legenda -->
                <details id="legend" class="absolute right-3 top-3 z-[800] rounded-lg bg-white/95 text-xs shadow-lg">
                    <summary class="cursor-pointer list-none px-3 py-2 font-extrabold text-[#800000]">Legenda ▾</summary>
                    <div class="space-y-1 px-3 pb-3">
                        @foreach ($disasters as $disaster)
                            <div class="flex items-center gap-2 whitespace-nowrap text-[#2f0000]">
                                <span class="inline-block h-3 w-3 rounded-full border border-white shadow"
                                    style="background-color: {{ $disaster->color }}"></span>
                                {{ $disaster->name }}
                            </div>
                        @endforeach
                        @if ($disasters->isEmpty())
                            <p class="text-gray-500">Belum ada data lokasi.</p>
                        @endif
                    </div>
                </details>

                <p id="locate-status" class="absolute bottom-16 left-3 z-[800] hidden rounded-lg bg-red-600/90 px-3 py-1.5 text-xs text-white shadow"></p>
            </div>
        </div>

    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('map', {
                center: [-8.2, 115.0],
                zoom: 9,
                zoomControl: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            L.control.zoom({
                position: 'bottomright'
            }).addTo(map);

            var colors = @json(\App\Models\Disaster::COLORS);
            var locations = @json($locations);
            var mitigasiUrl = '{{ route('penanggulangan-bencana') }}';

            // Satu layer group per jenis bencana supaya bisa difilter.
            var layers = {};

            locations.forEach(function(loc) {
                var slug = loc.disaster.slug;
                var color = colors[slug] || '{{ \App\Models\Disaster::DEFAULT_COLOR }}';

                var icon = L.divIcon({
                    html: '<div style="background-color:' + color + ';width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 0 4px rgba(0,0,0,0.5);"></div>',
                    iconSize: [14, 14],
                    className: ''
                });

                var popup =
                    '<div style="min-width:170px">' +
                    '<strong style="color:' + color + '">' + loc.disaster.name + '</strong><br>' +
                    '<span style="color:#2f0000">' + loc.location_name + '</span>' +
                    '<div style="display:flex;gap:6px;margin-top:8px">' +
                    '<a href="https://www.google.com/maps/dir/?api=1&destination=' + loc.latitude + ',' + loc.longitude + '"' +
                    ' target="_blank" rel="noopener"' +
                    ' style="flex:1;text-align:center;background:#c25c06;color:#fff;padding:5px 8px;border-radius:6px;font-weight:700;text-decoration:none">Rute</a>' +
                    '<a href="' + mitigasiUrl + '#' + slug + '"' +
                    ' style="flex:1;text-align:center;background:#ffac00;color:#800000;padding:5px 8px;border-radius:6px;font-weight:700;text-decoration:none">Mitigasi</a>' +
                    '</div></div>';

                layers[slug] = layers[slug] || L.layerGroup().addTo(map);
                L.marker([loc.latitude, loc.longitude], { icon: icon })
                    .bindPopup(popup)
                    .addTo(layers[slug]);
            });

            // Filter chip
            document.querySelectorAll('.filter-chip').forEach(function(chip) {
                chip.addEventListener('click', function() {
                    document.querySelectorAll('.filter-chip').forEach(function(other) {
                        var active = other === chip;
                        other.dataset.active = active ? 'true' : 'false';
                        other.classList.toggle('bg-[#800000]', active);
                        other.classList.toggle('text-white', active);
                        other.classList.toggle('bg-white', !active);
                        other.classList.toggle('text-[#800000]', !active);
                    });

                    var filter = chip.dataset.filter;
                    Object.keys(layers).forEach(function(slug) {
                        var show = filter === 'all' || filter === slug;
                        if (show) {
                            map.addLayer(layers[slug]);
                        } else {
                            map.removeLayer(layers[slug]);
                        }
                    });
                });
            });

            // Lokasi pengguna
            var userMarker = null;
            var status = document.getElementById('locate-status');

            function showStatus(message) {
                status.textContent = message;
                status.classList.toggle('hidden', !message);
            }

            document.getElementById('locate-btn').addEventListener('click', function() {
                if (!navigator.geolocation) {
                    showStatus('Browser tidak mendukung geolokasi.');
                    return;
                }
                showStatus('');
                navigator.geolocation.getCurrentPosition(function(pos) {
                    var latlng = [pos.coords.latitude, pos.coords.longitude];
                    if (userMarker) map.removeLayer(userMarker);
                    userMarker = L.circleMarker(latlng, {
                        radius: 8,
                        color: '#fff',
                        weight: 3,
                        fillColor: '#2563eb',
                        fillOpacity: 1
                    }).addTo(map).bindPopup('Lokasi Anda');
                    map.setView(latlng, 13);
                }, function() {
                    showStatus('Gagal mengambil lokasi. Aktifkan izin lokasi.');
                });
            });
        });
    </script>
</body>

</html>
