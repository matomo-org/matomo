/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.filter').filter('translate', translate);

    function translate() {

        return function(key, value1, value2, value3) {
            var values = [];
            if (arguments && arguments.length > 1) {
                for (var index = 1; index < arguments.length; index++) {
                    values.push(arguments[index]);
                }
            }
            return _pk_translate(key, values);
        };
    }
})();