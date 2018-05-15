/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-trusted-hosts-setting>
 */
(function () {
    angular.module('piwikApp').directive('piwikTrustedHostsSetting', piwikTrustedHostsSetting);

    piwikTrustedHostsSetting.$inject = ['piwik'];

    function piwikTrustedHostsSetting(piwik){

        return {
            restrict: 'A',
            transclude: true,
            template: '<div ng-transclude></div>',
            controller: 'TrustedHostsController',
            controllerAs: 'trustedHosts',
            compile: function (element, attrs) {

                return function (scope, element, attrs, controller) {
                    controller.hosts = [];
                    angular.forEach(JSON.parse(attrs.piwikTrustedHostsSetting), function (host) {
                        controller.hosts.push({host: host});
                    });
                };
            }
        };
    }
})();