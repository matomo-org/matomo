/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div matomo-multi-pair-field field1=".." field2="" ng-model="...">
 */
(function () {
    angular.module('piwikApp').directive('matomoMultiPairField', matomoMultiPairField);

    matomoMultiPairField.$inject = ['$document', 'piwik', '$filter'];

    function matomoMultiPairField($document, piwik, $filter){
        return {
            restrict: 'A',
            scope: {
                name: '=',
                field1: '=',
                field2: '=',
                field3: '=',
                field4: '='
            },
            require: "?ngModel",
            templateUrl: 'plugins/CoreHome/angularjs/multipairfield/multipairfield.directive.html?cb=' + piwik.cacheBuster,
            controller: 'MultiPairFieldController',
            controllerAs: 'multiPairField',
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