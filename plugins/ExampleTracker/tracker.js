/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

(function () {

    var configs = {};

    function getTrackerConfig(tracker) {
        tracker.getExtraConfig('ExampleTracker', function (err, config) { // make sure to perform HTTP request as early as possible
            if (err) {
                return;
            }

            configs[tracker.getUniqueTrackerId()] = config;
        });
    }

    function init() {
        if ('object' === typeof window && 'object' === typeof window.Piwik && 'object' === typeof window.Piwik.ExampleTracker) {
            // do not initialize twice
            return;
        }

        if ('object' === typeof window && !window.Piwik) {
            // piwik is not defined yet
            return;
        }

        Piwik.ExampleTracker = {
            // empty
        };

        Piwik.addPlugin('ExampleTracker', {
            log: function (eventParams) {
                if (!eventParams || !eventParams.tracker || !configs[eventParams.tracker.getUniqueTrackerId()]) {
                    return '';
                }

                var config = configs[eventParams.tracker.getUniqueTrackerId()];
                return 'myCustomVisitParam=' + config.myCustomVisitParam;
            },
        });

        if (window.Piwik.initialized) {
            Piwik.on('TrackerAdded', getTrackerConfig);
        } else {
            Piwik.on('TrackerAdded', getTrackerConfig);
        }
    }

    if ('object' === typeof window.Piwik) {
        init();
    } else {
        // tracker is loaded separately for sure
        if ('object' !== typeof window.piwikPluginAsyncInit) {
            window.piwikPluginAsyncInit = [];
        }

        window.piwikPluginAsyncInit.push(init);
    }

})();