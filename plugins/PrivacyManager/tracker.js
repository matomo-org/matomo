/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

(function () {

    function init() {
        if ('object' === typeof window && !window.Matomo) {
            // Matomo is not defined yet
            return;
        }

        // disable cookies and remove them when a tracker is created
        window.Matomo.on('TrackerSetup', function(tracker) {
            tracker.setCookieConsentGiven=function(){};
            tracker.rememberCookieConsentGiven=function(){};
            tracker.disableCookies();
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