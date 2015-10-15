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

    piwikExpandOnClick.$inject = ['$document'];

    function piwikExpandOnClick($document){

        return {
            restrict: 'A',
            link: function(scope, element, attr) {

                element.find('.title').on('click', function () {
                    element.toggleClass('expanded');
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

    angular.module('piwikApp').directive('piwikExpandOnHover', piwikExpandOnHover);

    piwikExpandOnHover.$inject = ['$document'];

    function piwikExpandOnHover($document){

        return {
            restrict: 'A',
            link: function(scope, element, attr) {

                element.on('mouseenter', '.title', function () {
                    element.addClass('expanded');
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
