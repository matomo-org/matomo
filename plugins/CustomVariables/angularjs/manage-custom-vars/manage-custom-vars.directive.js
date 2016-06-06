/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-manage-custom-vars>
 */
(function () {
    angular.module('piwikApp').directive('piwikManageCustomVars', piwikManageCustomVars);

    piwikManageCustomVars.$inject = ['piwik'];

    function piwikManageCustomVars(piwik){
        return {
            restrict: 'A',
            scope: {},
            templateUrl: 'plugins/CustomVariables/angularjs/manage-custom-vars/manage-custom-vars.directive.html?cb=' + piwik.cacheBuster,
            controller: 'ManageCustomVarsController',
            controllerAs: 'manageCustomVars'
        };
    }
})();