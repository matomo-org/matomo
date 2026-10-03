(function () {
  if (window.M) {
    return;
  }

  // define "window.M" and prevent overriding it
  // to allow patching Materialize internals
  const _materializeWrapper = {};

  Object.defineProperty(window, 'M', {
    get() {
      return _materializeWrapper;
    },
    set() {
      // prevent wrapper overriding
    },
  });

  // wrap "window.M.anime" and deactivate animations
  let _materializeAnimate;

  Object.defineProperty(_materializeWrapper, 'anime', {
    get() {
      return _materializeAnimate;
    },
    set(newAnimate) {
      _materializeAnimate = function (params) {
        if (!params) {
          params = {};
        }

        params.duration = 0;

        return newAnimate(params);
      }

      for (const [key, value] of Object.entries(newAnimate)) {
        _materializeAnimate[key] = value;
      }
    },
  });
})();
