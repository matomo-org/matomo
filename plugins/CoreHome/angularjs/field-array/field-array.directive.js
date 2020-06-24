/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div matomo-field-array field=".." ng-model="...">
 */
(function () {
    angular.module('piwikApp').directive('matomoFieldArray', matomoFieldArray);

    matomoFieldArray.$inject = ['$document', 'piwik', '$filter'];

    function matomoFieldArray($document, piwik, $filter){
        return {
            restrict: 'A',
            scope: {
                name: '=',
                field: '='
            },
            require: "?ngModel",
            templateUrl: 'plugins/CoreHome/angularjs/field-array/field-array.directive.html?cb=' + piwik.cacheBuster,
            controller: 'FieldArrayController',
            controllerAs: 'fieldArray',
            compile: function (element, attrs) {

                return function (scope, element, attrs, ngModel) {

                    if (ngModel) {
                        ngModel.$setViewValue(scope.formValue);
                    }

                    scope.$watch('formValue', function (newValue, oldValue) {
                        if (newValue != oldValue) {
                            element.trigger('change', newValue);
                        }
                    }, true);

                    if (ngModel) {
                        ngModel.$render = function() {
                            if (angular.isString(ngModel.$viewValue)) {
                                scope.formValue = JSON.parse(ngModel.$viewValue);
                            } else {
                                scope.formValue = ngModel.$viewValue;
                            }
                        };
                    }

                    scope.$watch('formValue', function (newValue, oldValue) {
                        if (ngModel) {
                            ngModel.$setViewValue(newValue);
                        }
                    }, true);
                };
            }
        };
    }
})();