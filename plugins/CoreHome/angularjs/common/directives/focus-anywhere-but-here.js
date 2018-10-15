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

                var ignoreOutsideElement = false;
                var scrollTimeout = false;

                function onClickOutsideElement (event) {
                    if (ignoreOutsideElement) {
                        return; // used scroll bar just before
                    }

                    if (element.has(event.target).length === 0) {
                        setTimeout(function () {
                            scope.$apply(attr.piwikFocusAnywhereButHere);
                        }, 0);
                    }
                }

                function onScroll (event) {
                    // see https://github.com/matomo-org/matomo/issues/13489
                    ignoreOutsideElement = true;
                    if (scrollTimeout) {
                        clearTimeout(scrollTimeout);
                        scrollTimeout = null;
                    }
                    scrollTimeout = setTimeout(function () {
                        ignoreOutsideElement = false;
                    }, 500);
                }

                function onEscapeHandler (event) {
                    if (event.which === 27) {
                        setTimeout(function () {
                            scope.$apply(attr.piwikFocusAnywhereButHere);
                        }, 0);
                    }
                }

                $document.on('keyup', onEscapeHandler);
                $document.on('mouseup', onClickOutsideElement);
                $document.on('scroll', onScroll);
                scope.$on('$destroy', function() {
                    $document.off('mouseup', onClickOutsideElement);
                    $document.off('keyup', onEscapeHandler);
                    $document.off('scroll', onScroll);
                });
            }
        };
    }
})();
