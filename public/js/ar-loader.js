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
var audioElements = new Map();            // audioSrc → HTMLAudioElement (legacy)
var audioBufferCache = new Map();         // audioSrc → AudioBuffer
var audioContext = null;                  // Web Audio API context (lazy init)
var masterGain = null;                    // GainNode untuk mute/unmute via volume
var activeAudioSources = new Map();      // markerId → { source, startedAt }
var audioMuted = false;                  // default unmuted
window._arAudioElements = audioElements;  // debug: accessible from console
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

// ── Preload audio untuk satu marker ──────────────────────────────────────────
function preloadAudioForMarker(marker) {
  var audioSrc = parseMarkerModelData(marker).audioSrc;
  if (!audioSrc) return;

  // Pre-decode ke AudioBuffer — dilakukan di background saat AR scene dimuat.
  // Setelah ini, playAudioWhenReady() akan langsung pakai cache (tanpa fetch lagi).
  decodeAudioToBuffer(audioSrc).then(function() {
    console.log('[ar-loader] Audio pre-decoded:', audioSrc);
  }).catch(function(e) {
    console.error('[ar-loader] Audio pre-decode failed:', audioSrc, e);
    // Fallback: simpan HTML Audio element untuk fallback path
    if (!audioElements.has(audioSrc)) {
      var audio = new Audio(audioSrc);
      audio.preload = 'auto';
      audioElements.set(audioSrc, audio);
    }
  });
}

// ── Web Audio API Manager ─────────────────────────────────────────────────────
// Menggunakan Web Audio API decodeAudioData — bypasses browser autoplay policy
// karena AudioBuffer sudah di-memory sebelum play() dipanggil.
// Ini memperbaiki masalah: "audio tidak play di detection pertama".
function getAudioContext() {
  if (!audioContext) {
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
    masterGain = audioContext.createGain();
    masterGain.connect(audioContext.destination);
    masterGain.gain.value = audioMuted ? 0 : 1; // start unmuted (default)
    console.log('[ar-loader] AudioContext + GainNode created, ' + (audioMuted ? 'muted' : 'unmuted'));
  }
  return audioContext;
}

// Set volume 0 (mute) atau 1 (unmute) via GainNode
// Always updates audioMuted even if masterGain is null so UI state stays consistent
function setMasterVolume(unmuted) {
  audioMuted = !unmuted; // audioMuted = true when muting, false when unmuting
  if (masterGain) {
    masterGain.gain.setValueAtTime(unmuted ? 1 : 0, audioContext.currentTime);
  }
}

// Stop audio source yang sedang playing untuk satu marker
function stopMarkerAudio(markerId) {
  var active = activeAudioSources.get(markerId);
  if (active && active.source) {
    try { active.source.stop(0); } catch(e) {}
    activeAudioSources.delete(markerId);
  }
}

// Decode audio file ke AudioBuffer (sekali saja, di-cache)
function decodeAudioToBuffer(audioSrc) {
  return new Promise(function(resolve, reject) {
    // Sudah di-cache → langsung resolve
    if (audioBufferCache.has(audioSrc)) {
      resolve(audioBufferCache.get(audioSrc));
      return;
    }

    fetch(audioSrc).then(function(res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.arrayBuffer();
    }).then(function(arrayBuffer) {
      var ctx = getAudioContext();
      // Decode bisa throw synchronously jika format corrupt
      try {
        ctx.decodeAudioData(arrayBuffer, function(buffer) {
          audioBufferCache.set(audioSrc, buffer);
          console.log('[ar-loader] AudioBuffer decoded + cached:', audioSrc);
          resolve(buffer);
        }, function(err) {
          reject(err);
        });
      } catch(e) {
        reject(e);
      }
    }).catch(reject);
  });
}

