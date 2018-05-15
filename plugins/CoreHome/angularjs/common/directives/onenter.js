/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Allows you to define any expression to be executed in case the user presses enter
 *
 * Example
 * <div piwik-onenter="save()">
 * <div piwik-onenter="showList=false">
 */
(function () {
    angular.module('piwikApp.directive').directive('piwikOnenter', piwikOnenter);

    function piwikOnenter() {
        return function(scope, element, attrs) {
            element.bind("keydown keypress", function(event) {
                if(event.which === 13) {
                    scope.$apply(function(){
                        scope.$eval(attrs.piwikOnenter, {'event': event});
                    });

                    event.preventDefault();
                }
            });
        };
    }
})();