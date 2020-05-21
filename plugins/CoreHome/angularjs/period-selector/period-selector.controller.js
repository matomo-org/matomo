/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    angular.module('piwikApp').controller('PeriodSelectorController', PeriodSelectorController);

    PeriodSelectorController.$inject = ['piwik', '$location', 'piwikPeriods', 'piwikComparisonsService', '$rootScope', 'piwikUrl', '$element', '$timeout'];

    function PeriodSelectorController(piwik, $location, piwikPeriods, piwikComparisonsService, $rootScope, piwikUrl, $element, $timeout) {
        var piwikMinDate = new Date(piwik.minDateYear, piwik.minDateMonth - 1, piwik.minDateDay),
            piwikMaxDate = new Date(piwik.maxDateYear, piwik.maxDateMonth - 1, piwik.maxDateDay);

        var vm = this;

        vm.comparePeriodDropdownOptions = [
            {key: 'custom', value: _pk_translate('General_Custom')},
            {key: 'previousPeriod', value: _pk_translate('General_PreviousPeriod').replace(/\s+/, piwikHelper.htmlDecode('&nbsp;'))},
            {key: 'previousYear', value: _pk_translate('General_PreviousYear').replace(/\s+/, piwikHelper.htmlDecode('&nbsp;'))},
        ];

        // the period & date currently being viewed
        vm.periodValue = null;
        vm.dateValue = null;

        vm.selectedPeriod = null;

        vm.startRangeDate = null;
        vm.endRangeDate = null;
        vm.isRangeValid = null;

        vm.isLoadingNewPage = false;

        vm.isComparing = false;
        vm.comparePeriodType = 'previousPeriod';
        vm.compareStartDate = '';
        vm.compareEndDate = '';

        vm.getCurrentlyViewingText = getCurrentlyViewingText;
        vm.changeViewedPeriod = changeViewedPeriod;
        vm.setPiwikPeriodAndDate = setPiwikPeriodAndDate;
        vm.onApplyClicked = onApplyClicked;
        vm.updateSelectedValuesFromHash = updateSelectedValuesFromHash;
        vm.getPeriodDisplayText = getPeriodDisplayText;
        vm.$onChanges = $onChanges;
        vm.onRangeChange = onRangeChange;
        vm.isApplyEnabled = isApplyEnabled;
        vm.$onInit = init;
        vm.isComparisonEnabled = isComparisonEnabled;

        $rootScope.$on('$locationChangeSuccess', setIsComparing);

        function init() {
            vm.updateSelectedValuesFromHash();
            setIsComparing();
            initTopControls(); // must be called when a top control changes width

            handleZIndexPositionRelativeCompareDropdownIssue();
        }

        function handleZIndexPositionRelativeCompareDropdownIssue() {
            $element.on('focus', '#comparePeriodToDropdown .select-dropdown', function () {
                $element.addClass('compare-dropdown-open');
            }).on('blur', '#comparePeriodToDropdown .select-dropdown', function () {
                $element.removeClass('compare-dropdown-open');
            });
        }

        function setIsComparing() {
            vm.isComparing = piwikComparisonsService.isComparingPeriods();
        }

        function $onChanges(changesObj) {
            if (changesObj.periods) {
                removeUnrecognizedPeriods();
            }
        }

        function onRangeChange(start, end) {
            if (!start || !end) {
                vm.isRangeValid = false;
                return;
            }

            vm.isRangeValid = true;
            vm.startRangeDate = start;
            vm.endRangeDate = end;
        }

        function isApplyEnabled() {
            if (vm.selectedPeriod === 'range'
                && !vm.isRangeValid
            ) {
                return false;
            }

            if (vm.isComparing
                && vm.comparePeriodType === 'custom'
                && !isCompareRangeValid()
            ) {
                return false;
            }

            return true;
        }

        function isCompareRangeValid() {
            try {
                piwikPeriods.parseDate(vm.compareStartDate);
            } catch (e) {
                return false;
            }

            try {
                piwikPeriods.parseDate(vm.compareEndDate);
            } catch (e) {
                return false;
            }

            return true;
        }

        function removeUnrecognizedPeriods() {
            vm.periods = vm.periods.filter(function (periodLabel) {
                return piwikPeriods.isRecognizedPeriod(periodLabel);
            });
        }

        function updateSelectedValuesFromHash() {
            var strDate = piwikUrl.getSearchParam('date');
            var strPeriod = piwikUrl.getSearchParam('period');

            vm.periodValue = strPeriod;
            vm.selectedPeriod = strPeriod;

            vm.dateValue = vm.startRangeDate = vm.endRangeDate = null;

            if (strPeriod === 'range') {
                var period = piwikPeriods.get(strPeriod).parse(strDate);
                vm.dateValue = period.startDate;
                vm.startRangeDate = formatDate(period.startDate);
                vm.endRangeDate = formatDate(period.endDate);
            } else {
                vm.dateValue = piwikPeriods.parseDate(strDate);
                setRangeStartEndFromPeriod(strPeriod, strDate);
            }
        }

        function getPeriodDisplayText(periodLabel) {
            return piwikPeriods.get(periodLabel).getDisplayText();
        }

        function getCurrentlyViewingText() {
            var date;
            if (vm.periodValue === 'range') {
                date = vm.startRangeDate + ',' + vm.endRangeDate;
            } else {
                date = formatDate(vm.dateValue);
            }

            try {
                return piwikPeriods.parse(vm.periodValue, date).getPrettyString();
            } catch (e) {
                return _pk_translate('General_Error');
            }
        }

        function changeViewedPeriod(period) {
            // only change period if it's different from what's being shown currently
            if (period === vm.periodValue) {
                return;
            }

            // can't just change to a range period, w/o setting two new dates
            if (period === 'range') {
                return;
            }

            setPiwikPeriodAndDate(period, vm.dateValue);
        }

        function onApplyClicked() {
            if (vm.selectedPeriod === 'range') {
                var dateString = getSelectedDateString();
                if (!dateString) {
                    return;
                }

                vm.periodValue = 'range';

                propagateNewUrlParams(dateString, 'range');
                return;
            }

            setPiwikPeriodAndDate(vm.selectedPeriod, vm.dateValue);
        }

        function getSelectedDateString() {
            if (vm.selectedPeriod === 'range') {
                var dateFrom = vm.startRangeDate,
                    dateTo = vm.endRangeDate,
                    oDateFrom = piwikPeriods.parseDate(dateFrom),
                    oDateTo = piwikPeriods.parseDate(dateTo);

                if (!isValidDate(oDateFrom)
                    || !isValidDate(oDateTo)
                    || oDateFrom > oDateTo
                ) {
                    // TODO: use a notification instead?
                    $('#alert').find('h2').text(_pk_translate('General_InvalidDateRange'));
                    piwik.helper.modalConfirm('#alert', {});
                    return null;
                }

                return dateFrom + ',' + dateTo;
            } else {
                return formatDate(vm.dateValue);
            }
        }

        function setPiwikPeriodAndDate(period, date) {
            vm.periodValue = period;
            vm.selectedPeriod = period;
            vm.dateValue = date;

            var currentDateString = formatDate(date);
            setRangeStartEndFromPeriod(period, currentDateString);

            propagateNewUrlParams(currentDateString, vm.selectedPeriod);
            initTopControls();
        }

        function setRangeStartEndFromPeriod(period, dateStr) {
            var dateRange = piwikPeriods.parse(period, dateStr).getDateRange();
            vm.startRangeDate = formatDate(dateRange[0] < piwikMinDate ? piwikMinDate : dateRange[0]);
            vm.endRangeDate = formatDate(dateRange[1] > piwikMaxDate ? piwikMaxDate : dateRange[1]);
        }

        function getSelectedComparisonParams() {
            var previousDate;

            if (!vm.isComparing) {
                return {};
            }

            if (vm.comparePeriodType === 'custom') {
                return {
                    comparePeriods: ['range'],
                    compareDates: [vm.compareStartDate + ',' + vm.compareEndDate],
                };
            } else if (vm.comparePeriodType === 'previousPeriod') {
                previousDate = getPreviousPeriodDateToSelectedPeriod();
                return {
                    comparePeriods: [vm.selectedPeriod],
                    compareDates: [previousDate],
                };
            } else if (vm.comparePeriodType === 'previousYear') {
                var dateStr = vm.selectedPeriod === 'range' ? (vm.startRangeDate + ',' + vm.endRangeDate) : vm.dateValue;
                var currentDateRange = piwikPeriods.parse(vm.selectedPeriod, dateStr).getDateRange();
                currentDateRange[0].setFullYear(currentDateRange[0].getFullYear() - 1);
                currentDateRange[1].setFullYear(currentDateRange[1].getFullYear() - 1);

                if (vm.selectedPeriod === 'range') {
                    return {
                        comparePeriods: ['range'],
                        compareDates: [piwikPeriods.format(currentDateRange[0]) + ',' + piwikPeriods.format(currentDateRange[1])],
                    };
                }

                return {
                    comparePeriods: [vm.selectedPeriod],
                    compareDates: [piwikPeriods.format(currentDateRange[0])],
                };
            } else {
                console.warn("Unknown compare period type: " + vm.comparePeriodType);
                return {};
            }
        }

        function getPreviousPeriodDateToSelectedPeriod() {
            if (vm.selectedPeriod === 'range') {
                var currentStartRange = piwikPeriods.parseDate(vm.startRangeDate);
                var currentEndRange = piwikPeriods.parseDate(vm.endRangeDate);
                var newEndDate = piwikPeriods.RangePeriod.getLastNRange('day', 2, currentStartRange).startDate;

                var rangeSize = Math.floor((currentEndRange - currentStartRange) / 86400000);
                var newRange = piwikPeriods.RangePeriod.getLastNRange('day', 1 + rangeSize, newEndDate);

                return piwikPeriods.format(newRange.startDate) + ',' + piwikPeriods.format(newRange.endDate);
            }

            var newStartDate = piwikPeriods.RangePeriod.getLastNRange(vm.selectedPeriod, 2, vm.dateValue).startDate;
            return piwikPeriods.format(newStartDate);
        }

        function propagateNewUrlParams(date, period) {
            var compareParams = getSelectedComparisonParams();

            if (piwik.helper.isAngularRenderingThePage()) {
                vm.closePeriodSelector(); // defined in directive

                var $search = $location.search();
                var isCurrentlyComparing = piwikUrl.getSearchParam('compareSegments') || piwikUrl.getSearchParam('comparePeriods');
                if (date !== $search.date || period !== $search.period || vm.isComparing || isCurrentlyComparing) {
                    // eg when using back button the date might be actually already changed in the URL and we do not
                    // want to change the URL again
                    $search.date = date;
                    $search.period = period;
                    $search.compareSegments = piwikUrl.getSearchParam('compareSegments') || [];
                    $.extend($search, compareParams);

                    delete $search['compareSegments[]'];
                    delete $search['comparePeriods[]'];
                    delete $search['compareDates[]'];

                    $location.search($.param($search));
                }

                return;
            }

            vm.isLoadingNewPage = true;

            // not in an angular context (eg, embedded dashboard), so must actually
            // change the URL
            var url = $.param($.extend({ date: date, period: period }, compareParams));
            broadcast.propagateNewPage(url);
        }

        function isValidDate(d) {
            if (Object.prototype.toString.call(d) !== "[object Date]") {
                return false;
            }

            return !isNaN(d.getTime());
        }

        function formatDate(date) {
            return piwikPeriods.format(date);
        }

        function isComparisonEnabled() {
            return piwikComparisonsService.isComparisonEnabled();
        }
    }
})();
