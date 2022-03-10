/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').factory('customDimensionsModel', customDimensionsModel);

    customDimensionsModel.$inject = ['piwikApi', '$q'];

    function customDimensionsModel(piwikApi, $q) {
        var fetchAllPromise;

        var model = {
            customDimensions : [],
            availableScopes: [],
            extractionDimensions: [],
            isLoading: false,
            isUpdating: false,
            fetchCustomDimensionsConfiguration: fetchCustomDimensionsConfiguration,
            findCustomDimension: findCustomDimension,
            createOrUpdateDimension: createOrUpdateDimension,
            reload: reload
        };

        return model;

        function reload()
        {
            model.customDimensions = [];
            model.availableScopes = [];
            model.extractionDimensions = [];
            fetchAllPromise = null;
            fetchCustomDimensionsConfiguration();
        }


    }
})();
