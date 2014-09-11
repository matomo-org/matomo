/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
 *      show-all-sites-item="true">
 *
 * Within a form
 * <div piwik-siteselector input-name="siteId">
 *
 * Events:
 * Triggers a `change` event on any change
 * <div piwik-siteselector id="mySelector">
 * $('#mySelector').on('change', function (event) { event.id/event.name })
 */
angular.module('piwikApp').directive('piwikSiteselector', function($document, piwik, $filter){
    var defaults = {
        name: '',
        siteid: piwik.idSite,
        sitename: piwik.siteName,
        allSitesLocation: 'bottom',
        allSitesText: $filter('translate')('General_MultiSitesSummary'),
        showSelectedSite: 'false',
        showAllSitesItem: 'true',
        switchSiteOnSelect: 'true'
    };

    return {
        restrict: 'A',
        scope: {
            showSelectedSite: '=',
            showAllSitesItem: '=',
            switchSiteOnSelect: '=',
            inputName: '@name',
            allSitesText: '@',
            allSitesLocation: '@'
        },
        templateUrl: 'plugins/CoreHome/angularjs/siteselector/siteselector.html?cb=' + piwik.cacheBuster,
        controller: 'SiteSelectorController',
        compile: function (element, attrs) {

            for (var index in defaults) {
               if (!attrs[index]) { attrs[index] = defaults[index]; }
            }

            return function (scope, element, attrs) {

                // selectedSite.id|.name + model is hard-coded but actually the directive should not know about this
                scope.selectedSite.id   = attrs.siteid;
                scope.selectedSite.name = attrs.sitename;

                if (!attrs.siteid || !attrs.sitename) {
                    scope.model.loadInitialSites();
                }

                scope.$watch('selectedSite.id', function (newValue, oldValue, scope) {
                    if (newValue != oldValue) {
                        element.attr('siteid', newValue);
                        element.trigger('change', scope.selectedSite);
                    }
                });

                /** use observe to monitor attribute changes
                attrs.$observe('maxsitenamewidth', function(val) {
                    // for instance trigger a function or whatever
                }) */
            };
        }
    };
});