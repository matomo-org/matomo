/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('SegmentGeneratorController', SegmentGeneratorController);

    SegmentGeneratorController.$inject = ['$scope', 'piwik', 'piwikApi', 'segmentGeneratorModel', '$filter', '$timeout'];

    function SegmentGeneratorController($scope, piwik, piwikApi, segmentGeneratorModel, $filter, $timeout) {
        var translate = $filter('translate');

        var self = this;
        var firstSegment = '';
        var firstMatch = '';
        this.conditions = [];

        function generateUniqueId() {
            var id = '';
            var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

            for (var i = 1; i <= 10; i++) {
                id += chars.charAt(Math.floor(Math.random() * chars.length));
            }

            return id;
        }

        this.segments = {};

        this.matches = {
            metric: [
                {key: '==', value: translate('General_OperationEquals')},
                {key: '!=', value: translate('General_OperationNotEquals')},
                {key: '<=', value: translate('General_OperationAtMost')},
                {key: '>=', value: translate('General_OperationAtLeast')},
                {key: '<',  value: translate('General_OperationLessThan')},
                {key: '>',  value: translate('General_OperationGreaterThan')}
            ],
            dimension: [
                {key: '==', value: translate('General_OperationIs')},
                {key: '!=', value: translate('General_OperationIsNot')},
                {key: '=@', value: translate('General_OperationContains')},
                {key: '!@', value: translate('General_OperationDoesNotContain')},
                {key: '=^', value: translate('General_OperationStartsWith')},
                {key: '=$', value: translate('General_OperationEndsWith')}
            ],
        };
        this.matches[''] = this.matches.dimension;

        this.addAndCondition = function () {
            var condition = {orConditions: []};
            this.addOrCondition(condition);

            this.conditions.push(condition);
        };

        this.addOrCondition = function (condition) {
            var orCondition = {
                id: generateUniqueId(),
                segment: firstSegment,
                matches: firstMatch,
                value: '',
                isLoading: false
            };

            condition.orConditions.push(orCondition);

            $timeout(function () {
                self.updateAutocomplete(orCondition);
            });
        };

        this.updateAutocomplete = function (orCondition) {
            orCondition.isLoading = true;

            var resolved = false;

            var promise = piwikApi.fetch({
                module: 'API',
                format: 'json',
                method: 'API.getSuggestedValuesForSegment',
                segmentName: orCondition.segment
            }, {createErrorNotification: false})

            promise.then(function(response) {
                orCondition.isLoading = false;
                resolved = true;

                var inputElement = $('.orCondId' + orCondition.id + " .metricValueBlock input");

                if (response && response.result != 'error') {

                    inputElement.autocomplete({
                        source: response,
                        minLength: 0,
                        select: function(event, ui){
                            event.preventDefault();
                            orCondition.value = ui.item.value;
                            $timeout(function () {
                                $scope.$apply();
                            });
                        }
                    });
                }

                inputElement.off('click');
                inputElement.click(function (e) {
                    $(inputElement).autocomplete('search', orCondition.value);
                });
            }, function(response) {
                resolved = true;
                orCondition.isLoading = false;

                var inputElement = $('.orCondId' + orCondition.id + " .metricValueBlock input");
                inputElement.autocomplete({
                    source: [],
                    minLength: 0
                });
                $(inputElement).autocomplete('search', orCondition.value);
            });

            $timeout(function () {
                if (!resolved) {
                    promise.abort();
                }
            }, 20000);
        }

        this.removeOrCondition = function (condition, orCondition) {
            var index = condition.orConditions.indexOf(orCondition);
            if (index > -1) {
                condition.orConditions.splice(index, 1);
            }

            if (condition.orConditions.length === 0) {
                var index = self.conditions.indexOf(condition);
                if (index > -1) {
                    self.conditions.splice(index, 1);
                }
            }
        };

        segmentGeneratorModel.loadSegments().then(function (segments) {

            self.segmentList = [];

            var groups = {};
            angular.forEach(segments, function (segment) {
                if (!segment.category) {
                    segment.category = 'Others';
                }

                if (!firstSegment) {
                    firstSegment = segment.segment;
                    if (segment.type && self.matches[segment.type]) {
                        firstMatch = self.matches[segment.type][0].key;
                    } else {
                        firstMatch = self.matches[''][0].key
                    }
                }

                self.segments[segment.segment] = segment;
                self.segmentList.push({group: segment.category, key: segment.segment, value: segment.name});
            });

            if ($scope.addInitialCondition) {
                self.addAndCondition();
            }
        });
    }

})();
