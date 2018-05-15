/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-form-condition>
 */
(function () {
    angular.module('piwikApp.directive').directive('fieldCondition', piwikFieldCondition);

    piwikFieldCondition.$inject = ['piwik', '$timeout'];

    function piwikFieldCondition(piwik, $timeout){

        function evaluate(scope, condition, element)
        {
            if (scope.$eval(condition, scope.allValues)) {
                element.show();
            } else {
                element.hide();
            }
        }

        function getValueFromElement(element)
        {
            if (element.attr('type') === 'checkbox') {
                return element.is(':checked')
            } else if (element.attr('type') === 'radio') {
                var name = element.attr('name');
                return $('.form-group [name=' + name + ']:checked').val()
            } else if (element.prop('tagName').toLowerCase() === 'select') {
                var name = element.val();
                if (name.indexOf('string:') === 0) {
                    return name.substr('string:'.length);
                }

                return name;
            }

            return element.val();
        }

        function evaluateConditionalExpression(scope, condition, element)
        {
            var fieldParts = condition.replace('!', '');
            fieldParts = fieldParts.split(' ');
            var fieldNames = [];
            fieldParts.forEach(function (name) {
                name = $.trim(name);
                if (name && name.length > 3) {
                    fieldNames.push(name);
                }
            });

            scope.allValues = {};
            angular.forEach(fieldNames, function (name) {
                var actualField = $('.form-group [name=' + name + ']').first();
                if (actualField.length) {
                    scope.allValues[name] = getValueFromElement(actualField);
                    actualField.on('change', function () {
                        scope.allValues[name] = getValueFromElement($(this));
                        evaluate(scope, condition, element);
                    })
                }
            });

            evaluate(scope, condition, element);
        }


        return {
            priority: 10, // makes sure to render after other directives, otherwise the content might be overwritten again see https://github.com/piwik/piwik/pull/8467
            restrict: 'A',
            link: function(scope, element, attrs) {

                var condition = attrs.fieldCondition;
                if (condition) {
                    $timeout(function (){
                        evaluateConditionalExpression(scope, condition, element);
                    });
                }
            },
        };
    }
})();