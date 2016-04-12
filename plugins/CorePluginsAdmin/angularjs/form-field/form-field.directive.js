/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-form-field="{...}">
 */
(function () {
    angular.module('piwikApp').directive('piwikFormField', piwikFormField);

    piwikFormField.$inject = ['piwik'];

    function piwikFormField(piwik){

        return {
            restrict: 'A',
            scope: {
                piwikFormField: '=',
                allSettings: '='
            },
            templateUrl: 'plugins/CorePluginsAdmin/angularjs/form-field/form-field.directive.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                function evaluateConditionalExpression(scope, field)
                {
                    if (!field.condition) {
                        return;
                    }

                    var values = {};
                    angular.forEach(scope.allSettings, function (setting) {
                        if (setting.value === '0') {
                            values[setting.name] = 0;
                        } else {
                            values[setting.name] = setting.value;
                        }
                    });

                    field.showField = scope.$eval(field.condition, values);
                }

                function hasUiControl(field, uiControlType)
                {
                    return field.uiControl === uiControlType;
                }

                function isSelectControl(field)
                {
                    return hasUiControl(field, 'select') || hasUiControl(field, 'multiselect');
                }

                function hasGroupedValues(availableValues)
                {
                    if (!angular.isObject(availableValues)
                        || angular.isArray(availableValues)) {
                        return false;
                    }

                    var key;
                    for (key in availableValues) {
                        if (Object.prototype.hasOwnProperty.call(availableValues, key)) {
                            if (angular.isObject(availableValues[key])) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    }

                    return false;
                }

                return function (scope, element, attrs) {
                    var field = scope.piwikFormField;
                    
                    if (angular.isArray(field.defaultValue)) {
                        field.defaultValue = field.defaultValue.join(',');
                    }

                    if (isSelectControl(field) && field.availableValues) {
                        var availableValues = field.availableValues;

                        if (!hasGroupedValues(availableValues)) {
                            availableValues = {'': availableValues};
                        }

                        var flatValues = [];
                        angular.forEach(availableValues, function (values, group) {
                            angular.forEach(values, function (value, key) {

                                if (field.type === 'integer' && angular.isString(key)) {
                                    key = parseInt(key, 10);
                                }

                                flatValues.push({group: group, key: key, value: value});
                            });
                        });

                        field.availableValues = flatValues;
                    }

                    field.showField = true;

                    if (field.condition && scope.allSettings) {
                        evaluateConditionalExpression(scope, field);

                        for (var key in scope.allSettings) {
                            if(scope.allSettings.hasOwnProperty(key)) {
                                scope.$watchCollection('allSettings[' + key + '].value', function (val, oldVal) {
                                    if (val !== oldVal) {
                                        evaluateConditionalExpression(scope, field);
                                    }
                                });
                            }
                        }

                    }

                    scope.formField = field;
                };
            }
        };
    }
})();