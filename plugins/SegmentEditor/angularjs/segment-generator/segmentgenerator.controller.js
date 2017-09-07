/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('SegmentGeneratorController', SegmentGeneratorController);

    SegmentGeneratorController.$inject = ['$scope', 'piwik', 'piwikApi', 'segmentGeneratorModel', '$filter', '$timeout'];

    var findAndExplodeByMatch = function(metric){
        var matches = ["==" , "!=" , "<=", ">=", "=@" , "!@","<",">", "=^", "=$"];
        var newMetric = {};
        var minPos = metric.length;
        var match, index;
        var singleChar = false;

        for (var key=0; key < matches.length; key++) {
            match = matches[key];
            index = metric.indexOf(match);
            if(index !== -1){
                if(index < minPos){
                    minPos = index;
                    if(match.length === 1){
                        singleChar = true;
                    }
                }
            }
        }

        if (minPos < metric.length) {
            // sth found - explode
            if(singleChar == true){
                newMetric.segment = metric.substr(0,minPos);
                newMetric.matches = metric.substr(minPos,1);
                newMetric.value = metric.substr(minPos+1);
            } else {
                newMetric.segment = metric.substr(0,minPos);
                newMetric.matches = metric.substr(minPos,2);
                newMetric.value = metric.substr(minPos+2);
            }
            // if value is only "" -> change to empty string
            if(newMetric.value === '""')
            {
                newMetric.value = "";
            }
        }

        newMetric.value = decodeURIComponent(newMetric.value);
        return newMetric;
    };

    function generateUniqueId() {
        var id = '';
        var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        for (var i = 1; i <= 10; i++) {
            id += chars.charAt(Math.floor(Math.random() * chars.length));
        }

        return id;
    }

    function stripTags(text) {
        if (text) {
            text = ('' + text).replace(/(<([^>]+)>)/ig,"");
        }
        return text;
    }

    function SegmentGeneratorController($scope, piwik, piwikApi, segmentGeneratorModel, $filter, $timeout) {
        var translate = $filter('translate');

        var self = this;
        var firstSegment = '';
        var firstMatch = '';
        this.conditions = [];
        this.model = segmentGeneratorModel;

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

        this.andConditionLabel = '';

        this.addNewAndCondition = function () {
            var condition = {orConditions: []};

            this.addAndCondition(condition);
            this.addNewOrCondition(condition);

            return condition;
        };

        this.addAndCondition = function (condition) {
            this.andConditionLabel = translate('SegmentEditor_OperatorAND');
            this.conditions.push(condition);
            this.updateSegmentDefinition();
        }

        this.addNewOrCondition = function (condition) {
            var orCondition = {
                segment: firstSegment,
                matches: firstMatch,
                value: ''
            };

            this.addOrCondition(condition, orCondition);
        };

        this.addOrCondition = function (condition, orCondition) {
            orCondition.isLoading = false;
            orCondition.id = generateUniqueId();

            condition.orConditions.push(orCondition);
            this.updateSegmentDefinition();

            $timeout(function () {
                self.updateAutocomplete(orCondition);
            });
        };

        this.updateAutocomplete = function (orCondition) {
            orCondition.isLoading = true;

            this.updateSegmentDefinition();

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
                            self.updateSegmentDefinition();
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
                if (self.conditions.length === 0) {
                    self.andConditionLabel = '';
                }
            }

            this.updateSegmentDefinition();
        };

        this.getSegmentString = function () {
            var segmentStr = '';

            angular.forEach(this.conditions, function (conditions) {
                var subSegmentStr = '';

                angular.forEach(conditions.orConditions, function (orCondition){
                    if (subSegmentStr !== ''){
                        subSegmentStr += ","; // OR operator
                    }

                    subSegmentStr += orCondition.segment + orCondition.matches +  encodeURIComponent(orCondition.value);
                })

                if (segmentStr !== '') {
                    segmentStr += ";"; // add AND operator between segment blocks
                }

                segmentStr += subSegmentStr;
            });

            return segmentStr
        };

        this.setSegmentString = function (segmentStr) {
            var orCondition, condition;

            this.conditions = [];
            
            if (!segmentStr) {
                return;
            }

            var blocks = segmentStr.split(';');

            for (var key = 0; key < blocks.length; key++) {
                var condition = {orConditions: []};
                this.addAndCondition(condition);

                blocks[key] = blocks[key].split(',');
                for (var innerkey = 0; innerkey < blocks[key].length; innerkey++) {
                    orCondition = findAndExplodeByMatch(blocks[key][innerkey]);
                    this.addOrCondition(condition, orCondition);
                }
            }
        };

        this.updateSegmentDefinition = function () {
            $scope.segmentDefinition = this.getSegmentString();
        }

        if ($scope.segmentDefinition) {
            this.setSegmentString($scope.segmentDefinition);
        }

        $scope.$watch('idsite', function (newValue, oldValue) {
            if (newValue != oldValue) {
                reloadSegments(newValue);
            }
        });

        reloadSegments($scope.idsite);

        function reloadSegments(idsite) {
            segmentGeneratorModel.loadSegments(idsite).then(function (segments) {

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

                    var segmentData = {group: segment.category, key: segment.segment, value: segment.name};
                    if ('acceptedValues' in segment && segment.acceptedValues) {
                        segmentData.tooltip = stripTags(segment.acceptedValues);
                    }
                    self.segmentList.push(segmentData);
                });

                if ($scope.addInitialCondition && self.conditions.length === 0) {
                    self.addNewAndCondition();
                }
            });
        }
    }

})();
