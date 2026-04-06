/**
 * AR Dynamic GLB Loader — Bencana Alam
 *
 * Logic:
 * 1. Saat marker terdeteksi (markerFound) → download GLB, tampilkan
 * 2. Saat marker hilang (markerLost)  → cache tetap, hide model dari scene
 * 3. Model di-cache (Map) → tidak download ulang jika marker muncul lagi
 * 4. Visibility versioning → cancel load jika marker hilang sebelum load selesai
 */

// ── State ────────────────────────────────────────────────────────────────────
var markerVisibilityVersion = new Map(); // markerId → integer counter
var modelStates = new Map();              // modelSrc → { loaded, object3D, animations }
var globalClock = null;

// ── Inisialisasi DRACO + Meshopt Loader ──────────────────────────────────────
// Three.js r148 (A-Frame 1.5.0): setMeshoptDecoder adalah instance method,
// jadi kita harus patch load() agar auto-set di setiap instance.
function initLoaders() {
  if (typeof THREE === 'undefined' || !THREE.GLTFLoader) {
    console.warn('[ar-loader] THREE tidak tersedia, skip loader init');
    return;
  }

  // Buat decoder instances sekali
  var sharedDracoLoader = null;
  if (THREE.DRACOLoader) {
    sharedDracoLoader = new THREE.DRACOLoader();
    sharedDracoLoader.setDecoderPath('/ar-marker/');
    sharedDracoLoader.setDecoderConfig({ type: 'wasm' });
    sharedDracoLoader.preload();
  }

  // Patch GLTFLoader.load() agar auto-inject meshopt + DRACO di setiap instance
  var OriginalGLTFLoader = THREE.GLTFLoader;
  THREE.GLTFLoader = function (manager) {
    var instance = new OriginalGLTFLoader(manager);
    // Auto-set meshopt
    if (typeof MeshoptDecoder !== 'undefined') {
      instance.setMeshoptDecoder(MeshoptDecoder);
    }
    // Auto-set DRACO
    if (sharedDracoLoader) {
      instance.setDRACOLoader(sharedDracoLoader);
    }
    return instance;
  };
  THREE.GLTFLoader.prototype = OriginalGLTFLoader.prototype;
  THREE.GLTFLoader.constructor = OriginalGLTFLoader;
  // Copy static methods
  THREE.GLTFLoader.prototype.constructor = THREE.GLTFLoader;

  console.log('[ar-loader] Meshopt + DRACO auto-injected via loader patch');
}

// ── Visibility Versioning ────────────────────────────────────────────────────
function bumpMarkerVisibilityVersion(markerId) {
  var next = (markerVisibilityVersion.get(markerId) || 0) + 1;
  markerVisibilityVersion.set(markerId, next);
  return next;
}

function isMarkerLoadStillRelevant(markerId, visibilityVersion) {
  return markerVisibilityVersion.has(markerId) &&
    (markerVisibilityVersion.get(markerId) || 0) === visibilityVersion;
}

// ── Parse metadata dari data-marker-* attributes ─────────────────────────────
function parseMarkerModelData(marker) {
  return {
    modelSrc: marker.getAttribute('data-model-src') || null,
    modelScale: marker.getAttribute('data-model-scale') || '1 1 1',
    modelPosition: marker.getAttribute('data-model-position') || '0 0.25 0',
  };
}

// ── Loading Overlay UI ────────────────────────────────────────────────────────
function showLoading() {
  var overlay = document.getElementById('loading-overlay');
  if (overlay) {
    overlay.style.display = 'flex';
    var bar = document.getElementById('progress-bar');
    if (bar) bar.style.width = '10%';
  }
}

function updateProgress(percent) {
  var bar = document.getElementById('progress-bar');
  if (bar) bar.style.width = percent + '%';
}

function hideLoading() {
  var overlay = document.getElementById('loading-overlay');
  if (overlay) {
    var bar = document.getElementById('progress-bar');
    if (bar) bar.style.width = '100%';
    setTimeout(function () { overlay.style.display = 'none'; }, 400);
  }
}

