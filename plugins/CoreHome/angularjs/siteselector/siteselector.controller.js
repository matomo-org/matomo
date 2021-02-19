/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('SiteSelectorController', SiteSelectorController);

    SiteSelectorController.$inject = ['$scope', 'siteSelectorModel', 'piwik', 'AUTOCOMPLETE_MIN_SITES'];

    function SiteSelectorController($scope, siteSelectorModel, piwik, AUTOCOMPLETE_MIN_SITES){

        $scope.model = siteSelectorModel;

        $scope.model.loadInitialSites();

        $scope.autocompleteMinSites = AUTOCOMPLETE_MIN_SITES;
        $scope.activeSiteId = piwik.idSite;

        $scope.switchSite = function (site, $event) {

            // for Mac OS cmd key needs to be pressed, ctrl key on other systems
            var controlKey = navigator.userAgent.indexOf("Mac OS X") !== -1 ? $event.metaKey : $event.ctrlKey;

            if ($event && controlKey && $event.target && $event.target.href) {
                window.open($event.target.href, "_blank");
                return;
            }

            $scope.selectedSite = {id: site.idsite, name: site.name};

            if (!$scope.switchSiteOnSelect || $scope.activeSiteId == site.idsite) {
                return;
            }

            $scope.model.loadSite(site.idsite);
        };

        $scope.getUrlAllSites = function () {
            var newParameters = 'module=MultiSites&action=index';
            return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters);
        };
        $scope.getUrlForSiteId = function (idSite) {
            var idSiteParam   = 'idSite=' + idSite;
            var newParameters = 'segment=&' + idSiteParam;
            var hash = piwik.broadcast.isHashExists() ? piwik.broadcast.getHashFromUrl() : "";
            return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters) +
            '#' + piwik.helper.getQueryStringWithParametersModified(hash.substring(1), newParameters);
        };

        piwikHelper.registerShortcut('w', _pk_translate('CoreHome_ShortcutWebsiteSelector'), function(event) {
            if (event.altKey) {
                return;
            }
            if (event.preventDefault) {
                event.preventDefault();
            } else {
                event.returnValue = false; // IE
            }
            $('.siteSelector .title').trigger('click').focus();
        });
    }

})();
