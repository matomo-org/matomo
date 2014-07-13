/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp.filter').filter('trim', function() {

    return function(string) {
        if (string) {
            return $.trim('' + string);
        }

        return string;
    };
});
