;(function (window) {
    "use strict";

    var lastTimer = -1;

    var install = function (Visibility) {

        // Run callback every `interval` milliseconds if page is visible and
        // every `hiddenInterval` milliseconds if page is hidden.
        //
        //   Visibility.every(60 * 1000, 5 * 60 * 1000, function () {
        //       checkNewMails();
        //   });
        //
        // You can skip `hiddenInterval` and callback will be called only if
        // page is visible.
        //
        //   Visibility.every(1000, function () {
        //       updateCountdown();
        //   });
        //
        // It is analog of `setInterval(callback, interval)` but use visibility
        // state.
        //
        // It return timer ID, that you can use in `Visibility.stop(id)` to stop
        // timer (`clearInterval` analog).
        // Warning: timer ID is different from interval ID from `setInterval`,
        // so don’t use it in `clearInterval`.
        //
        // On change state from hidden to visible timers will be execute.
        Visibility.every = function (interval, hiddenInterval, callback) {
            Visibility._time();

            if ( !callback ) {
                callback = hiddenInterval;
                hiddenInterval = null;
            }

            lastTimer += 1;
            var number = lastTimer;

            Visibility._timers[number] = {
                visible:  interval,
                hidden:   hiddenInterval,
                callback: callback
            };
            Visibility._run(number, false);

            if ( Visibility.isSupported() ) {
                Visibility._listen();
            }
            return number;
        };

        // Stop timer from `every` method by it ID (`every` method return it).
        //
        //   slideshow = Visibility.every(5 * 1000, function () {
        //       changeSlide();
        //   });
        //   $('.stopSlideshow').click(function () {
        //       Visibility.stop(slideshow);
        //   });
        Visibility.stop = function(id) {
            if ( !Visibility._timers[id] ) {
                return false;
            }
            Visibility._stop(id);
            delete Visibility._timers[id];
            return true;
        };

        // Callbacks and intervals added by `every` method.
        Visibility._timers = { };

        // Initialize variables on page loading.
        Visibility._time = function () {
            if ( Visibility._timed ) {
                return;
            }
            Visibility._timed     = true;
            Visibility._wasHidden = Visibility.hidden();

            Visibility.change(function () {
                Visibility._stopRun();
                Visibility._wasHidden = Visibility.hidden();
            });
        };

        // Try to run timer from every method by it’s ID. It will be use
        // `interval` or `hiddenInterval` depending on visibility state.
        // If page is hidden and `hiddenInterval` is null,
        // it will not run timer.
        //
        // Argument `runNow` say, that timers must be execute now too.
        Visibility._run = function (id, runNow) {
            var interval,
                timer = Visibility._timers[id];

            if ( Visibility.hidden() ) {
                if ( null === timer.hidden ) {
                    return;
                }
                interval = timer.hidden;
            } else {
                interval = timer.visible;
            }

            var runner = function () {
                timer.last = new Date();
                timer.callback.call(window);
            }

            if ( runNow ) {
                var now  = new Date();
                var last = now - timer.last ;

                if ( interval > last ) {
                    timer.delay = setTimeout(function () {
                        runner();
                        timer.id = setInterval(runner, interval);
                    }, interval - last);
                } else {
                    runner();
                    timer.id = setInterval(runner, interval);
                }

            } else {
              timer.id = setInterval(runner, interval);
            }
        };

        // Stop timer from `every` method by it’s ID.
        Visibility._stop = function (id) {
            var timer = Visibility._timers[id];
            clearInterval(timer.id);
            clearTimeout(timer.delay);
            delete timer.id;
            delete timer.delay;
        };

        // Listener for `visibilitychange` event.
        Visibility._stopRun = function (event) {
            var isHidden  = Visibility.hidden(),
                wasHidden = Visibility._wasHidden;

            if ( (isHidden && !wasHidden) || (!isHidden && wasHidden) ) {
                for ( var i in Visibility._timers ) {
                    Visibility._stop(i);
                    Visibility._run(i, !isHidden);
                }
            }
        };

        return Visibility;
    }

    if ( typeof(module) != 'undefined' && module.exports ) {
        module.exports = install(require('./visibility.core'));
    } else {
        install(window.Visibility)
    }

})(window);
