/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    // can probably be shared
    angular.module('piwikApp').factory('piwikComparisonsService', ComparisonFactory);

    ComparisonFactory.$inject = ['$location', '$rootScope', 'piwikPeriods'];

    // TODO: unit test
    function ComparisonFactory($location, $rootScope, piwikPeriods) {
        var comparisons;

        $rootScope.$on('$locationChangeSuccess', updateComparisonsFromQueryParams);

        updateComparisonsFromQueryParams();

        return {
            getComparisons: getComparisons,
            removeComparison: removeComparison,
            addComparison: addComparison
        };

        function getComparisons() {
            return comparisons;
        }

        function removeComparison(comparisonToRemove) {
            var newComparisons = comparisons.filter(function (comparison) {
                return comparison !== comparisonToRemove;
            });
            updateQueryParamsFromComparisons(newComparisons);
        }

        function addComparison(params) {
            var newComparisons = comparisons.concat([{ params: params }]);
            updateQueryParamsFromComparisons(newComparisons);
        }

        function updateQueryParamsFromComparisons(newComparisons) {
            // get unique segments/periods/dates from new Comparisons
            var compareSegments = {};
            var comparePeriodDatePairs = {};

            newComparisons.forEach(function (comparison) {
                if (typeof comparison.params.segment !== 'undefined') {
                    compareSegments[comparison.params.segment] = true;
                } else if (typeof comparison.params.period !== 'undefined') {
                    comparePeriodDatePairs[comparison.params.period + '|' + comparison.params.date] = true;
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

            var availableSegments = $('.segmentEditorPanel').data('uiControlObject').impl.availableSegments || [];

            var compareSegments = getQueryParamValue('compareSegments') || [];
            compareSegments = compareSegments instanceof Array ? compareSegments : [compareSegments];

            var comparePeriods = getQueryParamValue('comparePeriods') || [];
            comparePeriods = comparePeriods instanceof Array ? comparePeriods : [comparePeriods];

            var compareDates = getQueryParamValue('compareDates') || [];
            compareDates = compareDates instanceof Array ? compareDates : [compareDates];

            var newComparisons = [];
            compareSegments.forEach(function (segment) {
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
                    title: segmentTitle
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
                    title: title
                });
            }

            setComparisons(newComparisons);
        }

        function getQueryParamValue(name) { // TODO: code redundancy w/ period selector
            var result = broadcast.getValueFromHash(name);
            if (!result) {
                result = broadcast.getValueFromUrl(name);
            }
            return result;
        }

        function setComparisons(newComparisons) {
            comparisons = newComparisons;
            Object.freeze(comparisons);
        }
    }

})();