// ── Load GLB via A-Frame / Three.js ──────────────────────────────────────────────
// Karena initLoaders() sudah set MeshoptDecoder dan DRACO di prototype,
// GLTFLoader.load() otomatis handle kedua extension.
function loadGLB(modelSrc, markerId, visibilityVersion) {
  return new Promise(function (resolve, reject) {
    if (!globalClock) {
      globalClock = new THREE.Clock();
      globalClock.start(); // Start clock agar getDelta() return elapsed time
    }

    var loader = new THREE.GLTFLoader();
    var cancelled = false;

    loader.load(
      modelSrc,
      function (gltf) {
        if (cancelled || !isMarkerLoadStillRelevant(markerId, visibilityVersion)) return;
        cancelled = true;

        var scene = gltf.scene;
        if (!scene) { reject(new Error('Scene kosong')); return; }

        var marker = document.getElementById(markerId);
        if (marker) {
          var entity = marker.querySelector('a-entity');
          if (entity) {
            var modelData = parseMarkerModelData(marker);
            entity.setAttribute('position', modelData.modelPosition);
            entity.setAttribute('scale', modelData.modelScale);
            entity.setObject3D('dynamicModel', scene);
          }
        }

        // Jalankan semua animasi — clipAction.play() auto-starts dari time 0
        if (gltf.animations && gltf.animations.length > 0 && scene) {
          var mixer = new THREE.AnimationMixer(scene);
          gltf.animations.forEach(function (clip) {
            mixer.clipAction(clip).play();
          });
          mixer._markerId = markerId; // untuk debugging

          var sceneEl = document.querySelector('a-scene');
          if (sceneEl) {
            if (!sceneEl._arDynamicMixers) {
              sceneEl._arDynamicMixers = [];
              sceneEl.addEventListener('tick', function (time, delta) {
                // Pakai delta dari A-Frame tick (ms), convert ke detik
                var dt = (delta !== undefined ? delta : globalClock.getDelta()) / 1000;
                if (dt > 0.1) dt = 0.1; // clamp max delta agar tidak skip frame
                sceneEl._arDynamicMixers.forEach(function (m) { m.update(dt); });
              });
            }
            sceneEl._arDynamicMixers.push(mixer);
          }
          console.log('[ar-loader] ' + gltf.animations.length + ' animasi dimulai untuk', markerId);
        }

        updateProgress(100);
        resolve(gltf);
      },
      function (e) {
        if (cancelled || !isMarkerLoadStillRelevant(markerId, visibilityVersion)) {
          cancelled = true; return;
        }
        if (e.lengthComputable) updateProgress(Math.round((e.loaded / e.total) * 90));
      },
      function (err) {
        if (!cancelled && isMarkerLoadStillRelevant(markerId, visibilityVersion)) {
          reject(err);
        }
      }
    );
  });
}

// ── Attach / Detach model ────────────────────────────────────────────────────
function attachModel(markerId, modelSrc) {
  var marker = document.getElementById(markerId);
  if (!marker) return;
  var entity = marker.querySelector('a-entity');
  if (!entity) return;

  var modelData = parseMarkerModelData(marker);
  entity.setAttribute('position', modelData.modelPosition);
  entity.setAttribute('scale', modelData.modelScale);
  entity.removeObject3D('dynamicModel');

  var cached = modelStates.get(modelSrc);
  if (cached && cached.object3D) {
    entity.setObject3D('dynamicModel', cached.object3D);
  }
}

function detachModel(markerId) {
  var marker = document.getElementById(markerId);
  if (!marker) return;
  var entity = marker.querySelector('a-entity');
  if (entity) entity.removeObject3D('dynamicModel');
}

// ── Pasang event listener ke marker ──────────────────────────────────────────
function initMarkerListeners() {
  var markers = document.querySelectorAll('a-marker');
  if (!markers.length) {
    console.warn('[ar-loader] Tidak ada a-marker ditemukan');
    return;
  }

  markers.forEach(function (marker) {
    var markerId = marker.id;
    var modelSrc = parseMarkerModelData(marker).modelSrc;

    marker.addEventListener('markerFound', function () {
      if (!modelSrc) return;

      var version = bumpMarkerVisibilityVersion(markerId);
      showLoading();
      updateProgress(5);

      attachModel(markerId, modelSrc);

      var cached = modelStates.get(modelSrc);
      if (cached && cached.loaded) {
        updateProgress(100);
        hideLoading();
        return;
      }

      loadGLB(modelSrc, markerId, version)
        .then(function (gltf) {
          modelStates.set(modelSrc, {
            loaded: true,
            object3D: gltf.scene,
            animations: gltf.animations,
          });
          updateProgress(100);
          hideLoading();
        })
        .catch(function (err) {
          console.error('[ar-loader] Gagal load model untuk', markerId, err);
          updateProgress(100);
          hideLoading();
        });
    });

    marker.addEventListener('markerLost', function () {
      bumpMarkerVisibilityVersion(markerId);
      detachModel(markerId);
    });
  });

  console.log('[ar-loader] ' + markers.length + ' marker(s) di-register');
}

// ── Boot ────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  var scene = document.querySelector('a-scene');

  if (scene.hasLoaded) {
    onSceneReady();
  } else {
    scene.addEventListener('loaded', onSceneReady);
  }

  setTimeout(function () { hideLoading(); }, 8000);
});

function onSceneReady() {
  if (typeof THREE !== 'undefined') initLoaders();
  initMarkerListeners();
}
