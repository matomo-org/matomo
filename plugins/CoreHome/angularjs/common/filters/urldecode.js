/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.filter').filter('urldecode', urldecode);

    urldecode.$inject = [];

    function urldecode() {

        return function(text) {
            if (text && text.length) {
                return decodeURIComponent(text);
            }

            return text;
        };
    }
})();
