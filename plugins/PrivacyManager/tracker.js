/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {

    function init() {
        if ('object' === typeof window && !window.Matomo) {
            // Matomo is not defined yet
            return;
        }

        // Disable cookies and remove them when a tracker is created.
        //
        // Replacing these also stops the tracker reporting cookie consent, but that is not what
        // makes a site tracking without cookies measure every visitor the same way: the server
        // refuses consent for such a site whichever consent method the page called.
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
