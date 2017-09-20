/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-content-block>
 */
(function () {
    angular.module('piwikApp').directive('piwikContentBlock', piwikContentBlock);

    piwikContentBlock.$inject = ['piwik'];

    function piwikContentBlock(piwik){

        var adminContent = null;

        return {
            restrict: 'A',
            replace: true,
            transclude: true,
            scope: {
                contentTitle: '@',
                feature: '@',
                helpUrl: '@',
                helpText: '@'
            },
            templateUrl: 'plugins/CoreHome/angularjs/content-block/content-block.directive.html?cb=' + piwik.cacheBuster,
            controllerAs: 'contentBlock',
            compile: function (element, attrs) {

                if (attrs.feature === 'true') {
                    attrs.feature = true;
                }

                return function (scope, element, attrs) {
                    var inlineHelp = element.find('[ng-transclude] > .contentHelp');
                    if (inlineHelp.length) {
                        scope.helpText = inlineHelp.html();
                        inlineHelp.remove();
                    }

                    if (scope.feature && (scope.feature===true || scope.feature ==='true')) {
                        scope.feature = scope.contentTitle;
                    }

                    if (adminContent === null) {
                        // cache admin node for further content blocks
                        adminContent = $('#content.admin');
                    }

                    var contentTopPosition = false;

                    if (adminContent.length) {
                        contentTopPosition = adminContent.offset().top;
                    }

                    if (contentTopPosition || contentTopPosition === 0) {
                        var parents = element.parentsUntil('.col', '[piwik-widget-loader]');
                        var topThis;
                        if (parents.length) {
                            // when shown within the widget loader, we need to get the offset of that element
                            // as the widget loader might be still shown. Would otherwise not position correctly
                            // the widgets on the admin home page
                            topThis = parents.offset().top;
                        } else {
                            topThis = element.offset().top;
                        }

                        if ((topThis - contentTopPosition) < 17) {
                            // we make sure to display the first card with no margin-top to have it on same as line as
                            // navigation
                            element.css('marginTop', '0');
                        }
                    }

                };
            }
        };
    }
})();