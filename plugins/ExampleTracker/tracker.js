/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

(function () {

    var configs = {};

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
                if (!eventParams || !eventParams.tracker) {
                    return '';
                }

                return '&myCustomVisitParam=' + 500 + eventParams.tracker.getSiteId();
            },
        });
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