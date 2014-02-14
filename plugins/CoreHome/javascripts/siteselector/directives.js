/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

piwikApp.directive('piwikSiteSelector', function($document, piwik, $filter){

    return {
        restrict: 'A',
        // why not directly use camel case and for example site-name? If I remember correct this does not work
        // in all of or required IE versions. Alternative can be to define a namespace attribute and prefix all
        // attributes with piwik, eg. piwik-site-name. Again: Not sure if I remember correct, will have a look
        // later
        scope: {
            showAutocomplete: '=showautocomplete',
            showSelectedSite: '=showselectedsite',
            showAllSitesItem: '=showallsitesitem',
            switchSiteOnSelect: '=switchsiteonselect',
            maxSitenameWidth: '=maxsitenamewidth',
            inputName: '@inputname',
            allSitesText: '@allsitestext',
            allSitesLocation: '@allsiteslocation'
        },
        templateUrl: 'plugins/CoreHome/javascripts/siteselector/partial.html',
        controller: 'SiteSelectorController',
        compile: function (element, attrs) {
            attrs.$addClass('sites_autocomplete');

            // define default values
            if (!attrs.allsiteslocation) attrs.allsiteslocation = 'bottom';
            if (!attrs.allsitestext) attrs.allsitestext = $filter('translate')('General_MultiSitesSummary');
            if (!attrs.siteid) attrs.siteid = '';
            if (!attrs.sitename) attrs.sitename = '';
            if (!attrs.inputname) attrs.inputname = '';
            if (!attrs.showautocomplete) attrs.showautocomplete = 'true';
            if (!attrs.showselectedsite) attrs.showselectedsite = 'false';
            if (!attrs.switchsiteonselect) attrs.switchsiteonselect = 'true';
            if (!attrs.showallsitesitem) attrs.showallsitesitem = 'true';
            if (!attrs.maxSitenameWidth) attrs.maxsitenamewidth = '130'; // can be removed?

            return function (scope, element, attrs) {

                // selectedSite.id|.name is hard-coded but actually the directive should not know about this
                scope.selectedSite.id   = attrs.siteid;
                scope.selectedSite.name = attrs.sitename;

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