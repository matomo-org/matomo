/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

piwikApp.directive('piwikSiteSelector', function($document, piwik, $filter){

    function getBool(attr, property, defaultValue)
    {
        return angular.isDefined(attr[property]) && 'true' === attr[property];
    }

    return {
        restrict: 'A',
        scope: true,
        templateUrl: 'plugins/CoreHome/javascripts/siteselector/partial.html',
        controller: 'SiteSelectorController',
        compile: function (element, attrs) {
            element.addClass('sites_autocomplete');

            return function (scope, element, attr, ctrl) {

                // why not directly use camel case and for example site-name? If I remember correct this does not work
                // in all of or required IE versions. Alternative can be to define a namespace attribute and prefix all
                // attributes with piwik, eg. piwik-site-name. Again: Not sure if I remember correct, will have a look
                // later
                scope.allSitesLocation = attr.allsiteslocation || 'bottom';
                scope.allSitesText = attr.allsitestext || $filter('translate')('General_MultiSitesSummary');
                scope.selectedSite = {id: attr.siteid || piwik.idSite, name: attr.sitename || ''};
                scope.inputName    = attr.inputname || '';
                scope.showAutocomplete   = getBool(attr, 'showautocomplete', true);
                scope.showSelectedSite   = getBool(attr, 'showselectedsite', false);
                scope.switchSiteOnSelect = getBool(attr, 'switchsiteonselect', true);
                scope.showAllSitesItem   = getBool(attr, 'showallsitesitem', true);
                /*
                 $scope.max_sitename_width = 130; // can be removed?
                 */

                function passSiteId (newValue, oldValue, scope) {
                    if (newValue != oldValue) {
                        element.attr('siteid', newValue);
                        element.trigger('change', scope.selectedSite);
                    }
                }

                scope.$watch('selectedSite.id', passSiteId);
            }
        }
    }
});