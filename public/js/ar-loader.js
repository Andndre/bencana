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
var modelStates = new Map();              // "markerId:modelKey" → { loaded, modelSrc }
var sharedDracoLoader = null;

// ── Inisialisasi DRACO Loader ────────────────────────────────────────────────
function initDracoLoader() {
  if (typeof THREE === 'undefined' || !THREE.DRACOLoader) {
    console.warn('[ar-loader] THREE.DRACOLoader tidak tersedia, skip DRACO');
    return;
  }

  sharedDracoLoader = new THREE.DRACOLoader();
  sharedDracoLoader.setDecoderPath('/ar-marker/');
  sharedDracoLoader.setDecoderConfig({ type: 'wasm' });
  sharedDracoLoader.preload();

  // Patch GLTFLoader bawaan A-Frame agar otomatis pakai DRACO
  var OriginalGLTFLoader = THREE.GLTFLoader;
  function PatchedGLTFLoader(manager) {
    var loader = new OriginalGLTFLoader(manager);
    if (typeof loader.setDRACOLoader === 'function') {
      loader.setDRACOLoader(sharedDracoLoader);
    }
    return loader;
  }
  PatchedGLTFLoader.prototype = OriginalGLTFLoader.prototype;
  THREE.GLTFLoader = PatchedGLTFLoader;

  console.log('[ar-loader] DRACO loader initialized');
}

// ── Visibility Versioning ────────────────────────────────────────────────────
// Setiap kali marker muncul/hilang, counter naik.
// Jika load dimulai di versi lama tapi marker sudah hilang → load dibatalkan.
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
  // Data ini di-set oleh blade template
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
    if (bar) bar.style.width = '30%';
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

// ── Load model via A-Frame, fallback ke Three.js ────────────────────────────
function loadModelForMarker(marker, modelData, visibilityVersion) {
  var entity = marker.querySelector('a-entity');
  if (!entity || !modelData.modelSrc) return Promise.resolve();

  var modelKey = marker.id + ':' + modelData.modelSrc;
  var cached = modelStates.get(modelKey);

  // ── Jika sudah di-cache ──────────────────────────────────────────────────
  if (cached && cached.loaded) {
    attachModelToEntity(entity, cached.modelSrc, modelData.modelScale, modelData.modelPosition);
    return Promise.resolve();
  }

  // ── Load baru ─────────────────────────────────────────────────────────────
  showLoading();
  updateProgress(20);

  return new Promise(function (resolve, reject) {
    var finished = false;

    function onLoaded() {
      if (finished || !isMarkerLoadStillRelevant(marker.id, visibilityVersion)) return;
      finished = true;
      modelStates.set(modelKey, { loaded: true, modelSrc: modelData.modelSrc });
      updateProgress(100);
      hideLoading();
      resolve();
    }

    function onError() {
      if (finished || !isMarkerLoadStillRelevant(marker.id, visibilityVersion)) return;
      finished = true;

      // Fallback: Three.js GLTFLoader manual
      loadWithThreeFallback(entity, modelData.modelSrc, modelData.modelScale, modelData.modelPosition)
        .then(function () {
          modelStates.set(modelKey, { loaded: true, modelSrc: modelData.modelSrc });
          updateProgress(100);
          hideLoading();
          resolve();
        })
        .catch(function (err) {
          console.error('[ar-loader] Three.js fallback gagal:', err);
          updateProgress(100);
          hideLoading();
          reject(err);
        });
    }

    entity.addEventListener('model-loaded', onLoaded, { once: true });
    entity.addEventListener('model-error', onError, { once: true });

    // Trigger load via A-Frame — set gltf-model + animation-mixer agar semua animasi diputar
    updateProgress(40);
    entity.setAttribute('gltf-model', modelData.modelSrc);
    entity.setAttribute('animation-mixer', 'clip: *; loop: repeat');
    entity.setAttribute('scale', modelData.modelScale);
    entity.setAttribute('position', modelData.modelPosition);
  });
}

