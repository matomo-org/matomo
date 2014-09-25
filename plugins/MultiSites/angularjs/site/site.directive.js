/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Renders a single website row, for instance to be used within the MultiSites Dashboard.
 *
 * Usage:
 * <div piwik-multisites-site>
 *     website="{label: 'Name', main_url: 'http://...', idsite: '...'}"
 *     evolution-metric="visits_evolution"
 *     show-sparklines="true"
 *     date-sparkline="2014-01-01,2014-02-02"
 *     display-revenue-column="true"
 *     </div>
 */
(function () {
    angular.module('piwikApp').directive('piwikMultisitesSite', piwikMultisitesSite);

    piwikMultisitesSite.$inject = ['piwik'];

    function piwikMultisitesSite(piwik){

        return {
            restrict: 'AC',
            replace: true,
            scope: {
                website: '=',
                evolutionMetric: '=',
                showSparklines: '=',
                dateSparkline: '=',
                displayRevenueColumn: '=',
                metric: '='
            },
            templateUrl: 'plugins/MultiSites/angularjs/site/site.directive.html?cb=' + piwik.cacheBuster,
            controller: 'MultiSitesSiteController'
        };
    }
})();