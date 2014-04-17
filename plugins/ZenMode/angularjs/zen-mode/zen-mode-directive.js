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
 * Will execute the "executeMyFunction" function in the current scope once the yes button is pressed.
 */
angular.module('piwikApp.directive').directive('piwikZenMode', function($rootElement) {

    return {
        restrict: 'A',
        compile: function (element, attrs) {
            var zenMode = attrs.piwikZenMode;
            var parent, prev, next;

            if ('visible' == zenMode) {
                element.hide();
            }

            $rootElement.bind('zen-mode', function (event, enabled) {
                if (zenMode == 'visible') {
                    enabled ? element.show() : element.hide();
                } else if (zenMode == 'hidden') {
                    enabled ? element.hide() : element.show();
                } else if (zenMode) {
                    if (enabled) {
                        parent = element.parent();
                        prev = element.prev();
                        next = element.next();

                        $rootElement.find(zenMode).prepend(element);

                    } else {

                        if (prev) {
                            prev.after(element);
                        } else if (next) {
                            next.before(element);
                        } else if (parent) {
                            parent.append(element)
                        }

                        prev = next = parent = null;
                    }
                }
            })

            return function () {

            };
        }
    };
});