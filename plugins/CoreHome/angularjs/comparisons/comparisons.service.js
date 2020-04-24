/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    angular.module('piwikApp.service').factory('piwikComparisonsService', ComparisonFactory);

    ComparisonFactory.$inject = ['$location', '$rootScope', 'piwikPeriods', 'piwikApi', 'piwikUrl'];

    function ComparisonFactory($location, $rootScope, piwikPeriods, piwikApi, piwikUrl) {
        var segmentComparisons = [];
        var periodComparisons = [];
        var comparisonsDisabledFor = [];
        var isEnabled = null;

        var SERIES_COLOR_COUNT = 8;
        var SERIES_SHADE_COUNT = 3;

        var colors = getAllSeriesColors();

        $rootScope.$on('$locationChangeSuccess', updateComparisonsFromQueryParams);
        $rootScope.$on('piwikSegmentationInited', updateComparisonsFromQueryParams);

        if (!piwikHelper.isAngularRenderingThePage()) { // if we're, eg, widgetized
            updateComparisonsFromQueryParams();
        }

        loadComparisonsDisabledFor();

        return {
            getComparisons: getComparisons,
            removeSegmentComparison: removeSegmentComparison,
            addSegmentComparison: addSegmentComparison,
            isComparisonEnabled: isComparisonEnabled,
            getSegmentComparisons: getSegmentComparisons,
            getPeriodComparisons: getPeriodComparisons,
            getSeriesColor: getSeriesColor,
            getAllComparisonSeries: getAllComparisonSeries,
            isComparing: isComparing,
            isComparingPeriods: isComparingPeriods,
            getIndividualComparisonRowIndices: getIndividualComparisonRowIndices,
            getComparisonSeriesIndex: getComparisonSeriesIndex,
            getSeriesColorName: getSeriesColorName
        };

        function getComparisons() {
            return getSegmentComparisons().concat(getPeriodComparisons());
        }

        function isComparing() {
            return isComparisonEnabled() && (segmentComparisons.length > 1 || periodComparisons.length > 1); // first two are for selected segment/period
        }

        function isComparingPeriods() {
            return getPeriodComparisons().length > 1; // first is currently selected period
        }

        function getSegmentComparisons() {
            if (!isComparisonEnabled()) {
                return [];
            }

            return segmentComparisons;
        }

        function getPeriodComparisons() {
            if (!isComparisonEnabled()) {
                return [];
            }

            return periodComparisons;
        }

        function getSeriesColor(segmentComparison, periodComparison, metricIndex) {
            metricIndex = metricIndex || 0;

            var seriesIndex = getComparisonSeriesIndex(periodComparison.index, segmentComparison.index) % SERIES_COLOR_COUNT;
            if (metricIndex === 0) {
                return colors['series' + seriesIndex];
            } else {
                var shadeIndex = metricIndex % SERIES_SHADE_COUNT;
                return colors['series' + seriesIndex + '-shade' + shadeIndex];
            }
        }

        function getSeriesColorName(seriesIndex, metricIndex) {
            var colorName = 'series' + (seriesIndex % SERIES_COLOR_COUNT);
            if (metricIndex > 0) {
                colorName += '-shade' + (metricIndex % SERIES_SHADE_COUNT);
            }
            return colorName;
        }

        function isComparisonEnabled() {
            return isEnabled;
        }

        function getIndividualComparisonRowIndices(seriesIndex) {
            var segmentCount = getSegmentComparisons().length;
            var segmentIndex = seriesIndex % segmentCount;
            var periodIndex = Math.floor(seriesIndex / segmentCount);

            return {
                segmentIndex: segmentIndex,
                periodIndex: periodIndex,
            };
        }

        function getComparisonSeriesIndex(periodIndex, segmentIndex) {
            var segmentCount = getSegmentComparisons().length;
            return periodIndex * segmentCount + segmentIndex;
        }

        function getAllComparisonSeries() {
            var seriesInfo = [];

            var seriesIndex = 0;
            getPeriodComparisons().forEach(function (periodComp) {
                getSegmentComparisons().forEach(function (segmentComp) {
                    seriesInfo.push({
                        index: seriesIndex,
                        params: $.extend({}, segmentComp.params, periodComp.params),
                        color: colors['series' + seriesIndex],
                    });
                    ++seriesIndex;
                });
            });
            return seriesInfo;
        }

        function removeSegmentComparison(index) {
            if (!isComparisonEnabled()) {
                throw new Error('Comparison disabled.');
            }

            var newComparisons = [].concat(segmentComparisons);
            newComparisons.splice(index, 1);

            var extraParams = {};
            if (index === 0) {
                extraParams.segment = newComparisons[0].params.segment;
            }

            updateQueryParamsFromComparisons(newComparisons, periodComparisons, extraParams);
        }

        function addSegmentComparison(params) {
            if (!isComparisonEnabled()) {
                throw new Error('Comparison disabled.');
            }

            var newComparisons = segmentComparisons.concat([{ params: params }]);
            updateQueryParamsFromComparisons(newComparisons, periodComparisons);
        }

        function updateQueryParamsFromComparisons(segmentComparisons, periodComparisons, extraParams) {
            extraParams = extraParams || {};

            // get unique segments/periods/dates from new Comparisons
            var compareSegments = {};
            var comparePeriodDatePairs = {};

            var firstSegment = false;
            var firstPeriod = false;

            segmentComparisons.forEach(function (comparison) {
                if (firstSegment) {
                    compareSegments[comparison.params.segment] = true;
                } else {
                    firstSegment = true;
                }
            });

            periodComparisons.forEach(function (comparison) {
                if (firstPeriod) {
                    comparePeriodDatePairs[comparison.params.period + '|' + comparison.params.date] = true;
                } else {
                    firstPeriod = true;
                }
            });

            var comparePeriods = [];
            var compareDates = [];
            Object.keys(comparePeriodDatePairs).forEach(function (pair) {
                var parts = pair.split('|');
                comparePeriods.push(parts[0]);
                compareDates.push(parts[1]);
            });

            var compareParams = {
                compareSegments: Object.keys(compareSegments),
                comparePeriods: comparePeriods,
                compareDates: compareDates,
            };

            // change the page w/ these new param values
            if (piwik.helper.isAngularRenderingThePage()) {
                var search = $location.search();
                var newSearch = $.extend({}, search, compareParams, extraParams);

                delete newSearch['compareSegments[]'];
                delete newSearch['comparePeriods[]'];
                delete newSearch['compareDates[]'];

                if (JSON.stringify(newSearch) !== JSON.stringify(search)) {
                    $location.search($.param(newSearch));
                }

                return;
            }

            var paramsToRemove = [];
            ['compareSegments', 'comparePeriods', 'compareDates'].forEach(function (name) {
                if (!compareParams[name].length) {
                    paramsToRemove.push(name);
                }
            });

            // angular is not rendering the page (ie, we are in the embedded dashboard) or we need to change the segment
            var url = $.param($.extend({}, extraParams));
            var strHash = $.param($.extend({}, compareParams));
            broadcast.propagateNewPage(url, undefined, strHash, paramsToRemove);
        }

        function updateComparisonsFromQueryParams() {
            var title;
            var availableSegments = [];
            try {
                availableSegments = $('.segmentEditorPanel').data('uiControlObject').impl.availableSegments || [];
            } catch (e) {
                // segment editor is not initialized yet
            }

            var compareSegments = piwikUrl.getSearchParam('compareSegments') || [];
            compareSegments = compareSegments instanceof Array ? compareSegments : [compareSegments];

            var comparePeriods = piwikUrl.getSearchParam('comparePeriods') || [];
            comparePeriods = comparePeriods instanceof Array ? comparePeriods : [comparePeriods];

            var compareDates = piwikUrl.getSearchParam('compareDates') || [];
            compareDates = compareDates instanceof Array ? compareDates : [compareDates];

            // add base comparisons
            compareSegments.unshift(piwikUrl.getSearchParam('segment'));
            comparePeriods.unshift(piwikUrl.getSearchParam('period'));
            compareDates.unshift(piwikUrl.getSearchParam('date'));

            var newSegmentComparisons = [];
            compareSegments.forEach(function (segment, idx) {
                var storedSegment = null;

                availableSegments.forEach(function (s) {
                    if (s.definition === segment
                        || s.definition === decodeURIComponent(segment)
                        || decodeURIComponent(s.definition) === segment
                    ) {
                        storedSegment = s;
                    }
                });

                var segmentTitle = storedSegment ? storedSegment.name : _pk_translate('General_Unknown');
                if (segment.trim() === '') {
                    segmentTitle = _pk_translate('SegmentEditor_DefaultAllVisits');
                }

                newSegmentComparisons.push({
                    params: {
                        segment: segment
                    },
                    title: piwikHelper.htmlDecode(segmentTitle),
                    index: idx
                });
            });

            var newPeriodComparisons = [];
            for (var i = 0; i < Math.min(compareDates.length, comparePeriods.length); ++i) {
                try {
                    title = piwikPeriods.parse(comparePeriods[i], compareDates[i]).getPrettyString();
                } catch (e) {
                    title = _pk_translate('General_Error');
                }

                newPeriodComparisons.push({
                    params: {
                        date: compareDates[i],
                        period: comparePeriods[i]
                    },
                    title: title,
                    index: i
                });
            }

            checkEnabledForCurrentPage();
            setComparisons(newSegmentComparisons, newPeriodComparisons);
        }

        function setComparisons(newSegmentComparisons, newPeriodComparisons) {
            var oldSegmentComparisons = segmentComparisons;
            var oldPeriodComparisons = periodComparisons;

            segmentComparisons = newSegmentComparisons;
            Object.freeze(segmentComparisons);

            periodComparisons = newPeriodComparisons;
            Object.freeze(periodComparisons);

            if (JSON.stringify(oldPeriodComparisons) !== JSON.stringify(periodComparisons)
                || JSON.stringify(oldSegmentComparisons) !== JSON.stringify(segmentComparisons)
            ) {
                $rootScope.$emit('piwikComparisonsChanged');
            }
        }

        function checkEnabledForCurrentPage() {
            // category/subcategory is not included on top bar pages, so in that case we use module/action
            var category = piwikUrl.getSearchParam('category') || piwikUrl.getSearchParam('module');
            var subcategory = piwikUrl.getSearchParam('subcategory') || piwikUrl.getSearchParam('action');

            var id = category + "." + subcategory;
            isEnabled = comparisonsDisabledFor.indexOf(id) === -1 && comparisonsDisabledFor.indexOf(category + ".*") === -1;

            $('html').toggleClass('comparisonsDisabled', !isEnabled);
        }

        function loadComparisonsDisabledFor() {
            piwikApi.fetch({
                module: 'API',
                method: 'API.getPagesComparisonsDisabledFor',
            }).then(function (result) {
                comparisonsDisabledFor = result;
                checkEnabledForCurrentPage();
            });
        }

        function getAllSeriesColors() {
            var colorManager = piwik.ColorManager,
                seriesColorNames = [];

            for (var i = 0; i < SERIES_COLOR_COUNT; ++i) {
                seriesColorNames.push('series' + i);
                for (var j = 0; j < SERIES_SHADE_COUNT; ++j) {
                    seriesColorNames.push('series' + i + '-shade' + j);
                }
            }

            return colorManager.getColors('comparison-series-color', seriesColorNames);
        }
    }

})();
