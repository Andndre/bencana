{{-- Preview 3D + progress bar upload. Dipakai oleh markers/create dan markers/edit (ID elemen sama). --}}
<script type="module">
    import { ModelViewerElement } from 'https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js';

    // model-viewer 3.5.0 tidak punya lokasi default untuk decoder Meshopt, jadi GLB hasil
    // kompresi EXT_meshopt_compression gagal dimuat sampai lokasi ini diisi sendiri.
    // File decoder-nya sudah ada di repo, dipakai bareng /ar-kamera.
    ModelViewerElement.meshoptDecoderLocation = '/ar-marker/meshopt_decoder.js';
</script>
<script>
    var modelObjectUrl = null;

    function humanSize(bytes) {
        return bytes < 1024 * 1024
            ? (bytes / 1024).toFixed(0) + ' KB'
            : (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    // Tampilkan file terpilih + peringatan bila melebihi batas ukuran (MB).
    function showFileInfo(id, file, maxMb) {
        var el = document.getElementById(id);
        if (!el) return;

        var tooBig = file.size > maxMb * 1024 * 1024;
        el.textContent = file.name + ' — ' + humanSize(file.size) +
            (tooBig ? ' (melebihi batas ' + maxMb + 'MB, upload akan ditolak)' : '');
        el.className = 'mt-1 text-xs ' + (tooBig ? 'font-semibold text-red-600' : 'text-gray-500');
    }

    function showModelPreview(src) {
        var viewer = document.getElementById('model-viewer');
        var wrap = document.getElementById('model-viewer-wrap');
        if (!viewer || !wrap) return;

        if (modelObjectUrl) URL.revokeObjectURL(modelObjectUrl);
        modelObjectUrl = typeof src === 'string' ? null : URL.createObjectURL(src);

        viewer.src = modelObjectUrl || src;
        wrap.classList.remove('hidden');
    }

    // Upload dengan progress bar; XHR mengikuti redirect Laravel lewat responseURL.
    (function() {
        var form = document.getElementById('marker-form');
        var bar = document.getElementById('upload-bar');
        var wrap = document.getElementById('upload-progress');
        var label = document.getElementById('upload-percent');
        var submit = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            var xhr = new XMLHttpRequest();
            xhr.open('POST', form.action);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            wrap.classList.remove('hidden');
            submit.disabled = true;
            submit.classList.add('opacity-50', 'cursor-not-allowed');

            xhr.upload.addEventListener('progress', function(ev) {
                if (!ev.lengthComputable) return;
                var percent = Math.round((ev.loaded / ev.total) * 100);
                bar.style.width = percent + '%';
                label.textContent = percent + '%';
            });

            xhr.addEventListener('load', function() {
                if (xhr.status >= 200 && xhr.status < 400) {
                    window.location = xhr.responseURL || form.dataset.redirect;
                    return;
                }

                submit.disabled = false;
                submit.classList.remove('opacity-50', 'cursor-not-allowed');
                wrap.classList.add('hidden');

                var message = 'Upload gagal. Coba lagi.';
                if (xhr.status === 422) {
                    try {
                        var errors = JSON.parse(xhr.responseText).errors || {};
                        message = Object.values(errors).flat().join('\n') || message;
                    } catch (err) { /* pakai pesan default */ }
                } else if (xhr.status === 413) {
                    message = 'File terlalu besar untuk diterima server.';
                }
                alert(message);
            });

            xhr.addEventListener('error', function() {
                submit.disabled = false;
                submit.classList.remove('opacity-50', 'cursor-not-allowed');
                wrap.classList.add('hidden');
                alert('Koneksi terputus saat mengunggah.');
            });

            xhr.send(new FormData(form));
        });
    })();
</script>
