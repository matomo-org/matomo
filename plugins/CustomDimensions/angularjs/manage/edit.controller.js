/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('CustomDimensionsEditController', CustomDimensionsEditController);

    CustomDimensionsEditController.$inject = ['$scope', 'customDimensionsModel', 'piwik', '$location', '$filter'];

    function CustomDimensionsEditController($scope, customDimensionsModel, piwik, $location, $filter) {

        var self = this;
        var currentId = null;
        var notificationId = 'customdimensions';

        var translate = $filter('translate');

        this.model = customDimensionsModel;

        function getNotification()
        {
            var UI = require('piwik/UI');
            return new UI.Notification();
        }

        function removeAnyCustomDimensionNotification()
        {
            getNotification().remove(notificationId);
        }

        function showNotification(message, context)
        {
            var notification = getNotification();
            notification.show(message, {context: context, id: notificationId});
        }

        function init(dimensionId)
        {
            self.create = dimensionId == '0';
            self.edit   = !(dimensionId == '0');

            if (dimensionId !== null) {
                removeAnyCustomDimensionNotification();
            }

            self.model.fetchCustomDimensionsConfiguration().then(function () {

                if (self.edit && dimensionId) {
                    self.model.findCustomDimension(dimensionId).then(function (dimension) {
                        self.dimension = dimension;
                        if (dimension && !dimension.extractions.length) {
                            self.addExtraction();
                        }
                    });
                } else if (self.create) {
                    self.dimension = {
                        idSite: piwik.idSite,
                        name: '',
                        active: false,
                        extractions: [],
                        scope: $scope.dimensionScope,
                        case_sensitive: true,
                    };
                    self.addExtraction();
                }
            });
        }

        this.removeExtraction = function(index)
        {
            if (index > -1) {
                this.dimension.extractions.splice(index, 1);
            }
        };

        this.addExtraction = function()
        {
            if (this.doesScopeSupportExtraction()) {
                this.dimension.extractions.push({dimension: 'url', pattern: ''});
            }
        };

        this.doesScopeSupportExtraction = function () {
            if (!this.dimension || !this.dimension.scope || !this.model.availableScopes) {
                return false;
            }

            var index, scope;
            for (index in this.model.availableScopes) {
                scope = this.model.availableScopes[index];
                if (scope && scope.value === this.dimension.scope) {
                    return scope.supportsExtractions;
                }
            }

            return false;
        };

        this.createCustomDimension = function () {
            var method = 'CustomDimensions.configureNewCustomDimension';

            this.isUpdating = true;

            customDimensionsModel.createOrUpdateDimension(this.dimension, method).then(function (response) {
                if (response.type === 'error') {
                    return;
                }

                showNotification(translate('CustomDimensions_DimensionCreated'), response.type);
                self.model.reload();
                $location.url('/list');
            });
        };

        this.updateCustomDimension = function () {
            this.dimension.idDimension = this.dimension.idcustomdimension;

            var method = 'CustomDimensions.configureExistingCustomDimension';

            this.isUpdating = true;

            customDimensionsModel.createOrUpdateDimension(this.dimension, method).then(function (response) {
                if (response.type === 'error') {
                    return;
                }

                showNotification(translate('CustomDimensions_DimensionUpdated'), response.type);
                $location.url('/list');
            });
        };

        $scope.$watch('dimensionId', function (newValue, oldValue) {
            if (newValue != oldValue || currentId === null) {
                currentId = newValue;
                init(newValue);
            }
        });
    }
})();