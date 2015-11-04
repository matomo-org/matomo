/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ManageCustomVarsController', ManageCustomVarsController);

    ManageCustomVarsController.$inject = ['manageCustomVarsModel', 'piwik', '$filter'];

    function ManageCustomVarsController(manageCustomVarsModel, piwik, $filter) {
        manageCustomVarsModel.fetchUsages();

        this.model = manageCustomVarsModel;
        this.siteName = piwik.siteName;
        this.scopes = ['visit', 'page'];
    }
})();