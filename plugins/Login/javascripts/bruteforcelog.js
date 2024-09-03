/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function ($) {

    window.bruteForceLog = {
        unblockAllIps: function () {
            piwikHelper.modalConfirm('#confirmUnblockAllIps', {yes: function () {
                var ajaxRequest = new ajaxHelper();
                ajaxRequest.addParams({
                    module: 'API',
                    method: 'Login.unblockBruteForceIPs',
                    format: 'json'
                }, 'get');
                ajaxRequest.setCallback(
                    function (response) {
                        piwikHelper.refreshAfter(0);
                    }
                );
                ajaxRequest.send();
            }});
        }
    };
}(jQuery));
