/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    angular.module('piwikApp').component('piwikComparisons', {
        templateUrl: 'plugins/CoreHome/angularjs/comparisons/comparisons.component.html?cb=' + piwik.cacheBuster,
        bindings: {
            // TODO
        },
        controller: ComparisonsController
    });

    ComparisonsController.$inject = ['piwikComparisonsService', '$rootScope', 'piwikApi', '$element'];

    function ComparisonsController(comparisonsService, $rootScope, piwikApi, $element) {
        var vm = this;
        var comparisonTooltips = null;

        vm.comparisonsService = comparisonsService;
        vm.$onInit = $onInit;
        vm.$onDestroy = $onDestroy;
        vm.comparisonHasSegment = comparisonHasSegment;
        vm.getComparisonPeriodType = getComparisonPeriodType;
        vm.getComparisonTooltip = getComparisonTooltip;

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
            if (!comparisonTooltips) {
                return undefined;
            }

            return comparisonTooltips[periodComparison.index][segmentComparison.index];
        }

        function onComparisonsChanged() {
            comparisonTooltips = null;

            if (!vm.comparisonsService.getComparisons().length) {
                return;
            }


            piwikApi.fetch({
                method: 'API.getProcessedReport',
                apiModule: 'VisitsSummary',
                apiAction: 'get',
                compare: '1',
                compareSegments: getQueryParamValue('compareSegments'),
                comparePeriods: getQueryParamValue('comparePeriods'),
                compareDates: getQueryParamValue('compareDates'),
                format_metrics: '1',
            }).then(function (report) {
                comparisonTooltips = {};
                comparisonsService.getPeriodComparisons().forEach(function (periodComp) {
                    comparisonTooltips[periodComp.index] = {};

                    var segmentComparisons = comparisonsService.getSegmentComparisons();
                    segmentComparisons.forEach(function (segmentComp) {
                        comparisonTooltips[periodComp.index][segmentComp.index] = generateComparisonTooltip(report, periodComp, segmentComp, segmentComparisons.length);
                    });
                });
            });
        }

        // TODO: data structures used to store comparisons are all over the place, needs to be better.
        function generateComparisonTooltip(visitsSummary, periodComp, segmentComp, segmentCompCount) {
            var firstRow = visitsSummary.reportData.comparisons[0];
            var comparedToSegmentLabel = firstRow.compareSegmentPretty + ', ' + firstRow.comparePeriodPretty;

            var comparisonRow = visitsSummary.reportData.comparisons[periodComp.index * segmentCompCount + segmentComp.index];

            var tooltip = '<div class="comparison-card-tooltip">';
            Object.keys(visitsSummary.columns).forEach(function (columnName) {
                if (typeof comparisonRow[columnName] === 'undefined') {
                    return;
                }

                tooltip += '<p>';
                tooltip += comparisonRow[columnName] + ' ' + visitsSummary.columns[columnName];
                if (segmentComp.index > 0 || periodComp.index > 0) {
                    tooltip += ' (' + _pk_translate('General_XComparedToY', ['<strong>' + comparisonRow[columnName + '_change'] + '</strong>', comparedToSegmentLabel]) + ')';
                }
                tooltip += '</p>';
            });
            tooltip += '</div>';
            return tooltip;
        }

        function getQueryParamValue(name) { // TODO: code redundancy w/ period selector and elsewhere
            var result = broadcast.getValueFromHash(name);
            if (!result) {
                result = broadcast.getValueFromUrl(name);
            }
            return result;
        }
    }
})();
