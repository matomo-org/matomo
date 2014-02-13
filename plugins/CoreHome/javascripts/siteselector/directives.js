/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

piwikApp.directive('piwikSiteSelector', function($document){
    return {
        restrict: 'A',
        link: function(scope, element, attr, ctrl) {
            // TODO pass Piwik as a depedency

            // why not directly use camel case and for example site-name? If I remember correct this does not work
            // in all of or required IE versions. Alternative can be to define a namespace attribute and prefix all
            // attributes with piwik, eg. piwik-site-name. Again: Not sure if I remember correct, will have a look
            // later

            scope.allWebsitesLinkLocation = attr.allwebsiteslocation || 'bottom';
            scope.showAutocomplete = attr.showautocomplete || true;
            scope.siteName  = attr.sitename || piwik.siteName;
            scope.selector.selectedSiteId = attr.selectedsiteid || piwik.idSite;
            scope.inputName = attr.inputname || '';
            scope.showSelectedSite = attr.showselectedsite || false;
            scope.selectorId = attr.selectorid || false;
            scope.switchSiteOnSelect = attr.switchsiteonselect || true;
            scope.showAllSitesItem = attr.showallsitesitem || true;

            /*
             $scope.max_sitename_width = 130; // can be removed?
             */

        },
        // scope: {showAutoComplete: '=showautocomplete', siteName: '=sitename'},
        templateUrl: 'plugins/CoreHome/javascripts/siteselector/partial.html'
    }
});