// ── Play audio: Web Audio API (preferred) + HTML Audio fallback ───────────────
function playAudioWhenReady(audioSrc, label, markerId) {
  label = label || 'marker';
  var markerKey = markerId || audioSrc;

  // Decode dulu (dari cache atau fetch baru), play.
  // masterGain.setVolume(0/1) handle mute/unmute — tidak perlu skip here.
  decodeAudioToBuffer(audioSrc).then(function(buffer) {
    var ctx = getAudioContext();

    // Jika context suspended, TUNGGU sampai benar-benar aktif SEBELUM play.
    // ctx.resume() mengembalikan promise — promise.then(...) menjamin urutan.
    var doPlay = function () {
      // Stop source lama jika ada (prevent double audio)
      stopMarkerAudio(markerKey);

      var source = ctx.createBufferSource();
      source.buffer = buffer;
      source.connect(masterGain); // 🔗 ke GainNode (bukan langsung ke destination)
      source.start(0);

      activeAudioSources.set(markerKey, { source: source });
      console.log('[ar-loader] Audio playing (Web Audio API, ' + label + '):', audioSrc);
    };

    if (ctx.state === 'suspended') {
      console.log('[ar-loader] AudioContext suspended — waiting for resume...');
      ctx.resume().then(function () {
        console.log('[ar-loader] AudioContext resumed — playing audio');
        doPlay();
      });
    } else {
      console.log('[ar-loader] AudioContext running — playing audio now');
      doPlay();
    }

  }).catch(function(e) {
    console.error('[ar-loader] Web Audio failed, fallback to HTML Audio:', audioSrc, e);
    // Fallback: HTML Audio (bisa kena autoplay block — tapi jika user sudah unmute,
    // berarti ada gesture, jadi peluang berhasil lebih tinggi)
    var cachedAudio = audioElements.get(audioSrc);
    if (cachedAudio) {
      cachedAudio.currentTime = 0;
      cachedAudio.play().catch(function(err) {
        console.error('[ar-loader] HTML Audio fallback also failed:', err);
      });
    }
  });
}

// marker-audio-handler was removed — audio playback is fully owned by
// playAudioWhenReady() called in initMarkerListeners (markerFound/markerLost).

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

    // Pre-decode audio ke AudioBuffer saat AR scene ready
    // Ini memastikan audio sudah di memory sebelum markerFound pertama kali fire
    preloadAudioForMarker(marker);

    marker.addEventListener('markerFound', function () {
      var version = bumpMarkerVisibilityVersion(markerId);
      showLoading();
      updateProgress(5);

      // Play audio langsung saat marker terdeteksi — tidak perlu tunggu tick
      var audioSrc = parseMarkerModelData(marker).audioSrc;
      if (audioSrc) {
        playAudioWhenReady(audioSrc, 'markerFound:' + markerId, markerId);
      }

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
    });

    marker.addEventListener('markerLost', function () {
      bumpMarkerVisibilityVersion(markerId);

      // Stop Web Audio source saat marker hilang
      stopMarkerAudio(markerId);

      // Pause HTML Audio fallback jika ada
      var audioSrc = parseMarkerModelData(marker).audioSrc;
      if (audioSrc) {
        var audio = audioElements.get(audioSrc);
        if (audio && !audio.paused) {
          audio.pause();
          audio.currentTime = 0;
        }
      }

      detachModel(markerId);
      // Pause mixer saat marker hilang agar animasi tidak jalan saat model tidak terlihat
      var cached = modelStates.get(modelSrc);
      if (cached && cached.mixer) {
        cached.mixer.timeScale = 0;
      }
    });
  });

  console.log('[ar-loader] ' + markers.length + ' marker(s) di-register');
}

// ── Audio Toggle Button ──────────────────────────────────────────────────────
function initAudioToggle() {
  var btn = document.getElementById('audio-toggle');
  if (!btn) return;

  // Eager init AudioContext agar masterGain tersedia sebelum klik pertama
  getAudioContext();

  // Sinkronkan icon dengan state awal audio
  updateIcon();

  var togglePending = false;

  function updateIcon() {
    var offIcon = document.getElementById('icon-audio-off');
    var onIcon = document.getElementById('icon-audio-on');
    if (!audioMuted) {
      offIcon.style.display = 'none';
      onIcon.style.display = 'block';
      btn.style.background = 'rgba(0,140,0,.9)';
    } else {
      offIcon.style.display = 'block';
      onIcon.style.display = 'none';
      btn.style.background = 'rgba(194,92,6,.9)';
    }
  }

  btn.addEventListener('click', function () {
    if (togglePending) return;
    togglePending = true;
    setTimeout(function () { togglePending = false; }, 300);

    setMasterVolume(audioMuted); // audioMuted=true → setMasterVolume(true) → unmute; audioMuted=false → mute
    updateIcon();
    console.log('[ar-loader] Audio muted:', audioMuted);

    // User gesture — resume AudioContext (safe to call repeatedly)
    var ctx = getAudioContext();
    if (ctx.state === 'suspended') {
      ctx.resume().then(function () {
        console.log('[ar-loader] AudioContext resumed');
      });
    }
  });
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
  initAudioToggle();
}
