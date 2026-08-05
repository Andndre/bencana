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

<div class="grid grid-cols-1 lg:grid-cols-5 gap-4 lg:gap-6 lg:h-[calc(100vh-180px)] lg:min-h-[500px]">

    {{-- Left: Map --}}
    <div class="lg:col-span-2 flex flex-col lg:h-full">

        {{-- Map --}}
        <div id="map-card" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col flex-1">

            {{-- Search + GPS --}}
            <div class="border-b border-gray-200 p-3 space-y-2">
                <div class="flex gap-2">
                    <input type="text" id="geocode-input" placeholder="Cari alamat, contoh: Kuta, Bali"
                        class="flex-1 min-w-0 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20">
                    <button type="button" id="geocode-btn"
                        class="flex-shrink-0 rounded-lg bg-[#800000] px-3 py-2 text-sm font-bold text-white hover:bg-[#6a0000] transition-colors">
                        Cari
                    </button>
                    <button type="button" id="gps-btn" title="Gunakan lokasi saya"
                        class="flex-shrink-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                        📡
                    </button>
                    <button type="button" id="add-btn" title="Isi data untuk pin titik baru"
                        class="flex-shrink-0 rounded-lg bg-[#c25c06] px-3 py-2 text-sm font-bold text-white hover:bg-[#a04a05] transition-colors">
                        +
                    </button>
                </div>
                <div id="geocode-results" class="hidden max-h-40 overflow-y-auto rounded-lg border border-gray-200 divide-y divide-gray-100"></div>
                <p id="geocode-status" class="hidden text-xs text-red-600"></p>
            </div>

            <div class="min-h-[400px] flex-1">
                <div id="admin-map" class="w-full h-full"></div>
            </div>
            <p class="border-t border-gray-100 px-3 py-1.5 text-[11px] text-gray-500">
                Klik peta untuk menambah titik · geser pin untuk memindahkan · klik pin untuk mengubah/hapus
                <span class="text-gray-400">· pencarian alamat oleh Nominatim/OpenStreetMap</span>
            </p>
        </div>
    </div>

    {{-- Isi popup peta: ditulis di Blade lalu di-clone JS (bukan string HTML di JavaScript) --}}
    <template id="tpl-add">
        <div class="w-56">
            <p class="mb-1.5 text-sm font-bold text-[#c25c06]">Titik Baru</p>
            <form method="POST" action="{{ route('admin.locations.store') }}" class="space-y-1.5">
                @csrf
                <input type="hidden" name="latitude" class="field-lat">
                <input type="hidden" name="longitude" class="field-lng">

                <label class="block text-[11px] font-semibold text-gray-600">Nama Lokasi</label>
                <input type="text" name="location_name" required placeholder="Contoh: Gitgit"
                    class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm text-gray-900 placeholder-gray-400 focus:border-[#c25c06] focus:outline-none">

                <label class="block text-[11px] font-semibold text-gray-600">Jenis Bencana</label>
                <select name="disaster_id" required
                    class="w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm text-gray-900 focus:border-[#c25c06] focus:outline-none">
                    @foreach ($disasters as $disaster)
                        <option value="{{ $disaster->id }}">{{ $disaster->name }}</option>
                    @endforeach
                </select>

                <p class="coords text-[11px] text-gray-500"></p>

                <div class="flex gap-2 pt-1">
                    <button type="submit"
                        class="flex-1 rounded-lg bg-[#c25c06] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#a04a05]">
                        Simpan
                    </button>
                    <button type="button" class="btn-cancel rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </template>

    <template id="tpl-edit">
        <div class="w-56">
            <p class="mb-1.5 text-sm font-bold text-[#800000]">Ubah Titik</p>
            <form method="POST" class="form-update space-y-1.5">
                @csrf
                @method('PUT')
                <input type="hidden" name="latitude" class="field-lat">
                <input type="hidden" name="longitude" class="field-lng">

                <label class="block text-[11px] font-semibold text-gray-600">Nama Lokasi</label>
                <input type="text" name="location_name" required
                    class="field-name w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm text-gray-900 focus:border-[#c25c06] focus:outline-none">

                <label class="block text-[11px] font-semibold text-gray-600">Jenis Bencana</label>
                <select name="disaster_id" required
                    class="field-disaster w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm text-gray-900 focus:border-[#c25c06] focus:outline-none">
                    @foreach ($disasters as $disaster)
                        <option value="{{ $disaster->id }}">{{ $disaster->name }}</option>
                    @endforeach
                </select>

                <p class="coords text-[11px] text-gray-500"></p>
                <p class="moved-hint hidden text-[11px] font-semibold text-amber-600">Posisi belum tersimpan.</p>

                <button type="submit"
                    class="w-full rounded-lg bg-[#c25c06] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#a04a05]">
                    Simpan
                </button>
            </form>

            <form method="POST" class="form-delete mt-2" onsubmit="return confirm('Hapus lokasi ini?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-full rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50">
                    Hapus
                </button>
            </form>
        </div>
    </template>

    {{-- Right: Locations Table --}}
    <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex-shrink-0 space-y-3">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-gray-800">Daftar Lokasi</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Total: {{ $locations->total() }} lokasi</p>
                </div>
                <a href="{{ route('admin.locations.export', request()->query()) }}"
                    class="flex-shrink-0 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                    Export CSV
                </a>
            </div>

            {{-- Search + filter --}}
            <form method="GET" action="{{ route('admin.locations') }}" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama lokasi..."
                    class="min-w-0 flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20">
                <select name="disaster_id"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-[#c25c06] focus:outline-none focus:ring-2 focus:ring-[#c25c06]/20">
                    <option value="">Semua Bencana</option>
                    @foreach ($disasters as $disaster)
                        <option value="{{ $disaster->id }}" @selected(request('disaster_id') == $disaster->id)>{{ $disaster->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="dir" value="{{ request('dir') }}">
                <button type="submit"
                    class="rounded-lg bg-[#c25c06] px-4 py-2 text-sm font-bold text-white hover:bg-[#a04a05] transition-colors">
                    Filter
                </button>
                @if (request('q') || request('disaster_id'))
                    <a href="{{ route('admin.locations') }}"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                        Reset
                    </a>
                @endif
            </form>

            {{-- Bulk action bar --}}
            <form id="bulk-form" method="POST" action="{{ route('admin.locations.bulk-destroy') }}"
                onsubmit="return confirm('Hapus semua lokasi yang dipilih?')">
                @csrf
                <div id="bulk-bar" class="hidden items-center gap-3 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2">
                    <span class="text-xs font-semibold text-amber-800"><span id="bulk-count">0</span> lokasi dipilih</span>
                    <button type="submit"
                        class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-red-700 transition-colors">
                        Hapus Terpilih
                    </button>
                </div>
            </form>
        </div>

        <div class="flex-1 overflow-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-3 w-10">
                            <input type="checkbox" id="check-all" class="rounded border-gray-300" aria-label="Pilih semua">
                        </th>
                        @foreach (['location_name' => 'Lokasi', 'disaster_id' => 'Bencana', 'latitude' => 'Koordinat'] as $column => $label)
                            <th class="px-3 sm:px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase whitespace-nowrap">
                                <a href="{{ route('admin.locations', array_merge(request()->query(), ['sort' => $column, 'dir' => request('sort') === $column && request('dir') !== 'desc' ? 'desc' : 'asc'])) }}"
                                    class="inline-flex items-center gap-1 hover:text-[#c25c06]">
                                    {{ $label }}
                                    @if (request('sort') === $column)
                                        <span>{{ request('dir') === 'desc' ? '▼' : '▲' }}</span>
                                    @endif
                                </a>
                            </th>
                        @endforeach
                        <th class="px-3 sm:px-5 py-3 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="locations-body">
                    @forelse ($locations as $location)
                        <tr>
                            <td class="px-3 py-3.5">
                                <input type="checkbox" name="ids[]" value="{{ $location->id }}" form="bulk-form"
                                    class="row-check rounded border-gray-300" aria-label="Pilih {{ $location->location_name }}">
                            </td>
                            <td class="px-3 sm:px-5 py-3.5">
                                <span class="font-semibold text-gray-900">{{ $location->location_name }}</span>
                            </td>
                            <td class="px-3 sm:px-5 py-3.5">
                                <span class="inline-flex items-center rounded-full bg-[#c25c06]/10 px-2.5 py-0.5 text-xs font-bold text-[#c25c06] whitespace-nowrap">
                                    {{ $location->disaster?->name }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-5 py-3.5">
                                <code class="text-xs text-gray-500 whitespace-nowrap">{{ number_format($location->latitude, 4) }}, {{ number_format($location->longitude, 4) }}</code>
                            </td>
                            <td class="px-3 sm:px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="focusMarker({{ $location->id }})"
                                        class="rounded-lg border border-blue-200 bg-white px-2 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50 transition-colors"
                                        title="Buka di peta">
                                        📍 Edit di peta
                                    </button>
                                    <button type="button"
                                        onclick="deleteLocation('{{ route('admin.locations.destroy', $location) }}', @js($location->location_name))"
                                        class="rounded-lg border border-red-200 bg-white px-2 py-1 text-xs font-semibold text-red-500 hover:bg-red-50 transition-colors">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-400 text-sm">Belum ada lokasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($locations->hasPages())
            <div class="border-t border-gray-200 px-4 py-3 flex-shrink-0">
                {{ $locations->links() }}
            </div>
        @endif
    </div>

</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const colors = @json(\App\Models\Disaster::COLORS);
    const allLocations = @json($mapLocations);

    const map = L.map('admin-map', {
        center: [-8.2, 115.0],
        zoom: 9,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const updateUrl = '{{ route('admin.locations.update', ['location' => '__ID__']) }}';
    const destroyUrl = '{{ route('admin.locations.destroy', ['location' => '__ID__']) }}';

    // Popup tampil utuh tanpa scroll: peta digeser sendiri agar muat (tinggi peta minimal 400px).
    const popupOptions = {
        minWidth: 220,
        keepInView: true,
        autoPanPadding: [12, 12],
    };

    // Isi popup di-clone dari <template> di Blade, jadi markup form tidak ditulis di JS.
    function popupFrom(templateId) {
        return document.getElementById(templateId).content.firstElementChild.cloneNode(true);
    }

    function setCoords(node, latlng) {
        node.querySelector('.field-lat').value = latlng.lat.toFixed(7);
        node.querySelector('.field-lng').value = latlng.lng.toFixed(7);
        node.querySelector('.coords').textContent = latlng.lat.toFixed(5) + ', ' + latlng.lng.toFixed(5);
    }

    // --- Marker lokasi: geser untuk memindahkan, klik untuk ubah/hapus ---
    const markersById = {};

    allLocations.forEach(function(loc) {
        const color = colors[loc.disaster.slug] || '{{ \App\Models\Disaster::DEFAULT_COLOR }}';
        const icon = L.divIcon({
            html: `<div style="background-color:${color};width:14px;height:14px;border-radius:50%;border:3px solid white;box-shadow:0 0 6px rgba(0,0,0,0.4);"></div>`,
            iconSize: [14, 14],
            className: ''
        });

        const marker = L.marker([loc.latitude, loc.longitude], { icon: icon, draggable: true }).addTo(map);
        const original = marker.getLatLng();

        const content = popupFrom('tpl-edit');
        const hint = content.querySelector('.moved-hint');
        content.querySelector('.form-update').action = updateUrl.replace('__ID__', loc.id);
        content.querySelector('.form-delete').action = destroyUrl.replace('__ID__', loc.id);
        content.querySelector('.field-name').value = loc.location_name;
        content.querySelector('.field-disaster').value = loc.disaster_id;
        setCoords(content, original);
        marker.bindPopup(content, popupOptions);

        marker.on('dragend', function() {
            setCoords(content, marker.getLatLng());
            hint.classList.remove('hidden');
            marker.openPopup();
        });

        // Tutup popup tanpa simpan = batalkan pemindahan.
        marker.on('popupclose', function() {
            if (hint.classList.contains('hidden')) return;
            marker.setLatLng(original);
            setCoords(content, original);
            hint.classList.add('hidden');
        });

        markersById[loc.id] = marker;
    });

    // --- Pin titik baru ---
    const addContent = popupFrom('tpl-add');

    const picker = L.marker(map.getCenter(), { draggable: true })
        .addTo(map)
        .bindTooltip('Titik baru — geser atau klik untuk mengisi data')
        .bindPopup(addContent, popupOptions);

    addContent.querySelector('.btn-cancel').addEventListener('click', () => picker.closePopup());
    setCoords(addContent, picker.getLatLng());

    function movePicker(lat, lng, zoom) {
        picker.setLatLng([lat, lng]);
        map.setView([lat, lng], zoom ?? map.getZoom());
        setCoords(addContent, picker.getLatLng());
        picker.openPopup();
    }

    picker.on('dragend', () => setCoords(addContent, picker.getLatLng()));
    map.on('click', (e) => movePicker(e.latlng.lat, e.latlng.lng));

    // Tombol "+" hanya membuka form pin baru — tidak memindahkan pin yang sudah digeser.
    document.getElementById('add-btn').addEventListener('click', function() {
        map.panTo(picker.getLatLng());
        picker.openPopup();
    });

    // --- Geocoding (Nominatim) ---
    const geocodeInput = document.getElementById('geocode-input');
    const geocodeResults = document.getElementById('geocode-results');
    const geocodeStatus = document.getElementById('geocode-status');

    function showStatus(message) {
        geocodeStatus.textContent = message;
        geocodeStatus.classList.toggle('hidden', !message);
    }

    async function geocode() {
        const q = geocodeInput.value.trim();
        if (!q) return;

        showStatus('');
        geocodeResults.classList.add('hidden');

        try {
            const res = await fetch('https://nominatim.openstreetmap.org/search?format=json&limit=5&q=' + encodeURIComponent(q));
            const data = await res.json();

            if (!data.length) {
                showStatus('Alamat tidak ditemukan.');
                return;
            }

            geocodeResults.innerHTML = '';
            data.forEach(function(place) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'block w-full px-3 py-2 text-left text-xs text-gray-700 hover:bg-gray-50';
                btn.textContent = place.display_name;
                btn.addEventListener('click', function() {
                    movePicker(parseFloat(place.lat), parseFloat(place.lon), 15);
                    geocodeResults.classList.add('hidden');
                });
                geocodeResults.appendChild(btn);
            });
            geocodeResults.classList.remove('hidden');
        } catch (e) {
            showStatus('Gagal menghubungi layanan pencarian alamat.');
        }
    }

    document.getElementById('geocode-btn').addEventListener('click', geocode);
    geocodeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            geocode();
        }
    });

    document.getElementById('gps-btn').addEventListener('click', function() {
        if (!navigator.geolocation) {
            showStatus('Browser tidak mendukung geolokasi.');
            return;
        }
        showStatus('');
        navigator.geolocation.getCurrentPosition(
            (pos) => movePicker(pos.coords.latitude, pos.coords.longitude, 15),
            () => showStatus('Gagal mengambil lokasi. Pastikan izin lokasi diaktifkan.')
        );
    });

    // --- Tabel ---
    function focusMarker(id) {
        const marker = markersById[id];
        if (!marker) return;

        map.setView(marker.getLatLng(), 14);
        marker.openPopup();
        document.getElementById('map-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function deleteLocation(url, name) {
        if (!confirm('Hapus lokasi "' + name + '"?')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
            '<input type="hidden" name="_method" value="DELETE">';
        document.body.appendChild(form);
        form.submit();
    }

    // --- Bulk select ---
    const checkAll = document.getElementById('check-all');
    const rowChecks = document.querySelectorAll('.row-check');
    const bulkBar = document.getElementById('bulk-bar');
    const bulkCount = document.getElementById('bulk-count');

    function refreshBulkBar() {
        const selected = document.querySelectorAll('.row-check:checked').length;
        bulkCount.textContent = selected;
        bulkBar.classList.toggle('hidden', selected === 0);
        bulkBar.classList.toggle('flex', selected > 0);
    }

    checkAll.addEventListener('change', function() {
        rowChecks.forEach(c => { c.checked = checkAll.checked; });
        refreshBulkBar();
    });
    rowChecks.forEach(c => c.addEventListener('change', refreshBulkBar));
</script>
@endsection
