/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('SitesManagerSiteController', SitesManagerSiteController);

    SitesManagerSiteController.$inject = ['$scope', '$filter', 'sitesManagerApiHelper', 'sitesManagerTypeModel', 'piwikApi', '$timeout'];

    function SitesManagerSiteController($scope, $filter, sitesManagerApiHelper, sitesManagerTypeModel, piwikApi, $timeout) {
        /*
        var updateView = function () { // ignoring forn ow
            $timeout(function () {
                $('.editingSite').find('select').material_select();
                Materialize.updateTextFields();
            });
        }
        */

        var initModel = function() {
            if (isSiteNew()) {
            } else {
                $scope.site.excluded_ips = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.excluded_ips);
                $scope.site.excluded_parameters = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.excluded_parameters);
                $scope.site.excluded_user_agents = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.excluded_user_agents);
                $scope.site.sitesearch_keyword_parameters = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.sitesearch_keyword_parameters);
                $scope.site.sitesearch_category_parameters = sitesManagerApiHelper.commaDelimitedFieldToArray($scope.site.sitesearch_category_parameters);
            }
        };

        var saveSite = function() {
        };

        var isSiteNew = function() {
            return angular.isUndefined($scope.site.idsite);
        };

        var deleteSite = function() {
        };
    }
})();
