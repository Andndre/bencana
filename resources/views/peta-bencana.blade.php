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

<body class="flex h-dvh w-screen justify-center overflow-hidden bg-black font-sans">
    <div class="max-w-110 relative h-full w-full overflow-hidden shadow-2xl">

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
                    <img src="{{ asset('images/info.webp') }}" alt="Kembali" class="w-8 rotate-180">
                </a>
                <h1 class="text-center text-xl font-extrabold tracking-wide text-[#800000]">PETA BENCANA</h1>
            </div>

            <!-- Map Container -->
            <div id="map" class="relative z-10 mt-0 w-full flex-1"></div>
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

            var colors = {
                'banjir': '#1e40af',
                'tanah-longsor': '#78350f',
                'gempa-bumi': '#991b1b',
                'tsunami': '#0c4a6e',
                'angin-puting-beliung': '#065f46',
            };

            var locations = @json($locations);

            locations.forEach(function(loc) {
                var color = colors[loc.disaster.slug] || '#800000';
                var icon = L.divIcon({
                    html: '<div style="background-color:' + color + ';width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 0 4px rgba(0,0,0,0.5);"></div>',
                    iconSize: [14, 14],
                    className: ''
                });
                L.marker([loc.latitude, loc.longitude], { icon: icon })
                    .addTo(map)
                    .bindPopup('<strong style="color:' + color + '">' + loc.disaster.name + '</strong><br>' + loc.location_name);
            });
        });
    </script>
</body>

</html>
