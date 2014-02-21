/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Allows you to define any expression to be executed in case the user presses enter
 *
 * Example
 * <div piwik-enter="save()">
 * <div piwik-enter="showList=false">
 */
angular.module('piwikApp.directive').directive('piwikEnter', function() {
    return function(scope, element, attrs) {
        element.bind("keydown keypress", function(event) {
            if(event.which === 13) {
                scope.$apply(function(){
                    scope.$eval(attrs.piwikEnter, {'event': event});
                });

                event.preventDefault();
            }
        });
    };
});