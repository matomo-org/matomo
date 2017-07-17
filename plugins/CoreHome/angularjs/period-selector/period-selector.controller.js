/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    angular.module('piwikApp').controller('PeriodSelectorController', PeriodSelectorController);

    PeriodSelectorController.$inject = ['piwik', '$location', 'piwikPeriods'];

    function PeriodSelectorController(piwik, $location, piwikPeriods) {
        var vm = this;

        // the period & date currently being viewed
        vm.periodValue = null;
        vm.dateValue = null;

        vm.selectedPeriod = null;

        vm.startRangeDate = null;
        vm.endRangeDate = null;

        vm.isLoadingNewPage = false;

        vm.getCurrentlyViewingText = getCurrentlyViewingText;
        vm.changeViewedPeriod = changeViewedPeriod;
        vm.setPiwikPeriodAndDate = setPiwikPeriodAndDate;
        vm.onApplyClicked = onApplyClicked;
        vm.updateSelectedValuesFromHash = updateSelectedValuesFromHash;
        vm.getPeriodDisplayText = getPeriodDisplayText;

        vm.updateSelectedValuesFromHash();
        initTopControls(); // must be called when a top control changes width

        function updateSelectedValuesFromHash() {
            var strDate = getQueryParamValue('date');
            var strPeriod = getQueryParamValue('period');

            vm.periodValue = strPeriod;
            vm.selectedPeriod = strPeriod;

            if (strPeriod === 'range') {
                var parts = strDate.split(',');
                vm.startRangeDate = parts[0];
                vm.endRangeDate = parts[1];

                vm.dateValue = piwikPeriods.parseDate(parts[0]);
            } else {
                vm.dateValue = piwikPeriods.parseDate(strDate);

                var range = piwikPeriods.parse(strPeriod, strDate).getDateRange();
                vm.startRangeDate = formatDate(range[0]);
                vm.endRangeDate = formatDate(range[1]);
            }
        }

        function getQueryParamValue(name) {
            // $location doesn't parse the URL before the hashbang, but it can hold the query param
            // values, if the page doesn't have the hashbang.
            var result = $location.search()[name];
            if (!result) {
                result = broadcast.getValueFromUrl(name);
            }
            return result;
        }

        function getPeriodDisplayText(periodLabel) {
            return piwikPeriods.get(periodLabel).getDisplayText();
        }

        function getCurrentlyViewingText() {
            var date;
            if (vm.periodValue === 'range') {
                date = formatDate(vm.startRangeDate) + ',' + formatDate(vm.endRangeDate);
            } else {
                date = formatDate(vm.dateValue);
            }

            return piwikPeriods.parse(vm.periodValue, date).getPrettyString();
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
                    return;
                }

                vm.periodValue = 'range';

                propagateNewUrlParams(dateFrom + ',' + dateTo, 'range');
                return;
            }

            setPiwikPeriodAndDate(vm.selectedPeriod, vm.dateValue);
        }

        function setPiwikPeriodAndDate(period, date) {
            vm.periodValue = period;
            vm.selectedPeriod = period;
            vm.dateValue = date;

            piwik.period = period;
            piwik.currentDateString = formatDate(date);

            var dateRange = piwikPeriods.parse(period, piwik.currentDateString).getDateRange();
            vm.startRangeDate = formatDate(dateRange[0]);
            vm.endRangeDate = formatDate(dateRange[1]);

            piwik.startDateString = vm.startRangeDate;
            piwik.endDateString = vm.endRangeDate;

            propagateNewUrlParams(piwik.currentDateString, vm.selectedPeriod);
            initTopControls();
        }

        function propagateNewUrlParams(date, period) {
            if (piwik.helper.isAngularRenderingThePage()) {
                vm.closePeriodSelector(); // defined in directive

                var $search = $location.search();
                if (date !== $search.date || period !== $search.period) {
                    // eg when using back button the date might be actually already changed in the URL and we do not
                    // want to change the URL again
                    $search.date = date;
                    $search.period = period;
                    $location.search($search);
                }

                return;
            }

            vm.isLoadingNewPage = true;

            // not in an angular context (eg, embedded dashboard), so must actually
            // change the URL
            broadcast.propagateNewPage('date=' + date + '&period=' + period);
        }

        function isValidDate(d) {
            if (Object.prototype.toString.call(d) !== "[object Date]") {
                return false;
            }

            return !isNaN(d.getTime());
        }

        function formatDate(date) {
            return $.datepicker.formatDate('yy-mm-dd', date);
        }
    }
})();
