/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * The given expression will be executed when the user presses either escape or presses something outside
 * of this element
 *
 * Example:
 * <div piwik-focus-anywhere-but-here="closeDialog()">my dialog</div>
 */
(function () {
    angular.module('piwikApp.directive').directive('piwikFocusAnywhereButHere', piwikFocusAnywhereButHere);

    piwikFocusAnywhereButHere.$inject = ['$document'];

    function piwikFocusAnywhereButHere($document){
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
        };
    }
})();
