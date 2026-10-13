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
        // Replacing these also stops the tracker reporting cookie consent to the server, so a
        // compliance policy that withholds campaign parameters keeps withholding them for every
        // visitor of a site tracking without cookies, whether or not they consented. That is
        // intended: forcing cookieless tracking is the strictest consent-exempt posture a site can
        // choose, and campaign values are treated as not collected under it. It is a consequence
        // of these stubs rather than a limitation of cookieless tracking itself, so reporting
        // consent here would be possible if that decision were ever revisited.
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
