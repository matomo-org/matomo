/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp', [
        'ngSanitize',
        'ngAnimate',
        'ngCookies',
        'ngDialog',
        'piwikApp.config',
        'piwikApp.service',
        'piwikApp.directive',
        'piwikApp.filter'
    ]);
    angular.module('app', []);
})();