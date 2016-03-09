/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-plugin-settings>
 */
(function () {
    angular.module('piwikApp').directive('piwikPluginSettings', piwikPluginSettings);

    piwikPluginSettings.$inject = ['piwik'];

    function piwikPluginSettings(piwik){
        var defaults = {
            mode: ''
        };

        return {
            restrict: 'A',
            scope: {
               mode: '@'
            },
            templateUrl: 'plugins/CorePluginsAdmin/angularjs/plugin-settings/plugin-settings.directive.html?cb=' + piwik.cacheBuster,
            controller: 'PluginSettingsController',
            controllerAs: 'pluginSettings',
            compile: function (element, attrs) {

                for (var index in defaults) {
                    if (defaults.hasOwnProperty(index) && attrs[index] === undefined) {
                        attrs[index] = defaults[index];
                    }
                }

                return function (scope, element, attrs) {

                };
            }
        };
    }
})();