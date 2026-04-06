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
var audioElements = new Map();            // audioSrc → HTMLAudioElement
var globalClock = null;
var _arAnimationMixers = [];             // central mixer list, updated by scene component

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
    audioSrc: marker.getAttribute('data-audio-src') || null,
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
          // Animasi clock mulai saat loadGLB() dipanggil. Jika load cepat (<0.1s),
          // initial getDelta() sudah aman. Jika lambat, clamp di animation loop
          // (dt > 0.1) akan menanganinya. Dua pemanggilan getDelta() berikut
          // mengsinkronkan clock agar mixer mulai dari elapsed ≈ 0.
          globalClock.getDelta();
          globalClock.getDelta();

          var mixer = new THREE.AnimationMixer(scene);
          gltf.animations.forEach(function (clip) {
            mixer.clipAction(clip).play();
          });
          mixer._markerId = markerId;
          _arAnimationMixers.push(mixer);

          modelStates.set(modelSrc, {
            loaded: true,
            object3D: gltf.scene,
            animations: gltf.animations,
            mixer: mixer,
          });
        }

        updateProgress(100);

        // Download audio after GLB loaded
        var audioSrc = parseMarkerModelData(document.getElementById(markerId)).audioSrc;
        if (audioSrc && !audioElements.has(audioSrc)) {
          var audio = new Audio();
          audio.src = audioSrc;
          audio.preload = 'auto';
          audioElements.set(audioSrc, audio);
        }

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
      var version = bumpMarkerVisibilityVersion(markerId);
      showLoading();
      updateProgress(5);

      attachModel(markerId, modelSrc);

      var cached = modelStates.get(modelSrc);
      if (cached && cached.loaded) {
        // Resume mixer animasi saat marker muncul lagi
        if (cached.mixer) {
          cached.mixer.timeScale = 1;
        }
        updateProgress(100);
        hideLoading();
      } else if (modelSrc) {
        loadGLB(modelSrc, markerId, version)
          .then(function () {
            updateProgress(100);
            hideLoading();
          })
          .catch(function (err) {
            console.error('[ar-loader] Gagal load model untuk', markerId, err);
            updateProgress(100);
            hideLoading();
          });
      } else {
        updateProgress(100);
        hideLoading();
      }

      // Play audio when marker found
      var audioSrc = parseMarkerModelData(marker).audioSrc;
      if (audioSrc) {
        var audio = audioElements.get(audioSrc);
        if (audio) {
          audio.currentTime = 0;
          audio.play().catch(function(e) {
            console.warn('[ar-loader] Audio play failed:', e.message);
          });
        }
      }
    });

    marker.addEventListener('markerLost', function () {
      bumpMarkerVisibilityVersion(markerId);
      detachModel(markerId);
      // Pause mixer saat marker hilang agar animasi tidak jalan saat model tidak terlihat
      var cached = modelStates.get(modelSrc);
      if (cached && cached.mixer) {
        cached.mixer.timeScale = 0;
      }

      // Pause audio when marker lost
      var audioSrc = parseMarkerModelData(marker).audioSrc;
      if (audioSrc) {
        var audio = audioElements.get(audioSrc);
        if (audio) {
          audio.pause();
        }
      }
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
  if (typeof AFRAME !== 'undefined') {
    // A-Frame 1.5.0 TIDAK emit 'tick' sebagai DOM event — hanya component
    // lifecycle method atau internal renderer loop. Kita gunakan rAF loop
    // yang dimulai saat renderstart, dengan fallback timeout.
    var sceneEl = document.querySelector('a-scene');
    var _animationLoopStarted = false;

    function startAnimationLoop() {
      if (_animationLoopStarted) return;
      _animationLoopStarted = true;
      (function animationLoop() {
        requestAnimationFrame(animationLoop);
        if (globalClock) {
          var dt = globalClock.getDelta();
          if (dt > 0.1) dt = 0.1;
          _arAnimationMixers.forEach(function (m) { m.update(dt); });
        }
      })();
    }

    sceneEl.addEventListener('renderstart', function () {
      startAnimationLoop();
    });

    // Fallback: mulai loop 2 detik setelah onSceneReady
    // (jika renderstart tidak fire dalam kasus AR.js tertentu)
    setTimeout(function () {
      if (!_animationLoopStarted) {
        startAnimationLoop();
      }
    }, 2000);
  }
  if (typeof THREE !== 'undefined') initLoaders();
  initMarkerListeners();
}
