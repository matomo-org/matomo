/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    angular.module('piwikApp').component('piwikComparisons', {
        templateUrl: 'plugins/CoreHome/angularjs/comparisons/comparisons.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            // empty
        },
        controller: ComparisonsController
    });

    ComparisonsController.$inject = ['piwikComparisonsService', '$rootScope', 'piwikApi', '$element', 'piwikUrl'];

    function ComparisonsController(comparisonsService, $rootScope, piwikApi, $element, piwikUrl) {
        var vm = this;
        var comparisonTooltips = null;

        vm.comparisonsService = comparisonsService;
        vm.$onInit = $onInit;
        vm.$onDestroy = $onDestroy;
        vm.comparisonHasSegment = comparisonHasSegment;
        vm.getComparisonPeriodType = getComparisonPeriodType;
        vm.getComparisonTooltip = getComparisonTooltip;
        vm.getUrlToSegment = getUrlToSegment;

        function $onInit() {
            $rootScope.$on('piwikComparisonsChanged', onComparisonsChanged);

            onComparisonsChanged();

            setUpTooltips();
        }

        function $onDestroy() {
            try {
                $element.tooltip('destroy');
            } catch (e) {
                // ignore
            }
        }

        function setUpTooltips() {
            $element.tooltip({
                track: true,
                content: function() {
                    var title = $(this).attr('title');
                    return piwikHelper.escape(title.replace(/\n/g, '<br />'));
                },
                show: {delay: 200, duration: 200},
                hide: false
            });
        }

        function comparisonHasSegment(comparison) {
            return typeof comparison.params.segment !== 'undefined';
        }

        function getComparisonPeriodType(comparison) {
            var period = comparison.params.period;
            if (period === 'range') {
                return _pk_translate('CoreHome_PeriodRange');
            }
            var periodStr = _pk_translate('Intl_Period' + period.substring(0, 1).toUpperCase() + period.substring(1));
            return periodStr.substring(0, 1).toUpperCase() + periodStr.substring(1);
        }

        function getComparisonTooltip(segmentComparison, periodComparison) {
            if (!comparisonTooltips
                || !Object.keys(comparisonTooltips).length
            ) {
                return undefined;
            }

            return comparisonTooltips[periodComparison.index][segmentComparison.index];
        }

        function onComparisonsChanged() {
            comparisonTooltips = null;

            if (!vm.comparisonsService.isComparing()) {
                return;
            }

            var periodComparisons = comparisonsService.getPeriodComparisons();
            var segmentComparisons = comparisonsService.getSegmentComparisons();
            piwikApi.fetch({
                method: 'API.getProcessedReport',
                apiModule: 'VisitsSummary',
                apiAction: 'get',
                compare: '1',
                compareSegments: piwikUrl.getSearchParam('compareSegments'),
                comparePeriods: piwikUrl.getSearchParam('comparePeriods'),
                compareDates: piwikUrl.getSearchParam('compareDates'),
                format_metrics: '1',
            }).then(function (report) {
                comparisonTooltips = {};
                periodComparisons.forEach(function (periodComp) {
                    comparisonTooltips[periodComp.index] = {};

                    segmentComparisons.forEach(function (segmentComp) {
                        comparisonTooltips[periodComp.index][segmentComp.index] = generateComparisonTooltip(report, periodComp, segmentComp);
                    });
                });
            });
        }

        function generateComparisonTooltip(visitsSummary, periodComp, segmentComp) {
            if (!visitsSummary.reportData.comparisons) { // sanity check
                return '';
            }

            var firstRowIndex = comparisonsService.getComparisonSeriesIndex(periodComp.index, 0);

            var firstRow = visitsSummary.reportData.comparisons[firstRowIndex];

            var comparisonRowIndex = comparisonsService.getComparisonSeriesIndex(periodComp.index, segmentComp.index);
            var comparisonRow = visitsSummary.reportData.comparisons[comparisonRowIndex];

            var firstPeriodRow = visitsSummary.reportData.comparisons[segmentComp.index];

            var tooltip = '<div class="comparison-card-tooltip">';

            var visitsPercent = ((comparisonRow.nb_visits / firstRow.nb_visits) * 100).toFixed(2) + '%';

            tooltip += _pk_translate('General_ComparisonCardTooltip1', [
                "'" + comparisonRow.compareSegmentPretty + "'",
                comparisonRow.comparePeriodPretty,
                visitsPercent,
                comparisonRow.nb_visits,
                firstRow.nb_visits
            ]);
            if (periodComp.index > 0) {
                tooltip += '<br/><br/>';
                tooltip += _pk_translate('General_ComparisonCardTooltip2', [
                    comparisonRow.nb_visits_change,
                    firstPeriodRow.compareSegmentPretty,
                    firstPeriodRow.comparePeriodPretty
                ]);
            }

            tooltip += '</div>';
            return tooltip;
        }

        function getUrlToSegment(segment) {
            var hash = window.location.hash;
            hash = broadcast.updateParamValue('comparePeriods[]=', hash);
            hash = broadcast.updateParamValue('compareDates[]=', hash);
            hash = broadcast.updateParamValue('compareSegments[]=', hash);
            hash = broadcast.updateParamValue('segment=' + encodeURIComponent(segment), hash);
            return window.location.search + hash;
        }
    }
})();
