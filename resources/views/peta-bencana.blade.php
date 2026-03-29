<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Peta Bencana - BENCANA ALAM</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @vite(['resources/css/app.css'])

    <style>
        #map { width: 100%; height: 100%; }
    </style>
</head>

<body class="flex h-screen w-screen justify-center overflow-hidden bg-black font-sans">
    <div class="max-w-110 relative h-full w-full overflow-hidden shadow-2xl">

        <div id="page"
            class="absolute inset-0 flex flex-col items-center justify-start transition-opacity duration-500 ease-in-out">
            <!-- Background with opacity -->
            <img src="{{ asset('images/marker bg.webp') }}" alt=""
                class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-50">
            <!-- Semi-transparent overlay -->
            <div class="absolute inset-0 bg-black/30"></div>

            <!-- Header -->
            <div class="relative z-1000 flex w-full items-center justify-center bg-[#ffac00] px-4 py-3 shadow-md">
                <h1 class="text-center text-xl font-extrabold tracking-wide text-[#800000]">PETA BENCANA</h1>
            </div>

            <!-- Map Container -->
            <div id="map" class="relative z-10 mt-0 w-full flex-1"></div>
        </div>

    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var map = L.map('map', {
                center: [-8.4, 115.2],
                zoom: 9,
                zoomControl: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            L.control.zoom({ position: 'bottomright' }).addTo(map);
        });
    </script>
</body>

</html>
