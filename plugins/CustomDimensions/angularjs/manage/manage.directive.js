/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-custom-dimensions-manage>
 */
(function () {
    angular.module('piwikApp').directive('piwikCustomDimensionsManage', piwikManageCustomDimensions);

    piwikManageCustomDimensions.$inject = ['piwik'];

    function piwikManageCustomDimensions(piwik){

        return {
            restrict: 'A',
            scope: {},
            templateUrl: 'plugins/CustomDimensions/angularjs/manage/manage.directive.html?cb=' + piwik.cacheBuster,
            controller: 'ManageCustomDimensionsController',
            controllerAs: 'manageDimensions'
        };
    }
})();