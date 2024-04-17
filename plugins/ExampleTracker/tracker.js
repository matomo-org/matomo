/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {

    var configs = {};

    function init() {
        if ('object' === typeof window && 'object' === typeof window.Matomo && 'object' === typeof window.Matomo.ExampleTracker) {
            // do not initialize twice
            return;
        }

        if ('object' === typeof window && !window.Matomo) {
            // matomo is not defined yet
            return;
        }

        Matomo.ExampleTracker = {
            // empty
        };

        Matomo.addPlugin('ExampleTracker', {
            log: function (eventParams) {
                if (!eventParams || !eventParams.tracker) {
                    return '';
                }

                return '&myCustomVisitParam=' + 500 + eventParams.tracker.getSiteId();
            },
        });
    }

    if ('object' === typeof window.Matomo) {
        init();
    } else {
        // tracker is loaded separately for sure
        if ('object' !== typeof window.matomoPluginAsyncInit) {
            window.matomoPluginAsyncInit = [];
        }

        window.matomoPluginAsyncInit.push(init);
    }

})();
