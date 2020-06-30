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

        function fetchCustomDimensionsConfiguration() {
            if (fetchAllPromise) {
                return fetchAllPromise;
            }

            model.isLoading = true;

            var deferred = $q.defer();
            // .fetch does not return a proper promise
            piwikApi.fetch({method: 'CustomDimensions.getConfiguredCustomDimensions', filter_limit: '-1'}).then(function (customDimensions) {
                model.customDimensions = customDimensions;
                deferred.resolve(customDimensions);
            });

            fetchAllPromise = $q.all([deferred.promise, fetchAvailableScopes(), fetchAvailableExtractionDimensions()]).then(function () {
                model.isLoading = false;

                return model.customDimensions;
            });

            return fetchAllPromise;
        }

        function fetchAvailableExtractionDimensions() {
            var deferred = $q.defer();
            // .fetch does not return a proper promise
            piwikApi.fetch({method: 'CustomDimensions.getAvailableExtractionDimensions', filter_limit: '-1'}).then(function (availableExtractionDimensions) {

                model.extractionDimensions = [];
                angular.forEach(availableExtractionDimensions, function (value) {
                    model.extractionDimensions.push({key: value.value, value: value.name});
                });
                deferred.resolve(availableExtractionDimensions);
            });

            return deferred.promise;
        }

        function fetchAvailableScopes() {
            var deferred = $q.defer();

            // .fetch does not return a proper promise
            piwikApi.fetch({method: 'CustomDimensions.getAvailableScopes', filter_limit: '-1'}).then(function (availableScopes) {
                model.availableScopes = availableScopes;
                deferred.resolve(availableScopes);
            });

            return deferred.promise;
        }

        function findCustomDimension(customDimensionId) {
            return fetchCustomDimensionsConfiguration().then(function (customDimensions) {
                var found;
                angular.forEach(customDimensions, function (dimension) {
                    if (parseInt(dimension.idcustomdimension, 10) === customDimensionId) {
                        found = dimension;
                    }
                });

                return found;
            });
        }

        function createOrUpdateDimension(dimension, method) {
            dimension = angular.copy(dimension);
            dimension.active = dimension.active ? '1' : '0';
            dimension.method = method;
            var extractions = dimension.extractions;
            delete dimension.extractions;

            dimension.caseSensitive = dimension.case_sensitive ? '1' : '0';
            delete dimension.case_sensitive;

            model.isUpdating = true;

            return piwikApi.post(dimension, {extractions: extractions}).then(function (response) {
                model.isUpdating = false;

                return {type: 'success'};

            }, function (error) {
                model.isUpdating = false;
                return {type: 'error', message: error};
            });
        }
    }
})();