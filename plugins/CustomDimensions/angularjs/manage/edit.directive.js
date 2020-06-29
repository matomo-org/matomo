/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-custom-dimensions-edit>
 */
(function () {
    angular.module('piwikApp').directive('piwikCustomDimensionsEdit', piwikCustomDimensionsEdit);

    piwikCustomDimensionsEdit.$inject = ['piwik'];

    function piwikCustomDimensionsEdit(piwik){

        return {
            restrict: 'A',
            scope: {
                dimensionId: '=',
                dimensionScope: '=',
            },
            templateUrl: 'plugins/CustomDimensions/angularjs/manage/edit.directive.html?cb=' + piwik.cacheBuster,
            controller: 'CustomDimensionsEditController',
            controllerAs: 'editDimension'
        };
    }
})();