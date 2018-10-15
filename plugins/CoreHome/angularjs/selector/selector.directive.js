/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 *
 */
(function () {
    angular.module('piwikApp').directive('piwikExpandOnClick', piwikExpandOnClick);

    piwikExpandOnClick.$inject = ['$document', 'piwik'];

    function piwikExpandOnClick($document, piwik){

        return {
            restrict: 'A',
            link: function(scope, element, attr) {

                element.find('.title').on('click', function () {
                    element.toggleClass('expanded');

                    var $position = element.find('.dropdown.positionInViewport');

                    if ($position.length) {
                        piwik.helper.setMarginLeftToBeInViewport($position);
                    }
                });

                var isMouseDown = false;
                var hasScrolled = false;

                function onClickOutsideElement (event) {
                    var hadUsedScrollbar = isMouseDown && hasScrolled;
                    isMouseDown = false;
                    hasScrolled = false;

                    if (hadUsedScrollbar) {
                        return;
                    }

                    if (element.has(event.target).length === 0) {
                        element.removeClass('expanded');
                    }
                }

                function onScroll (event) {
                    hasScrolled = true;
                }

                function onMouseDown (event) {
                    isMouseDown = true;
                    hasScrolled = false;
                }

                function onEscapeHandler (event) {
                    if (event.which === 27) {
                        isMouseDown = false;
                        hasScrolled = false;
                        element.removeClass('expanded');
                    }
                }

                $document.on('keyup', onEscapeHandler);
                $document.on('mousedown', onMouseDown);
                $document.on('mouseup', onClickOutsideElement);
                $document.on('scroll', onScroll);
                scope.$on('$destroy', function() {
                    $document.off('keyup', onEscapeHandler);
                    $document.off('mousedown', onMouseDown);
                    $document.off('mouseup', onClickOutsideElement);
                    $document.off('scroll', onScroll);
                });
            }
        };
    }

    angular.module('piwikApp').directive('piwikExpandOnHover', piwikExpandOnHover);

    piwikExpandOnHover.$inject = ['$document', 'piwik'];

    function piwikExpandOnHover($document, piwik){

        return {
            restrict: 'A',
            link: function(scope, element, attr) {

                element.on('mouseenter', '.title', function () {
                    element.addClass('expanded');

                    var $position = element.find('.dropdown.positionInViewport');

                    if ($position.length) {
                        piwik.helper.setMarginLeftToBeInViewport($position);
                    }
                });

                element.on('mouseleave', function () {
                    element.removeClass('expanded');
                });

                function onClickOutsideElement (event) {
                    if (element.has(event.target).length === 0) {
                        element.removeClass('expanded');
                    }
                }

                function onEscapeHandler (event) {
                    if (event.which === 27) {
                        element.removeClass('expanded');
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
