/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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

        function whenRendered(scope, element, inlineHelpNode) {
            return function () {
                var field = scope.formField;

                if (inlineHelpNode) {
                    angular.element(inlineHelpNode).appendTo(element.find('.inline-help'));
                }
            }
        }

        return {
            restrict: 'A',
            scope: {
                piwikFormField: '=',
                allSettings: '='
            },
            templateUrl: 'plugins/CorePluginsAdmin/angularjs/form-field/form-field.directive.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                return function (scope, element, attrs) {
                    var field = scope.piwikFormField;
                    var defaultValue = field.defaultValue;

                    // for selects w/ a placeholder, add an option to unset the select
                    if (field.uiControl === 'select'
                        && field.uiControlAttributes.placeholder
                        && !hasOption('')
                    ) {
                        field.availableOptions.splice(0, 0, { key: '', value: '' });
                    }

                    var inlineHelpNode;
                    if (field.inlineHelp && field.inlineHelp.indexOf('#') === 0) {
                        inlineHelpNode = field.inlineHelp;
                        field.inlineHelp = ' '; // we make sure inline help will be shown
                    }

                    scope.formField = field;

                    scope.templateLoaded = function () {
                        $timeout(whenRendered(scope, element, inlineHelpNode));
                    };

                    function hasOption(key) {
                        for (var i = 0; i !== field.availableOptions.length; ++i) {
                            if (field.availableOptions[i].key === key) {
                                return true;
                            }
                        }
                        return false;
                    }
                };
            }
        };
    }
})();
