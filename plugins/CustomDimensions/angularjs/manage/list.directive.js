/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-custom-dimensions-list>
 */
(function () {
    angular.module('piwikApp').directive('piwikCustomDimensionsList', piwikCustomDimensionsList);

    piwikCustomDimensionsList.$inject = ['piwik'];

    function piwikCustomDimensionsList(piwik){

        return {
            restrict: 'A',
            scope: {},
            templateUrl: 'plugins/CustomDimensions/angularjs/manage/list.directive.html?cb=' + piwik.cacheBuster,
            controller: 'CustomDimensionsListController',
            controllerAs: 'dimensionsList'
        };
    }
})();