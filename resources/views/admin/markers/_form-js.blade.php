{{-- Mode auto/custom, saran Marker ID, preview logo + preview marker. Dipakai create & edit. --}}
<script>
    var markerPreviewUrl = null;
    var previewToken = 0;
    var currentLogoFile = null;

    function previewLogoFile(input) {
        var file = input.files[0];
        if (!file) return;

        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logo-preview-image').src = e.target.result;
            document.getElementById('logo-preview-container').classList.remove('hidden');
            document.getElementById('logo-placeholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);
        showFileInfo('logo-file-info', file, 2);

        currentLogoFile = file;
        requestMarkerPreview(file);
    }

    // Preview dibuat di server oleh generator yang sama dengan yang dipakai saat menyimpan.
    function requestMarkerPreview(file) {
        var wrap = document.getElementById('marker-preview-wrap');
        var img = document.getElementById('marker-preview-image');
        var status = document.getElementById('marker-preview-status');
        if (!wrap || !img) return;

        var token = ++previewToken;
        wrap.classList.remove('hidden');
        status.textContent = 'Membuat preview...';

        var codeField = document.getElementById('marker_code');
        var data = new FormData();
        data.append('path_logo_tengah', file);
        data.append('marker_code', codeField ? codeField.value : '');
        data.append('_token', document.querySelector('input[name="_token"]').value);

        fetch(document.getElementById('marker-form').dataset.previewUrl, { method: 'POST', body: data })
            .then(function(res) {
                if (!res.ok) throw new Error('gagal');
                return res.blob();
            })
            .then(function(blob) {
                if (token !== previewToken) return; // hasil usang, abaikan
                if (markerPreviewUrl) URL.revokeObjectURL(markerPreviewUrl);
                markerPreviewUrl = URL.createObjectURL(blob);
                img.src = markerPreviewUrl;
                status.textContent = 'Beginilah marker akan dicetak.';
            })
            .catch(function() {
                if (token !== previewToken) return;
                status.textContent = 'Preview gagal dibuat. Pastikan file PNG valid.';
            });
    }

    // --- Toggle mode auto / custom ---
    (function() {
        var auto = document.getElementById('section-auto');
        var custom = document.getElementById('section-custom');
        var radios = document.querySelectorAll('input[name="mode"]');
        if (!auto || !custom || !radios.length) return;

        function apply() {
            var mode = document.querySelector('input[name="mode"]:checked').value;
            auto.classList.toggle('hidden', mode !== 'auto');
            custom.classList.toggle('hidden', mode !== 'custom');
            // Input tersembunyi di-disable supaya tidak ikut terkirim
            auto.querySelectorAll('input[type="file"]').forEach(function(el) { el.disabled = mode !== 'auto'; });
            custom.querySelectorAll('input[type="file"]').forEach(function(el) { el.disabled = mode !== 'custom'; });

            document.querySelectorAll('.mode-option').forEach(function(label) {
                var on = label.querySelector('input[name="mode"]').checked;
                label.classList.toggle('border-[#c25c06]', on);
                label.classList.toggle('bg-[#c25c06]/5', on);
                label.classList.toggle('border-gray-200', !on);
            });
        }

        radios.forEach(function(r) { r.addEventListener('change', apply); });
        apply();
    })();

    // --- Saran Marker ID dari bencana terpilih ---
    (function() {
        var select = document.getElementById('disaster_id');
        var code = document.getElementById('marker_code');
        if (!select || !code) return;

        var touched = code.value !== '';
        var debounce = null;

        // Marker ID jadi seed pola, jadi preview harus dibuat ulang saat ID berubah.
        code.addEventListener('input', function() {
            touched = true;
            if (!currentLogoFile) return;
            clearTimeout(debounce);
            debounce = setTimeout(function() { requestMarkerPreview(currentLogoFile); }, 400);
        });

        select.addEventListener('change', function() {
            if (touched) return;
            var slug = select.options[select.selectedIndex].dataset.slug;
            code.value = slug ? 'MRK-' + slug.toUpperCase() + '-01' : '';
            if (currentLogoFile) requestMarkerPreview(currentLogoFile);
        });
    })();

    // --- Drag & Drop logo ---
    (function() {
        var zone = document.getElementById('logo-drop-zone');
        var input = document.getElementById('path_logo_tengah');
        if (!zone || !input) return;

        ['dragenter', 'dragover'].forEach(function(name) {
            zone.addEventListener(name, function(e) {
                e.preventDefault();
                zone.classList.add('border-[#c25c06]', 'bg-[#c25c06]/5');
            }, false);
        });

        ['dragleave', 'drop'].forEach(function(name) {
            zone.addEventListener(name, function(e) {
                e.preventDefault();
                zone.classList.remove('border-[#c25c06]', 'bg-[#c25c06]/5');
            }, false);
        });

        zone.addEventListener('drop', function(e) {
            var files = e.dataTransfer.files;
            if (files.length > 0) {
                Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'files').set.call(input, files);
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    })();
</script>
