/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-field>
 *
 *     eg <div piwik-field ui-control="select"
 * title="{{ 'SitesManager_Timezone'|translate }}"
 * value="site.timezone"
 * options="timezones"
 * inline-help="test"
 * description=""
 * introduction=""
 * name=""
 * placeholder=""
 * rows="3"
 * autocomplete="off"
 * disabled="true"
 * full-width="true"
 * templateFile=""></div>
 *
 * templateFile allows to render a custom template
 * We do not use type= attribute here as it would match some CSS from input type=radio etc
 */
(function () {
    angular.module('piwikApp').directive('piwikField', piwikField);

    piwikField.$inject = ['piwik', '$compile'];

    function piwikField(piwik, $compile){

        return {
            restrict: 'A',
            require: '?ngModel',
            scope: {
                uicontrol: '@',
                name: '@',
                value: '@',
                default: '@',
                options: '=',
                description: '@',
                introduction: '@',
                title: '@',
                inlineHelp: '@',
                disabled: '=',
                autocomplete: '@',
                condition: '@',
                varType: '@',
                autofocus: '@',
                tabindex: '@',
                templateFile: '@',
                fullWidth: '@',
                maxlength: '@',
                required: '@',
                placeholder: '@',
                rows: '@',
                min: '@',
                max: '@'
            },
            template: '<div piwik-form-field="field"></div>',
            link: function(scope, elm, attrs, ctrl) {
                if (!ctrl) {
                    return;
                }

                // load init value
                if (scope.field.value !== undefined && scope.field.value !== null) {
                    ctrl.$setViewValue(scope.field.value);
                } else if (ctrl.$viewValue) {
                    scope.field.value = ctrl.$viewValue;
                }

                // view -> model
                scope.$watch('field.value', function (val, oldVal) {
                    if (val !== oldVal && val !== ctrl.$viewValue) {
                        ctrl.$setViewValue(val);
                    }
                });

                // model -> view
                ctrl.$render = function() {
                    scope.field.value = ctrl.$viewValue;
                };

            },
            controller: function ($scope) {
                var field = {};
                field.uiControl = $scope.uicontrol;
                if ($scope.varType) {
                    field.type = $scope.varType;
                } else if (field.uiControl === 'multiselect') {
                    field.type = 'array';
                } else if (field.uiControl === 'checkbox') {
                    field.type = 'boolean';
                } else if (field.uiControl === 'site') {
                    field.type = 'object';
                } else if (field.uiControl === 'number') {
                    field.type = 'integer';
                } else {
                    field.type = 'string';
                }

                field.name = $scope.name;
                field.value = $scope.value;
                field.defaultValue = $scope.default;
                field.availableValues = $scope.options;
                field.description = $scope.description;
                field.introduction = $scope.introduction;
                field.inlineHelp = $scope.inlineHelp;
                field.templateFile = $scope.templateFile;
                field.title = $scope.title;
                field.uiControlAttributes = {};
                field.fullWidth = !!$scope.fullWidth;

                if (field.type === 'array' && angular.isString(field.value) && field.value) {
                    field.value = JSON.parse(field.value);
                }

                var i = 0, attribute;
                var attributes = ['disabled', 'autocomplete', 'tabindex', 'autofocus', 'rows', 'required', 'maxlength', 'placeholder', 'min', 'max'];
                for (i; i < attributes.length; i++) {
                    attribute = attributes[i];
                    if (!!$scope[attribute]) {
                        field.uiControlAttributes[attribute] = $scope[attribute];
                    }
                }

                $scope.field = field;

                $scope.$watch('options', function (val, oldVal) {
                    if (val !== oldVal) {
                        $scope.field.availableValues = val;
                    }
                });

                $scope.$watch('title', function (val, oldVal) {
                    if (val !== oldVal) {
                        $scope.field.title = val;
                    }
                });

                if ('undefined' !== typeof $scope.placeholder && $scope.placeholder !== null) {
                    $scope.$watch('placeholder', function (val, oldVal) {
                        if (val !== oldVal) {
                            $scope.field.uiControlAttributes.placeholder = val;
                        }
                    });
                }

                $scope.$watch('disabled', function (val, oldVal) {
                    if (val !== oldVal) {
                        $scope.field.uiControlAttributes.disabled = val;
                    }
                });
            }
        };
    }
})();