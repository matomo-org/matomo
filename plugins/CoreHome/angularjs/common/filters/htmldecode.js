/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.filter').filter('htmldecode', htmldecode);

    htmldecode.$inject = ['piwik'];

    /**
     * Be aware that this filter can cause XSS so only use it when you're sure it is safe.
     * Eg it should be safe when it is afterwards escaped by angular sanitize again.
     */
    function htmldecode(piwik) {

        return function(text) {
            if (text && text.length) {
                return piwik.helper.htmlDecode(text);
            }

            return text;
        };
    }
})();
