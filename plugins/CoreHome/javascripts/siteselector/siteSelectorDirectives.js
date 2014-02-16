/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

piwikApp.directive('piwikSiteSelector', function($document, piwik, $filter){
    var defaults = {
        'name': '',
        'siteid': piwik.idSite,
        'sitename': piwik.siteName,
        'all-sites-location': 'bottom',
        'all-sites-text': $filter('translate')('General_MultiSitesSummary'),
        'show-selected-site': 'false',
        'show-all-sites-item': 'true',
        'switch-site-on-select': 'true',
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
        templateUrl: 'plugins/CoreHome/javascripts/siteselector/siteSelectorPartial.html',
        controller: 'SiteSelectorController',
        compile: function (element, attrs) {
            attrs.$addClass('sites_autocomplete');

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
            }
        }
    }
});