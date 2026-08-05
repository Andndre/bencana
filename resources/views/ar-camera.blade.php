<!DOCTYPE html>
<html>

<head>
    <title>AR Kamera - Simulasi Bencana</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />

    <script src="https://aframe.io/releases/1.4.2/aframe.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/AR-js-org/AR.js@3.4.8/aframe/build/aframe-ar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aframe-extras@7.2.0/dist/aframe-extras.min.js"></script>

    <script src="/js/gesture-detector.js"></script>
    <script src="/js/gesture-handler.js"></script>
    <script src="/ar-marker/meshopt_decoder.js"></script>
    <script src="/js/ar-loader.js"></script>

    <style>
        body {
            margin: 0;
            overflow: hidden;
            background: #000;
        }

        /* Force hide A-Frame VR/Enter VR button */
        .a-enter-vr-button,
        a-scene [icon-hide] {
            display: none !important;
        }

        .a-loader-title {
            display: none !important;
        }
    </style>
</head>

<body>

    <div id="instructions"
        style="
  position:fixed;inset:0;background:#0b0b0b;z-index:10001;display:none;
  flex-direction:column;align-items:center;justify-content:center;padding:24px;
  font-family:Arial,sans-serif;color:#fff;text-align:left;
">
        <div style="max-width:340px;width:100%;background:#1a1a1a;border-radius:14px;padding:20px">
            <h3 style="margin:0 0 12px;color:#ffac00;font-size:17px">Cara memakai kamera AR</h3>
            <ul style="margin:0;padding-left:18px;font-size:13px;line-height:1.7;color:#ddd">
                <li>Pastikan ruangan cukup terang, hindari pantulan cahaya di marker.</li>
                <li>Jarak kamera ke marker sekitar 20–40 cm.</li>
                <li>Letakkan marker di permukaan datar, jangan tertekuk.</li>
                <li>Tahan HP stabil sampai objek 3D muncul.</li>
            </ul>
            <button id="instructions-start"
                style="margin-top:16px;width:100%;background:#c25c06;color:#fff;border:none;
                       border-radius:10px;padding:12px;font-size:14px;font-weight:700;cursor:pointer">
                Mulai
            </button>
            <label style="display:flex;align-items:center;gap:8px;margin-top:12px;font-size:12px;color:#999">
                <input type="checkbox" id="instructions-hide"> Jangan tampilkan lagi
            </label>
        </div>
    </div>

    <div id="camera-error"
        style="
  position:fixed;inset:0;background:#0b0b0b;z-index:10002;display:none;
  flex-direction:column;align-items:center;justify-content:center;padding:24px;
  font-family:Arial,sans-serif;color:#fff;
">
        <div style="max-width:340px;width:100%;background:#1a1a1a;border-radius:14px;padding:20px">
            <h3 style="margin:0 0 10px;color:#ff6b6b;font-size:17px">Kamera tidak dapat diakses</h3>
            <p id="camera-error-reason" style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#ddd"></p>
            <p style="margin:0 0 16px;font-size:12px;line-height:1.6;color:#999">
                Chrome/Edge: ketuk ikon gembok di address bar → Izin situs → Kamera → Izinkan.<br>
                Safari iOS: Pengaturan → Safari → Kamera → Izinkan.<br>
                AR juga membutuhkan koneksi HTTPS.
            </p>
            <div style="display:flex;gap:8px">
                <button id="camera-retry"
                    style="flex:1;background:#c25c06;color:#fff;border:none;border-radius:10px;
                           padding:11px;font-size:13px;font-weight:700;cursor:pointer">Coba Lagi</button>
                <a href="/simulasi-bencana"
                    style="flex:1;text-align:center;background:#333;color:#fff;border-radius:10px;
                           padding:11px;font-size:13px;font-weight:700;text-decoration:none">Kembali</a>
            </div>
        </div>
    </div>

    <div id="scan-hint"
        style="
  position:absolute;top:70px;left:50%;transform:translateX(-50%);
  background:rgba(0,0,0,.65);color:#fff;padding:8px 14px;border-radius:20px;
  font-family:Arial,sans-serif;font-size:12px;z-index:1000;display:none;white-space:nowrap;
