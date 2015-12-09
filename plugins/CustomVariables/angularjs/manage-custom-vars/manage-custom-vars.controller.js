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
        this.scopes = [
            {value: 'visit', name: _pk_translate('General_TrackingScopeVisit')},
            {value: 'page', name: _pk_translate('General_TrackingScopePage')}
        ];
    }
})();