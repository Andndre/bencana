<!DOCTYPE html>
<html>
<head>
  <title>AR Kamera - Simulasi Bencana</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />

  <script src="https://aframe.io/releases/1.0.4/aframe.min.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/AR-js-org/AR.js@3.4.7/aframe/build/aframe-ar.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/aframe-extras@7.2.0/dist/aframe-extras.min.js"></script>

  <script src="/js/gesture-detector.js"></script>
  <script src="/js/gesture-handler.js"></script>
  <script src="/js/ar-loader.js"></script>

  <style>
    body { margin: 0; overflow: hidden; background: #000; }
  </style>
</head>
<body>

<div id="loading-overlay" style="
  position:fixed;inset:0;background:#000;display:flex;
  align-items:center;justify-content:center;z-index:9999;flex-direction:column;
">
  <div style="color:#fff;font-family:Arial;font-size:18px;margin-bottom:16px">Memuat AR...</div>
  <div style="width:200px;height:4px;background:#333;border-radius:2px;overflow:hidden">
    <div id="progress-bar" style="height:100%;background:#c25c06;width:0;transition:width .3s"></div>
  </div>
  <p style="color:#888;font-size:13px;margin-top:12px;text-align:center;max-width:280px;line-height:1.5">
    Arahkan kamera ke marker AR.<br/>Posisikan marker pada permukaan datar.
  </p>
</div>

<div id="ar-info" style="
  position:absolute;bottom:20px;left:20px;right:20px;
  background:rgba(194,92,6,.9);color:#fff;padding:15px;
  border-radius:10px;font-family:Arial,sans-serif;
  display:none;z-index:1000;
">
  <h4 id="marker-title" style="margin:0 0 6px;color:#ffac00"></h4>
  <p id="marker-description" style="margin:0;font-size:13px;line-height:1.5"></p>
</div>

<a href="/simulasi-bencana" style="
  position:absolute;top:15px;right:15px;
  background:rgba(0,0,0,.7);color:#fff;padding:8px 16px;
  border-radius:20px;text-decoration:none;font-family:Arial;
  font-size:14px;z-index:1000;
">Kembali</a>

<a-scene
  embedded
  arjs="detectionMode: mono_and_matrix; matrixCodeType: 3x3;"
  renderer="logarithmicDepthBuffer: true; antialias: true;"
  vr-mode-ui="enabled: false"
  gesture-detector
  id="scene"
  loading-screen="enabled: false"
>
  @foreach ($arMarkers as $marker)
    <a-marker
      type="pattern"
      url="/storage/{{ $marker->path_patt }}"
      raycaster="objects: .clickable"
      emitevents="true"
      cursor="fuse: false; rayOrigin: mouse;"
      id="marker{{ $marker->marker_id }}"
      data-marker-name="{{ $marker->nama }}"
      data-disaster-name="{{ $marker->disaster?->name ?? '' }}"
      data-disaster-description="{{ $marker->disaster?->description ?? '' }}"
      data-model-src="{{ $marker->path_model ? '/storage/' . $marker->path_model : '' }}"
      data-model-scale="1 1 1"
      data-model-position="0 0.25 0"
    >
      <a-entity
        id="marker{{ $marker->marker_id }}-entity"
        position="0 0 0"
        scale="1 1 1"
        class="clickable"
        gesture-handler
      >
        @unless ($marker->path_model)
          <a-box
            color="#c25c06"
            width="0.5" height="0.5" depth="0.5"
            position="0 0.25 0"
            animation="property: rotation; to: 0 360 0; dur: 3000; loop: true; easing: linear"
          ></a-box>
        @endunless
      </a-entity>
    </a-marker>
  @endforeach

  <a-entity camera></a-entity>
</a-scene>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var scene = document.querySelector('a-scene');
  var overlay = document.getElementById('loading-overlay');

  scene.addEventListener('arRendered', function () {
    overlay.style.display = 'none';
  });

  setTimeout(function () { overlay.style.display = 'none'; }, 5000);

  document.querySelectorAll('a-marker').forEach(function (marker) {
    marker.addEventListener('markerFound', function () {
      document.getElementById('marker-title').textContent = marker.getAttribute('data-marker-name');
      document.getElementById('marker-description').textContent = marker.getAttribute('data-disaster-description');
      document.getElementById('ar-info').style.display = 'block';
    });

    marker.addEventListener('markerLost', function () {
      document.getElementById('ar-info').style.display = 'none';
    });
  });
});
</script>

</body>
</html>