">Arahkan kamera ke marker AR</div>

    <div id="loading-overlay"
        style="
  position:fixed;inset:0;background:#000;display:flex;
  align-items:center;justify-content:center;z-index:9999;flex-direction:column;
">
        <div style="color:#fff;font-family:Arial;font-size:18px;margin-bottom:16px">Memuat AR...</div>
        <div style="width:200px;height:4px;background:#333;border-radius:2px;overflow:hidden">
            <div id="progress-bar" style="height:100%;background:#c25c06;width:0;transition:width .3s"></div>
        </div>
        <p style="color:#888;font-size:13px;margin-top:12px;text-align:center;max-width:280px;line-height:1.5">
            Arahkan kamera ke marker AR.<br />Posisikan marker pada permukaan datar.
        </p>
    </div>

    <div id="ar-info"
        style="
  position:absolute;bottom:20px;left:20px;right:20px;
  background:rgba(194,92,6,.9);color:#fff;padding:15px;
  border-radius:10px;font-family:Arial,sans-serif;
  display:none;z-index:1000;
">
        <h4 id="marker-title" style="margin:0 0 6px;color:#ffac00"></h4>
        <p id="marker-description" style="margin:0;font-size:13px;line-height:1.5"></p>
    </div>

    <a href="/simulasi-bencana"
        style="
  position:absolute;top:15px;right:15px;
  background:rgba(0,0,0,.7);color:#fff;padding:8px 16px;
  border-radius:20px;text-decoration:none;font-family:Arial;
  font-size:14px;z-index:1000;
