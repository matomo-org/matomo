/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
 * <h2 piwik-enriched-headline edit-url="index.php?module=Foo&action=bar&id=4">All Websites Dashboard</h2>
 * -> makes the headline clickable linking to the specified url
 *
 * <h2 piwik-enriched-headline inline-help="inlineHelp">Pages report</h2>
 * -> inlineHelp specified via a attribute shows help icon on headline hover
 *
 * <h2 piwik-enriched-headline>All Websites Dashboard
 *     <div class="inlineHelp">My <strong>inline help</strong></div>
 * </h2>
 * -> alternative definition for inline help
 * -> shows help icon to display inline help on click. Note: You can combine inlinehelp and help-url
 *
 * * <h2 piwik-enriched-headline report-generated="generated time">Pages report</h2>
 * -> reportGenerated specified via this attribute shows a clock icon with a tooltip which activated by hover
 * -> the tooltip shows the value of the attribute
 */
(function () {
    angular.module('piwikApp').directive('piwikEnrichedHeadline', piwikEnrichedHeadline);

    piwikEnrichedHeadline.$inject = ['$timeout'];

    function piwikEnrichedHeadline($timeout){
        return {
            transclude: true,
            replace: true,
            restrict: 'A',
            scope: {
                helpUrl: '@',
                editUrl: '@',
                reportGenerated: '@?',
                featureName: '@',
                inlineHelp: '@?',
                showReportGenerated: '=?'
            },
            // Note: The surrounding div is there, so we can replace the element, which might be already a h2
            template: '<div><matomo-enriched-headline help-url="{{ helpUrl }}" edit-url="{{ editUrl }}" ' +
                        'report-generated="{{ reportGenerated }}" feature-name="{{ featureName }}" ' +
                        'inline-help="{{ inlineHelp }}" show-report-generated="{{ showReportGenerated }}" ng-transclude></matomo-enriched-headline></div>',
            compile: function(element) {
                return {
                    post: function postLink( scope, element, attrs ) {
                        $timeout(function(){
                            matomo.createVue(element[0])
                        });
                    }
                }
            },
        };
    }
})();
