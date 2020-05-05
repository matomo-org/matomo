/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div matomo-anonymize-log-data>
 */
(function () {
    angular.module('piwikApp').directive('matomoAnonymizeLogData', anonymizeLogData);

    anonymizeLogData.$inject = ['piwik'];

    function anonymizeLogData(piwik){
        return {
            restrict: 'A',
            scope: {},
            templateUrl: 'plugins/PrivacyManager/angularjs/anonymize-log-data/anonymize-log-data.directive.html?cb=' + piwik.cacheBuster,
            controller: 'AnonymizeLogDataController',
            controllerAs: 'anonymizeLogData',
            compile: function (element, attrs) {

            }
        };
    }
})();