/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    // NOTE: there must be at least two plugins w/ downgraded angular modules added here (other than CoreAngular which
    // is the top level), otherwise, due to @angular/upgrade which checks if the number of downgraded modules > 1, the
    // module's injector won't be used if there's only one downgraded module.
    //
    // (this generally won't be the case since most core plugins will have angular modules, but it's worth noting)
    var pluginAngularModules = [];
    (window.piwik.pluginsLoadedAndActivated || []).forEach(function (pluginName) {
        var pluginNameKebab = toKebabCase(pluginName);
        if (window.matomo[pluginNameKebab] && window.matomo[pluginNameKebab].angularModuleName) {
            pluginAngularModules.push(window.matomo[pluginNameKebab].angularModuleName);
        }
    });

    angular.module('piwikApp', [
        'ngSanitize',
        'ngAnimate',
        'ngCookies',
        'piwikApp.config',
        'piwikApp.service',
        'piwikApp.directive',
        'piwikApp.filter',
    ].concat(pluginAngularModules));

    angular.module('piwikApp').config(['$locationProvider', function($locationProvider) {
        $locationProvider.html5Mode({ enabled: false, rewriteLinks: false }).hashPrefix('');
    }]);

    function toKebabCase(s) {
        return s.substring(0, 1).toLowerCase() + s.substring(1).replace(/([A-Z])/, '-$1').toLowerCase();
    }
})();