">Kembali</a>

    <button id="audio-toggle"
        style="
  position:absolute;top:15px;left:15px;
  background:rgba(0,140,0,.9);border:none;border-radius:50%;
  width:52px;height:52px;cursor:pointer;z-index:1000;
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 2px 8px rgba(0,0,0,.4);
" title="Toggle Audio">
        <svg id="icon-audio-off" xmlns="http://www.w3.org/2000/svg" width="22" height="22"
            viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round"
            style="display:none">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
            <line x1="23" y1="9" x2="17" y2="15"></line>
            <line x1="17" y1="9" x2="23" y2="15"></line>
        </svg>
        <svg id="icon-audio-on" xmlns="http://www.w3.org/2000/svg" width="22" height="22"
            viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
            <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
            <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
        </svg>
    </button>

    <a-scene embedded arjs="sourceType: webcam; detectionMode: mono_and_matrix; matrixCodeType: 3x3;"
        renderer="logarithmicDepthBuffer: true; antialias: true;" vr-mode-ui="enabled: false" gesture-detector
        id="scene" loading-screen="enabled: false">
        @foreach ($arMarkers as $marker)
            <a-marker type="pattern" url="/storage/{{ $marker->path_patt }}" raycaster="objects: .clickable"
                emitevents="true" cursor="fuse: false; rayOrigin: mouse;" id="marker{{ $marker->marker_id }}"
                data-marker-name="{{ $marker->nama }}" data-marker-code="{{ $marker->marker_code ?? '' }}"
                data-disaster-name="{{ $marker->disaster?->name ?? '' }}"
                data-disaster-description="{{ $marker->disaster?->description ?? '' }}"
                data-model-src="{{ $marker->path_model ? '/storage/' . $marker->path_model : '' }}"
                data-audio-src="{{ $marker->path_audio ? '/storage/' . $marker->path_audio : '' }}"
                data-model-scale="1 1 1" data-model-position="0 0.25 0">
                <a-entity id="marker{{ $marker->marker_id }}-entity" position="0 0 0" scale="1 1 1" class="clickable"
                    gesture-handler>
                    @unless ($marker->path_model)
                        <a-box color="#c25c06" width="0.5" height="0.5" depth="0.5" position="0 0.25 0"
                            animation="property: rotation; to: 0 360 0; dur: 3000; loop: true; easing: linear"></a-box>
                    @endunless
                </a-entity>
            </a-marker>
        @endforeach

        <a-entity camera></a-entity>
    </a-scene>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var scene = document.querySelector('a-scene');
            var overlay = document.getElementById('loading-overlay');
            var hint = document.getElementById('scan-hint');
            var errorPanel = document.getElementById('camera-error');
            var instructions = document.getElementById('instructions');

            // Log semua error JS yang terjadi
            window.addEventListener('error', function(e) {
                console.error('[ar-camera] JS Error:', e.message, e.filename + ':' + e.lineno);
            });

            // --- Instruksi penggunaan ---
            if (localStorage.getItem('ar-instructions-hidden') !== '1') {
                instructions.style.display = 'flex';
            }

            document.getElementById('instructions-start').addEventListener('click', function() {
                if (document.getElementById('instructions-hide').checked) {
                    localStorage.setItem('ar-instructions-hidden', '1');
                }
                instructions.style.display = 'none';
            });

            // --- Fallback izin kamera ---
            function showCameraError(reason) {
                document.getElementById('camera-error-reason').textContent = reason;
                overlay.style.display = 'none';
                instructions.style.display = 'none';
                hint.style.display = 'none';
                errorPanel.style.display = 'flex';
            }

            document.getElementById('camera-retry').addEventListener('click', function() {
                location.reload();
            });

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showCameraError('Browser ini tidak mendukung akses kamera (getUserMedia). Coba buka lewat Chrome atau Safari terbaru.');
            } else {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(function(stream) {
                        // Lepas stream uji; AR.js membuka kameranya sendiri.
                        stream.getTracks().forEach(function(track) { track.stop(); });
                    })
                    .catch(function(err) {
                        if (err.name === 'NotAllowedError') {
                            showCameraError('Izin kamera ditolak. Berikan izin kamera untuk situs ini, lalu coba lagi.');
                        } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                            showCameraError('Tidak ada kamera yang terdeteksi pada perangkat ini.');
                        } else {
                            showCameraError('Kamera gagal diakses (' + err.name + ').');
                        }
                    });
            }

            // Cek apakah AR.js berhasil load video stream
            scene.addEventListener('arSystemError', function(e) {
                console.error('[ar-camera] AR System Error:', e.detail);
                showCameraError('AR gagal menginisialisasi kamera. Pastikan izin kamera diaktifkan.');
            });

            scene.addEventListener('cameraInit', function(e) {
                console.log('[ar-camera] Kamera berhasil diinisialisasi', e.detail);
            });

            // Hide overlay once AR.js renderer starts drawing
            scene.addEventListener('arRendered', function() {
                overlay.style.display = 'none';
                if (errorPanel.style.display !== 'flex') hint.style.display = 'block';
            });

            // Fallback: always hide overlay after 10s even if arRendered doesn't fire
            setTimeout(function() {
                overlay.style.display = 'none';
                // Cek apakah video element punya stream aktif
                var video = document.querySelector('video');
                if (video && !video.srcObject) {
                    console.warn('[ar-camera] Video stream tidak aktif setelah 10 detik');
                    showCameraError('Kamera tidak aktif setelah 10 detik. Cek izin kamera di browser.');
                } else if (video) {
                    console.log('[ar-camera] Video stream aktif — OK');
                }
            }, 10000);

            document.querySelectorAll('a-marker').forEach(function(marker) {
                marker.addEventListener('markerFound', function() {
                    console.log('[ar-camera] markerFound:', marker.id);
                    hint.style.display = 'none';
                    document.getElementById('marker-title').textContent = marker.getAttribute(
                        'data-marker-name');
                    document.getElementById('marker-description').textContent = marker.getAttribute(
                        'data-disaster-description');
                    document.getElementById('ar-info').style.display = 'block';
                });

                marker.addEventListener('markerLost', function() {
                    document.getElementById('ar-info').style.display = 'none';
                    if (errorPanel.style.display !== 'flex') hint.style.display = 'block';
                });
            });
        });
    </script>

</body>

</html>
