/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div matomo-manage-gdpr>
 */
(function () {
    angular.module('piwikApp').directive('matomoManageGdpr', matomoManageGdpr);

    matomoManageGdpr.$inject = ['piwik'];

    function matomoManageGdpr(piwik){
        return {
            restrict: 'A',
            scope: {},
            templateUrl: 'plugins/PrivacyManager/angularjs/manage-gdpr/managegdpr.directive.html?cb=' + piwik.cacheBuster,
            controller: 'ManageGdprController',
            controllerAs: 'manageGdpr',
            compile: function (element, attrs) {

            }
        };
    }
})();