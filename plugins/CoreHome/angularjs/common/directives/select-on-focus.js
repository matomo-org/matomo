/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * On focus (click, tab) selects the text within the current element
 *
 * Example:
 * <div piwik-select-on-focus>my dialog</div>
 */
(function () {
    angular.module('piwikApp.directive').directive('piwikSelectOnFocus', piwikSelectOnFocus);

    function piwikSelectOnFocus(){
        return {
            restrict: 'A',
            link: function(scope, element, attr, ctrl) {

                var focusedElement = null;

                var tagName = (element.prop('tagName') + '').toLowerCase();
                var elementSupportsSelect = tagName === 'textarea';

                function onFocusHandler(event) {
                    if (focusedElement !== this) {
                        focusedElement = this;
                        angular.element(this).select();
                    }
                }

                function onClickHandler(event) {
                    // .select() + focus and blur seems to not work on pre elements
                    var range = document.createRange();
                    range.selectNode(this);
                    window.getSelection().addRange(range);
                }

                function onBlurHandler(event) {
                    focusedElement = null;
                }

                if (elementSupportsSelect) {
                    element.on('focus', onFocusHandler);
                    element.on('blur', onBlurHandler);
                } else {
                    element.on('click', onClickHandler);
                }

                scope.$on('$destroy', function() {
                    if (elementSupportsSelect) {
                        element.off('focus', onFocusHandler);
                        element.off('blur', onBlurHandler);
                    } else {
                        element.off('click', onClickHandler);
                    }
                });
            }
        };
    }
})();
