/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').factory('manageCustomVarsModel', manageCustomVarsModel);

    manageCustomVarsModel.$inject = ['piwikApi'];

    function manageCustomVarsModel(piwikApi) {

        var model = {
            customVariables : [],
            extractions : [],
            isLoading: false,
            fetchUsages: fetchUsages
        };

        return model;

        function fetchUsages() {

            model.isLoading = true;

            piwikApi.fetch({method: 'CustomVariables.getUsagesOfSlots'})
                .then(function (customVariables) {
                    model.customVariables = customVariables;
                })['finally'](function () {    // .finally() is not IE8 compatible see https://github.com/angular/angular.js/commit/f078762d48d0d5d9796dcdf2cb0241198677582c
                model.isLoading = false;
            });
        }

    }
})();