/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    // can probably be shared
    angular.module('piwikApp').factory('piwikComparisonsService', ComparisonFactory);

    ComparisonFactory.$inject = ['$location', '$rootScope', 'piwikPeriods', 'piwikApi'];

    // TODO: unit test
    function ComparisonFactory($location, $rootScope, piwikPeriods, piwikApi) {
        var comparisons = []; // TODO: split into segment/period array, code will be simpler
        var comparisonSeriesIndices = {};
        var comparisonsDisabledFor = [];
        var isEnabled = true;

        var SERIES_COLOR_COUNT = 8;
        var SERIES_SHADE_COUNT = 3;

        var colors = {};
        getAllSeriesColors();

        $rootScope.$on('$locationChangeSuccess', updateComparisonsFromQueryParams);
        $rootScope.$on('piwikPageChange', checkEnabledForCurrentPage);
        $rootScope.$on('piwikSegmentationInited', updateComparisonsFromQueryParams);

        loadComparisonsDisabledFor();

        return {
            getComparisons: getComparisons,
            removeComparison: removeComparison,
            addComparison: addComparison,
            isComparisonEnabled: isComparisonEnabled,
            getSegmentComparisons: getSegmentComparisons,
            getPeriodComparisons: getPeriodComparisons,
            getSeriesColor: getSeriesColor,
            getAllComparisonSeries: getAllComparisonSeries,
            isComparing: isComparing
        };

        function isComparing() {
            return isComparisonEnabled() && comparisons.length > 0;
        }

        function getSegmentComparisons() {
            return getComparisons().filter(function (comp) { return typeof comp.params.segment !== 'undefined'; });
        }

        function getPeriodComparisons() {
            return getComparisons().filter(function (comp) { return typeof comp.params.period !== 'undefined'; });
        }

        function getSeriesColor(segmentComparison, periodComparison, metricIndex) {
            metricIndex = metricIndex || 0;

            var seriesIndex = comparisonSeriesIndices[periodComparison.index][segmentComparison.index] % SERIES_COLOR_COUNT;
            if (metricIndex === 0) {
                return colors['series' + seriesIndex];
            } else {
                var shadeIndex = metricIndex % SERIES_SHADE_COUNT;
                return colors['series' + seriesIndex + '-shade' + shadeIndex];
            }
        }

        function isComparisonEnabled() {
            return isEnabled;
        }

        function getComparisons() {
            if (!isComparisonEnabled()) {
                return [];
            }

            return comparisons;
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

        function removeComparison(comparisonToRemove) {
            if (!isComparisonEnabled()) {
                throw new Error('Comparison disabled.');
            }

            var newComparisons = comparisons.filter(function (comparison) {
                return comparison !== comparisonToRemove;
            });
            updateQueryParamsFromComparisons(newComparisons);
        }

        function addComparison(params) {
            if (!isComparisonEnabled()) {
                throw new Error('Comparison disabled.');
            }

            var newComparisons = comparisons.concat([{ params: params }]);
            updateQueryParamsFromComparisons(newComparisons);
        }

        function updateQueryParamsFromComparisons(newComparisons) {
            // get unique segments/periods/dates from new Comparisons
            var compareSegments = {};
            var comparePeriodDatePairs = {};

            var firstSegment = false;
            var firstPeriod = false;

            newComparisons.forEach(function (comparison) {
                if (typeof comparison.params.segment !== 'undefined') {
                    if (firstSegment) {
                        compareSegments[comparison.params.segment] = true;
                    } else {
                        firstSegment = true;
                    }
                } else if (typeof comparison.params.period !== 'undefined') {
                    if (firstPeriod) {
                        comparePeriodDatePairs[comparison.params.period + '|' + comparison.params.date] = true;
                    } else {
                        firstPeriod = true;
                    }
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
                var newSearch = $.extend({}, search, compareParams);

                delete newSearch['compareSegments[]'];
                delete newSearch['comparePeriods[]'];
                delete newSearch['compareDates[]'];

                if (JSON.stringify(newSearch) !== JSON.stringify(search)) { // TODO: test this
                    $location.search($.param(newSearch));
                }

                return;
            }

            // angular is not rendering the page (ie, we are in the embedded dashboard)
            var url = $.param(compareParams);
            broadcast.propagateNewPage(url);
        }

        function updateComparisonsFromQueryParams() {
            var title;

            try {
                var availableSegments = $('.segmentEditorPanel').data('uiControlObject').impl.availableSegments || [];
            } catch (e) {
                // segment editor is not initialized yet
            }

            var compareSegments = getQueryParamValue('compareSegments') || [];
            compareSegments = compareSegments instanceof Array ? compareSegments : [compareSegments];

            var comparePeriods = getQueryParamValue('comparePeriods') || [];
            comparePeriods = comparePeriods instanceof Array ? comparePeriods : [comparePeriods];

            var compareDates = getQueryParamValue('compareDates') || [];
            compareDates = compareDates instanceof Array ? compareDates : [compareDates];

            if (!compareSegments.length && !comparePeriods.length) {
                setComparisons([]);
                return;
            }

            // add base comparisons
            compareSegments.unshift(getQueryParamValue('segment'));
            comparePeriods.unshift(getQueryParamValue('period'));
            compareDates.unshift(getQueryParamValue('date'));

            var newComparisons = [];
            compareSegments.forEach(function (segment, idx) {
                var storedSegment = availableSegments.find(function (s) {
                    return s.definition === segment;
                });

                var segmentTitle = storedSegment ? storedSegment.name : _pk_translate('General_Unknown');
                if (segment.replace('/^\s+|\s+$/g', '') === '') {
                    segmentTitle = _pk_translate('SegmentEditor_DefaultAllVisits');
                }

                newComparisons.push({
                    params: {
                        segment: segment
                    },
                    title: segmentTitle,
                    index: idx
                });
            });

            for (var i = 0; i < Math.min(compareDates.length, comparePeriods.length); ++i) {
                try {
                    title = piwikPeriods.parse(comparePeriods[i], compareDates[i]).getPrettyString();
                } catch (e) {
                    title = _pk_translate('General_Error');
                }

                newComparisons.push({
                    params: {
                        date: compareDates[i],
                        period: comparePeriods[i]
                    },
                    title: title,
                    index: i
                });
            }

            setComparisons(newComparisons);
        }

        function getQueryParamValue(name) { // TODO: code redundancy w/ period selector and elsewhere
            var result = broadcast.getValueFromHash(name);
            if (!result) {
                result = broadcast.getValueFromUrl(name);
            }
            return result;
        }

        function setComparisons(newComparisons) {
            comparisons = newComparisons;
            Object.freeze(comparisons);

            comparisonSeriesIndices = {};

            var seriesCount = 0;
            getPeriodComparisons().forEach(function (periodComp) {
                comparisonSeriesIndices[periodComp.index] = {};
                getSegmentComparisons().forEach(function (segmentComp) {
                    comparisonSeriesIndices[periodComp.index][segmentComp.index] = seriesCount;
                    ++seriesCount;
                });
            });

            $rootScope.$emit('piwikComparisonsChanged');
        }

        function checkEnabledForCurrentPage() {
            var category = getQueryParamValue('category');
            var subcategory = getQueryParamValue('subcategory');

            var id = category + "." + subcategory;
            isEnabled = comparisonsDisabledFor.indexOf(id) === -1;

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

            colors = colorManager.getColors('comparison-series-color', seriesColorNames);
        }
    }

})();
