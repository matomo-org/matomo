/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-segment-generator>
 */
(function () {
    angular.module('piwikApp').directive('piwikSegmentGenerator', piwikSegmentGenerator);

    piwikSegmentGenerator.$inject = ['$document', 'piwik', '$filter', '$timeout'];

    function piwikSegmentGenerator($document, piwik, $filter, $timeout){
        var defaults = {
            segmentDefinition: '',
            addInitialCondition: false,
            visitSegmentsOnly: false,
            idsite: piwik.idSite
        };

        return {
            restrict: 'A',
            scope: {
                segmentDefinition: '@',
                addInitialCondition: '=',
                visitSegmentsOnly: '=',
                idsite: '='
            },
            require: "?ngModel",
            templateUrl: 'plugins/SegmentEditor/angularjs/segment-generator/segmentgenerator.directive.html?cb=' + piwik.cacheBuster,
            controller: 'SegmentGeneratorController',
            controllerAs: 'segmentGenerator',
            compile: function (element, attrs) {

                for (var index in defaults) {
                    if (attrs[index] === undefined) {
                        attrs[index] = defaults[index];
                    }
                }

                return function (scope, element, attrs, ngModel) {
                    if (ngModel) {
                        ngModel.$render = function() {
                            scope.segmentDefinition = ngModel.$viewValue;
                            if (scope.segmentDefinition) {
                                scope.segmentGenerator.setSegmentString(scope.segmentDefinition);
                            } else {
                                scope.segmentGenerator.setSegmentString('');
                            }
                        };
                    }

                    scope.$watch('segmentDefinition', function (newValue) {
                        if (ngModel) {
                            ngModel.$setViewValue(newValue);
                        }
                    });
                };
            }
        };
    }
})();