/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp.directive').directive('piwikFocusAnywhereButHere', function($document){
    return {
        restrict: 'A',
        link: function(scope, element, attr, ctrl) {

            function onClickOutsideElement (event) {
                if (element.has(event.target).length === 0) {
                    scope.$apply(attr.piwikFocusAnywhereButHere);
                }
            }

            function onEscapeHandler (event) {
                if (event.which === 27) {
                    scope.$apply(attr.piwikFocusAnywhereButHere);
                }
            }

            $document.on('keyup', onEscapeHandler);
            $document.on('mouseup', onClickOutsideElement);
            scope.$on('$destroy', function() {
                $document.off('mouseup', onClickOutsideElement);
                $document.off('keyup', onEscapeHandler);
            });
        }
    }
});
