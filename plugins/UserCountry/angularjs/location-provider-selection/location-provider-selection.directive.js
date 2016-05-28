/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-location-provider-selection>
 */
(function () {
    angular.module('piwikApp').directive('piwikLocationProviderSelection', piwikLocationProviderSelection);

    piwikLocationProviderSelection.$inject = ['piwik'];

    function piwikLocationProviderSelection(piwik){

        return {
            restrict: 'A',
            transclude: true,
            controller: 'LocationProviderSelectionController',
            controllerAs: 'locationSelector',
            template: '<div ng-transclude></div>',
            compile: function (element, attrs) {

                return function (scope, element, attrs, controller) {
                    controller.selectedProvider = attrs.piwikLocationProviderSelection;
                };
            }
        };
    }
})();