// ── Fallback: Load dengan Three.js GLTFLoader ───────────────────────────────
function loadWithThreeFallback(entity, modelSrc, scale, position) {
  return new Promise(function (resolve, reject) {
    if (typeof THREE === 'undefined' || !THREE.GLTFLoader) {
      return reject(new Error('THREE.GLTFLoader tidak tersedia'));
    }

    var loader = new THREE.GLTFLoader();
    if (sharedDracoLoader) {
      loader.setDRACOLoader(sharedDracoLoader);
    }

    loader.load(
      modelSrc,
      function (gltf) {
        var scene = gltf && gltf.scene;
        if (!scene) return reject(new Error('Scene kosong'));

        entity.removeObject3D('mesh');
        entity.setObject3D('mesh', scene);
        entity.setAttribute('scale', scale);
        entity.setAttribute('position', position);

        // Jalankan semua animasi dari GLB
        if (gltf.animations && gltf.animations.length > 0) {
          var mixer = new THREE.AnimationMixer(scene);
          gltf.animations.forEach(function (clip) {
            mixer.clipAction(clip).play();
          });
          entity._animationMixer = mixer;

          // Update mixer setiap frame
          var sceneEl = document.querySelector('a-scene');
          var clock = sceneEl.components['arjs'] && sceneEl.components['arjs'].el
            ? new THREE.Clock()
            : new THREE.Clock();

          function animateMixers() {
            if (entity._animationMixer) {
              entity._animationMixer.update(clock.getDelta());
            }
            if (entity._animationMixer || entity._keepAnimating) {
              entity._animationMixerRAF = requestAnimationFrame(animateMixers);
            }
          }
          animateMixers();
        }

        resolve();
      },
      function (xhr) {
        if (xhr.total) {
          updateProgress(40 + Math.round((xhr.loaded / xhr.total) * 60));
        }
      },
      reject
    );
  });
}

// ── Attach cached model ke entity ───────────────────────────────────────────
function attachModelToEntity(entity, modelSrc, scale, position) {
  entity.removeObject3D('mesh');
  entity.setAttribute('gltf-model', modelSrc);
  entity.setAttribute('scale', scale);
  entity.setAttribute('position', position);
}

// ── Remove model dari entity (saat marker hilang) ───────────────────────────
function removeModelFromEntity(entity) {
  entity.removeObject3D('mesh');
  // Hentikan animation loop jika ada
  if (entity._animationMixerRAF) {
    cancelAnimationFrame(entity._animationMixerRAF);
    entity._animationMixerRAF = null;
  }
  entity._animationMixer = null;
  entity._keepAnimating = false;
}

// ── Inisialisasi: Pasang event listener ke semua marker ─────────────────────
function initMarkerListeners() {
  var markers = document.querySelectorAll('a-marker');
  if (!markers.length) {
    console.warn('[ar-loader] Tidak ada a-marker ditemukan');
    return;
  }

  markers.forEach(function (marker) {
    // ── Saat marker terdeteksi ────────────────────────────────────────────
    marker.addEventListener('markerFound', function () {
      var version = bumpMarkerVisibilityVersion(this.id);
      var modelData = parseMarkerModelData(this);

      if (!modelData.modelSrc) {
        // Tidak ada model GLB → tampilkan fallback cube (sudah di-blade)
        return;
      }

      loadModelForMarker(this, modelData, version).catch(function (err) {
        console.error('[ar-loader] Gagal load model untuk', this.id, err);
      }.bind(this));
    });

    // ── Saat marker hilang ────────────────────────────────────────────────
    marker.addEventListener('markerLost', function () {
      bumpMarkerVisibilityVersion(this.id);
      var entity = this.querySelector('a-entity');
      if (entity) {
        removeModelFromEntity(entity);
      }
    });
  });

  console.log('[ar-loader] ' + markers.length + ' marker(s) di-register');
}

// ── Boot ────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  // Tunggu A-Frame + THREE.js siap
  var scene = document.querySelector('a-scene');

  if (scene.hasLoaded) {
    onSceneReady();
  } else {
    scene.addEventListener('loaded', onSceneReady);
  }

  // Fallback: hide loading overlay setelah 8 detik
  setTimeout(function () { hideLoading(); }, 8000);
});

function onSceneReady() {
  // Init DRACO sebelum registrasi marker
  if (typeof THREE !== 'undefined') {
    initDracoLoader();
  }
  initMarkerListeners();
}
