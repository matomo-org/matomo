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


    }

})();
