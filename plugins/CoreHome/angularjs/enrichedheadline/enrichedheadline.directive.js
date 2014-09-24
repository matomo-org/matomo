/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 *
 * <h2 piwik-enriched-headline>All Websites Dashboard</h2>
 * -> uses "All Websites Dashboard" as featurename
 *
 * <h2 piwik-enriched-headline feature-name="All Websites Dashboard">All Websites Dashboard (Total: 309 Visits)</h2>
 * -> custom featurename
 *
 * <h2 piwik-enriched-headline help-url="http://piwik.org/guide">All Websites Dashboard</h2>
 * -> shows help icon and links to external url
 *
 * <h2 piwik-enriched-headline>All Websites Dashboard
 *     <div class="inlineHelp>My <strong>inline help</strong></div>
 * </h2>
 * -> shows help icon to display inline help on click. Note: You can combine inlinehelp and help-url
 */
(function () {
    angular.module('piwikApp').directive('piwikEnrichedHeadline', piwikEnrichedHeadline);

    piwikEnrichedHeadline.$inject = ['$document', 'piwik', '$filter'];

    function piwikEnrichedHeadline($document, piwik, $filter){
        var defaults = {
            helpUrl: ''
        };

        return {
            transclude: true,
            restrict: 'A',
            scope: {
                helpUrl: '@',
                featureName: '@'
            },
            templateUrl: 'plugins/CoreHome/angularjs/enrichedheadline/enrichedheadline.directive.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                for (var index in defaults) {
                    if (!attrs[index]) { attrs[index] = defaults[index]; }
                }

                return function (scope, element, attrs) {

                    var helpNode = $('[ng-transclude] .inlineHelp', element);

                    if ((!helpNode || !helpNode.length) && element.next()) {
                        // hack for reports :(
                        helpNode = element.next().find('.reportDocumentation');
                    }

                    if (helpNode && helpNode.length) {
                        if ($.trim(helpNode.text())) {
                            scope.inlineHelp = $.trim(helpNode.html());
                        }
                        helpNode.remove();
                    }

                    if (!attrs.featureName) {
                        attrs.featureName = $.trim(element.text());
                    }
                };
            }
        };
    }
})();