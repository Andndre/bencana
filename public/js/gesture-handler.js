/* global AFRAME */
AFRAME.registerComponent('gesture-handler', {
  schema: {
    minScale: { default: 0.3 },
    maxScale: { default: 8 }
  },

  init: function () {
    this.activeMarker = null;
    this.currentScale = 1;

    this.el.sceneEl.addEventListener('markerFound', function (evt) {
      if (evt.detail.id === this.el.parentElement.id) {
        this.activeMarker = evt.detail.id;
        this.currentScale = 1;
        this.el.setAttribute('scale', '1 1 1');
      }
    }.bind(this));

    this.el.sceneEl.addEventListener('markerLost', function (evt) {
      if (evt.detail.id === this.el.parentElement.id) {
        this.activeMarker = null;
      }
    }.bind(this));

    this.el.addEventListener('onefingermove', this.onOneFingerMove.bind(this));
    this.el.addEventListener('twofingermove',  this.onTwoFingerMove.bind(this));
  },

  onOneFingerMove: function (evt) {
    if (!this.activeMarker) return;
    var deltaX = evt.detail.deltaX;
    var rot = this.el.getAttribute('rotation') || { x: 0, y: 0, z: 0 };
    this.el.setAttribute('rotation', {
      x: rot.x,
      y: rot.y + deltaX,
      z: rot.z
    });
  },

  onTwoFingerMove: function (evt) {
    if (!this.activeMarker) return;
    var scale = Math.max(
      this.data.minScale,
      Math.min(this.data.maxScale, this.currentScale + evt.detail.spread)
    );
    this.currentScale = scale;
    this.el.setAttribute('scale', scale + ' ' + scale + ' ' + scale);
  }
});
