/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-siteselector>
 *
 * More advanced example
 * <div piwik-siteselector
 *      show-selected-site="true" show-all-sites-item="true" switch-site-on-select="true"
 *      all-sites-location="top|bottom" all-sites-text="test" show-selected-site="true"
 *      show-all-sites-item="true" only-sites-with-admin-access="true">
 *
 * Within a form
 * <div piwik-siteselector input-name="siteId">
 *
 * Events:
 * Triggers a `change` event on any change
 * <div piwik-siteselector id="mySelector">
 * $('#mySelector').on('change', function (event) { event.id/event.name })
 */
(function () {
    angular.module('piwikApp').directive('piwikSiteselector', piwikSiteselector);

    piwikSiteselector.$inject = ['$document', 'piwik', '$filter', '$timeout'];

    function piwikSiteselector($document, piwik, $filter, $timeout){
        return {
            restrict: 'A',
            scope: {
                showSelectedSite: '=',
                showAllSitesItem: '=',
                switchSiteOnSelect: '=',
                onlySitesWithAdminAccess: '=',
                inputName: '@name',
                allSitesText: '@',
                allSitesLocation: '@',
                placeholder: '@'
            },
            require: "?ngModel",
            templateUrl: 'plugins/CoreHome/angularjs/siteselector/siteselector.directive.html?cb=' + piwik.cacheBuster,
            controller: 'SiteSelectorController',
            compile: function (element, attrs) {

                return function (scope, element, attrs, ngModel) {
                    scope.$watch('view.showSitesList', function (newValue) { // TODO: is this needed?
                        element.toggleClass('expanded', !! newValue);
                    });

                };
            }
        };
    }
})();
