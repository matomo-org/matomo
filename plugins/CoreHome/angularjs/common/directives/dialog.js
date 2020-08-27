/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
(function () {
    angular.module('piwikApp.directive').directive('piwikDialog', piwikDialog);

    piwikDialog.$inject = ['piwik', '$parse'];

    function piwikDialog(piwik, $parse) {

        return {
            restrict: 'A',
            link: function(scope, element, attrs) {

                element.css('display', 'none');

                scope.$watch(attrs.piwikDialog, function(newValue, oldValue) {
                    if (newValue) {
                        piwik.helper.modalConfirm(element, {yes: function() {
                            if (attrs.yes) {
                                scope.$eval(attrs.yes);
                                setTimeout(function () { scope.$apply(); }, 0);
                            }
                        }, no: function() {
                                if (attrs.no) {
                                    scope.$eval(attrs.no);
                                    setTimeout(function () { scope.$apply(); }, 0);
                                }
                            }
                        }, {
                            onCloseEnd: function () {
                                setTimeout(function () {
                                    scope.$apply($parse(attrs.piwikDialog).assign(scope, false));
                                }, 0);
                            }
                        });
                    } else if (newValue === false && oldValue === true) {
                        // The user closed the dialog, e.g. by pressing Esc or clicking away from it
                        if (attrs.close) {
                            scope.$eval(attrs.close);
                            setTimeout(function () { scope.$apply(); }, 0);
                        }
                    }
                });
            }
        };
    }
})();