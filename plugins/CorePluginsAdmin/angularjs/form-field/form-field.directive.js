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

    piwikFormField.$inject = ['piwik', '$timeout'];

    function piwikFormField(piwik, $timeout){

        function syncMultiCheckboxKeysWithFieldValue(field)
        {
            angular.forEach(field.availableOptions, function (option, index) {
                if (option && field.value.indexOf(option.key) !== -1) {
                    field.checkboxkeys[index] = true;
                } else {
                    field.checkboxkeys[index] = false;
                }
            });
        }

        function hasUiControl(field, uiControlType)
        {
            return field.uiControl === uiControlType;
        }

        function isSelectControl(field)
        {
            return hasUiControl(field, 'select') || hasUiControl(field, 'multiselect');
        }

        function isArrayCheckboxControl(field)
        {
            return field.type === 'array' && hasUiControl(field, 'checkbox');
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

        function whenRendered(scope, element, inlineHelpNode) {
            return function () {
                var field = scope.formField;

                if (inlineHelpNode) {
                    angular.element(inlineHelpNode).appendTo(element.find('.inline-help'));
                }

                if (isSelectControl(field)) {
                    var $select = element.find('select');
                    $select.material_select();

                    scope.$watch('formField.value', function (val, oldVal) {
                        if (val !== oldVal) {
                            $timeout(function () {
                                $select.material_select();
                            });
                        }
                    });

                    scope.$watch('formField.uiControlAttributes.disabled', function (val, oldVal) {
                        if (val !== oldVal) {
                            $timeout(function () {
                                $select.material_select();
                            });
                        }
                    });

                } else if (hasUiControl(field, 'textarea')) {
                    element.find('textarea').trigger('autoresize');
                    scope.$watch('formField.value', function (val, oldVal) {
                        if (val !== oldVal) {
                            $timeout(function () {
                                element.find('textarea').trigger('autoresize');
                            });
                        }
                    });

                } else if (hasUiControl(field, 'file')) {

                    // angular doesn't support file input type with ngModel. We implement our own "two way binding"
                    var $file = element.find('[type=file]');

                    $file.on('change', function () {
                        scope.formField.value = $(this).val();
                    });

                    scope.$watch('formField.value', function (val, oldVal) {
                        if (val !== oldVal && val === '') {
                            $file.val('');
                        }
                    });

                } else if (isArrayCheckboxControl(field)) {

                    Materialize.updateTextFields();

                    scope.$watch('formField.value', function (val, oldVal) {
                        if (val !== oldVal && val && !oldVal && angular.isArray(val)) {
                            // we do this only on initial check
                            syncMultiCheckboxKeysWithFieldValue(field);
                        }
                    });


                } else if (hasUiControl(field, 'text')
                        || hasUiControl(field, 'textarea')
                        || hasUiControl(field, 'password')
                        || hasUiControl(field, 'email')
                        || hasUiControl(field, 'number')
                        || hasUiControl(field, 'url')
                        || hasUiControl(field, 'search')) {
                    Materialize.updateTextFields();
                    scope.$watch('formField.value', function (val, oldVal) {
                        if (val !== oldVal) {
                            $timeout(function () {
                                Materialize.updateTextFields();
                            });
                        }
                    });
                }
            }
        }

        function getTemplate(field) {
            var control = field.uiControl;
            if (control === 'password' || control === 'url' || control === 'search' || control === 'email') {
                control = 'text'; // we use same template for text and password both
            }

            var file = 'field-' + control;
            var fieldsSupportingArrays = ['textarea', 'checkbox', 'text'];
            if (field.type === 'array' && fieldsSupportingArrays.indexOf(control) !== -1) {
                file += '-array';
            }

            return 'plugins/CorePluginsAdmin/angularjs/form-field/' + file + '.html?cb=' + piwik.cacheBuster;
        };

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

                function formatAvailableValues(field)
                {
                    if (!field.availableValues) {
                        return;
                    }

                    var flatValues = [];

                    if (hasUiControl(field, 'radio') || hasUiControl(field, 'checkbox')) {
                        angular.forEach(field.availableValues, function (value, key) {

                            if (angular.isObject(value) && typeof value.key !== 'undefined'){
                                flatValues.push(value);
                                return;
                            }

                            if (field.type === 'integer' && angular.isString(key)) {
                                key = parseInt(key, 10);
                            }

                            flatValues.push({key: key, value: value});
                        });

                        return flatValues;
                    }

                    if (hasUiControl(field, 'expandable-select')) {
                        var availableValues = field.availableValues;
                        var flatValues = [];

                        var groups = {};
                        angular.forEach(availableValues, function (value) {

                            if (!value.group) {
                                value.group = '';
                            }

                            if (!(value.group in groups) || !groups[value.group]) {
                                groups[value.group] = {values: [], group: value.group}
                            }

                            var formatted = {key: value.key, value: value.value};

                            if ('tooltip' in value && value.tooltip) {
                                formatted.tooltip = value.tooltip;
                            }

                            groups[value.group].values.push(formatted);
                        });

                        angular.forEach(groups, function (group) {
                            if (group.values.length) {
                                flatValues.push(group);
                            }
                        });

                        return flatValues;
                    }

                    if (isSelectControl(field)) {
                        var availableValues = field.availableValues;

                        if (!hasGroupedValues(availableValues)) {
                            availableValues = {'': availableValues};
                        }

                        var flatValues = [];
                        angular.forEach(availableValues, function (values, group) {
                            angular.forEach(values, function (value, key) {

                                if (angular.isObject(value) && typeof value.key !== 'undefined'){
                                    flatValues.push(value);
                                    return;
                                }

                                if (field.type === 'integer' && angular.isString(key)) {
                                    key = parseInt(key, 10);
                                }

                                flatValues.push({group: group, key: key, value: value});
                            });
                        });

                        return flatValues;
                    }

                    return field.availableValues;
                }

                function formatPrettyDefaultValue(defaultValue, availableOptions) {

                    if (!angular.isArray(availableOptions)) {
                        return defaultValue;
                    }

                    var prettyValues = [];

                    if (!angular.isArray(defaultValue)) {
                        defaultValue = [defaultValue];
                    }

                    angular.forEach(availableOptions, function (value, key) {
                        if (defaultValue.indexOf(value.key) !== -1) {
                            prettyValues.push(value.value);
                        }
                    });

                    return prettyValues.join(', ');
                }

                return function (scope, element, attrs) {
                    var field = scope.piwikFormField;
                    var defaultValue = field.defaultValue;


                    if (angular.isArray(field.defaultValue)) {
                        field.defaultValue = defaultValue.join(',');
                    }

                    // convert boolean values since angular 1.6 uses strict equals when determining if a model value
                    // matches the ng-value of an input.
                    if (field.type === 'boolean') {
                        var valueIsTruthy = field.value && field.value > 0 && field.value !== '0';

                        // for checkboxes, the value MUST be either true or faluse
                        if (field.uiControl === 'checkbox') {
                            field.value = valueIsTruthy;
                        } else if (field.uiControl === 'radio') {
                            field.value = valueIsTruthy ? '1' : '0';
                        }
                    }

                    // we are setting availableOptions and not availableValues again. Otherwise when watching the scope
                    // availableValues and in the watch change availableValues could trigger lots of more watch events
                    field.availableOptions = formatAvailableValues(field);

                    field.defaultValuePretty = formatPrettyDefaultValue(defaultValue, field.availableOptions);

                    field.showField = true;

                    var inlineHelpNode;
                    if (field.inlineHelp && field.inlineHelp.indexOf('#') === 0) {
                        inlineHelpNode = field.inlineHelp;
                        field.inlineHelp = ' '; // we make sure inline help will be shown
                    }

                    if (isArrayCheckboxControl(field)) {
                        field.updateCheckboxArrayValue = function () {
                            var values = [];
                            for (var x in field.checkboxkeys) {
                                if (field.checkboxkeys[x]) {
                                    values.push(field.availableOptions[x].key);
                                }
                            }
                            field.value = values;
                        }
                        field.checkboxkeys = new Array(field.availableOptions.length);

                        if (field.value && angular.isArray(field.value)) {
                            syncMultiCheckboxKeysWithFieldValue(field);
                        }
                    }

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

                    if (!field.templateFile) {
                        field.templateFile = getTemplate(field);
                    }

                    scope.formField = field;

                    scope.$watch('formField.availableValues', function (val, oldVal) {
                        if (val !== oldVal) {
                            scope.formField.availableOptions = formatAvailableValues(scope.formField);

                            if (isSelectControl(scope.formField)) {
                                $timeout(function () {
                                    element.find('select').material_select();
                                });
                            }
                        }
                    });
                    scope.templateLoaded = function () {
                        $timeout(whenRendered(scope, element, inlineHelpNode));
                    };

                };
            }
        };
    }
})();