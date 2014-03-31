/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-dialog="showDialog">...</div>
 * Will show dialog once showDialog evaluates to true.
 *
 * <div piwik-dialog="showDialog" yes="executeMyFunction();">
 * ... <input type="button" role="yes" value="button">
 * </div>
 * Will execute the "executeMyFunction" function in the current scope once the yes button is pressed.
 */
angular.module('piwikApp.directive').directive('piwikDialog', function(piwik) {

    return {
        restrict: 'A',
        link: function(scope, element, attrs) {

            element.css('display', 'none');

            element.on( "dialogclose", function() {
                scope.$eval(attrs.piwikDialog+'=false');
            });

            scope.$watch(attrs.piwikDialog, function(newValue, oldValue) {
                if (newValue) {
                    piwik.helper.modalConfirm(element, {yes: function() {
                        if (attrs.yes) {
                            scope.$eval(attrs.yes);
                        }
                    }});
                }
            });
        }
    };
});