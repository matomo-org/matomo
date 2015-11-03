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
            fetchUsages: fetchUsages,
            hasCustomVariablesInGeneral: false,
            hasAtLeastOneUsage: false,
            numSlotsAvailable: 5,
        };

        return model;

        function fetchCustomVariables() {
            return piwikApi.fetch({method: 'CustomVariables.getCustomVariables', period: 'year', date: 'today', filter_limit: 1})
                .then(function (customVariables) {
                   model.hasCustomVariablesInGeneral = (customVariables && customVariables.length > 0);
                });
        }

        function fetchUsages() {

            model.isLoading = true;

            fetchCustomVariables().then(function () {
                return piwikApi.fetch({method: 'CustomVariables.getUsagesOfSlots'});

            }).then(function (customVariables) {
                model.customVariables = customVariables;

                angular.forEach(customVariables, function (customVar) {
                    if (customVar.index > model.numSlotsAvailable) {
                        model.numSlotsAvailable = customVar.index;
                    }

                    if (customVar.usages && customVar.usages.length > 0) {
                        model.hasAtLeastOneUsage = true;
                    }
                });

            })['finally'](function () {    // .finally() is not IE8 compatible see https://github.com/angular/angular.js/commit/f078762d48d0d5d9796dcdf2cb0241198677582c
                model.isLoading = false;
            });
        }

    }
})();