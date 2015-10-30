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
        this.createCustomVariableSlot = function () {
            var highestIndex = 5;
            angular.forEach(manageCustomVarsModel.customVariables, function (customVar) {
                if (customVar.index > highestIndex) {
                    highestIndex = customVar.index;
                }
            });

            var translate = $filter('translate');

            var command = './console customvariables:set-max-custom-variables ' + (highestIndex + 1);
            var text = translate('CustomVariables_CreatingCustomVariableTakesTime');
            text += '<br /><br />' + translate('CustomVariables_CurrentAvailableCustomVariables', '<strong>' + highestIndex + '</strong>');
            text += '<br /><br />' + translate('CustomVariables_ToCreateCustomVarExecute');
            text += '<br /><br /><code>' + command + '</code>';

            piwik.helper.modalConfirm('<div class="ui-confirm" title="' + translate('CustomVariables_CreateNewSlot') + '">' + text + '<br /><br /></div>');
        }
    }
})();