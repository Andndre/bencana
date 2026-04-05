/* global AFRAME */
AFRAME.registerComponent('gesture-detector', {
  schema: {
    enabled: { default: true },
    rotationSpeed: { default: 0.5 },
    positionSpeed: { default: 0.1 }
  },

  init: function () {
    this.singleTouchFinger = null;
    this.multiTouchFingers = [];
    this.isMultiTouch = false;
    this.lastTouchPositions = {};
    this.touchStarted = false;

    this.el.addEventListener('touchstart', this.onTouchStart.bind(this));
    this.el.addEventListener('touchmove',  this.onTouchMove.bind(this));
    this.el.addEventListener('touchend',   this.onTouchEnd.bind(this));
  },

  onTouchStart: function (evt) {
    evt.preventDefault();
    var touches = evt.touches;

    if (touches.length === 1) {
      this.isMultiTouch = false;
      this.singleTouchFinger = touches[0].identifier;
      this.lastTouchPositions[this.singleTouchFinger] = {
        x: touches[0].clientX, y: touches[0].clientY
      };
      this.el.emit('onefingerstart');
      this.touchStarted = true;
    } else if (touches.length === 2) {
      this.isMultiTouch = true;
      this.multiTouchFingers = [touches[0].identifier, touches[1].identifier];
      this.lastTouchPositions[this.multiTouchFingers[0]] = {
        x: touches[0].clientX, y: touches[0].clientY
      };
      this.lastTouchPositions[this.multiTouchFingers[1]] = {
        x: touches[1].clientX, y: touches[1].clientY
      };
      this.initialPinchDistance = Math.hypot(
        touches[0].clientX - touches[1].clientX,
        touches[0].clientY - touches[1].clientY
      );
      this.el.emit('twofingerstart');
    }
  },

  onTouchMove: function (evt) {
    evt.preventDefault();
    var touches = evt.touches;

    if (!this.data.enabled) return;

    if (this.isMultiTouch && touches.length === 2) {
      var f0 = this.multiTouchFingers[0];
      var f1 = this.multiTouchFingers[1];
      var t0 = Array.prototype.slice.call(touches).find(function (t) { return t.identifier === f0; }) || touches[0];
      var t1 = Array.prototype.slice.call(touches).find(function (t) { return t.identifier === f1; }) || touches[1];
      var currDist = Math.hypot(t0.clientX - t1.clientX, t0.clientY - t1.clientY);
      var delta    = (currDist - this.initialPinchDistance) / 100;

      this.el.emit('twofingermove', {
        spread: delta,
        rotation: 0
      });

      this.initialPinchDistance = currDist;
    } else if (this.singleTouchFinger !== null) {
      var touch = Array.prototype.slice.call(touches).find(function (t) { return t.identifier === this.singleTouchFinger; }.bind(this));
      if (!touch) return;
      var prev = this.lastTouchPositions[this.singleTouchFinger];
      if (!prev) return;

      var dx = (touch.clientX - prev.x) * this.data.rotationSpeed;
      var dy = (touch.clientY - prev.y) * this.data.rotationSpeed;

      this.el.emit('onefingermove', { deltaX: dx, deltaY: dy });

      this.lastTouchPositions[this.singleTouchFinger] = {
        x: touch.clientX, y: touch.clientY
      };
    }
  },

  onTouchEnd: function (evt) {
    if (this.singleTouchFinger !== null) {
      this.el.emit('onefingerend');
      this.singleTouchFinger = null;
    }
    if (this.isMultiTouch) {
      this.el.emit('twofingerend');
      this.isMultiTouch = false;
    }
    this.touchStarted = false;
  }
});
